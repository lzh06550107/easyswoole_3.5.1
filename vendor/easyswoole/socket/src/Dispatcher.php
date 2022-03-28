<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/25
 * Time: 下午3:16
 */

namespace EasySwoole\Socket;

use EasySwoole\Socket\AbstractInterface\Controller;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;
use EasySwoole\Socket\Client\Tcp;
use EasySwoole\Socket\Client\Udp;
use EasySwoole\Socket\Client\WebSocket;
use EasySwoole\Socket\Exception\ControllerPoolEmpty;
use Swoole\Coroutine as Co;
use Swoole\Server;

/**
 * 请求分发器
 */
class Dispatcher
{
    private $config;
    private $controllerPoolCreateNum = [];

    function __construct(Config $config) // socket配置
    {
        $this->config = $config;
        if($config->getParser() == null){ // 获取包解析器
            throw new \Exception('Package parser is required');
        }
    }

    /*
     * $args:
     *  Tcp  $fd，$reactorId
     *  Web Socket swoole_websocket_frame $frame
     *  Udp array $client_info;
     */
    function dispatch(Server $server , string $data, ...$args):void
    {
        $clientIp = null;
        $type = $this->config->getType(); // 获取服务类型
        switch ($type){ // 根据配置，实例化客户端连接实例对象
            case Config::TCP:{
                $client = new Tcp( ...$args);
                break;
            }
            case Config::WEB_SOCKET:{
                $client = new WebSocket( ...$args);
                break;
            }
            case Config::UDP:{
                $client = new Udp( ...$args);
                break;
            }
            default:{
                throw new \Exception('dispatcher type error : '.$type);
            }
        }
        $caller = null;
        $response = new Response(); // 实例化响应对象
        try{
            $caller = $this->config->getParser()->decode($data,$client); // 解码后生成调用者对象实例
        }catch (\Throwable $throwable){
            //注意，在解包出现异常的时候，则调用异常处理，默认是断开连接，服务端抛出异常
            $this->hookException($server,$throwable,$data,$client,$response);
            goto response;
        }
        //如果成功返回一个调用者，那么执行调用逻辑
        if($caller instanceof Caller){
            $caller->setClient($client); // 保存客户端连接
            //解包正确
            $controllerClass = $caller->getControllerClass(); // 获取控制器
            try{
                $controller = $this->getController($controllerClass);
            }catch (\Throwable $throwable){
                $this->hookException($server,$throwable,$data,$client,$response);
                goto response;
            }
            if($controller instanceof Controller){
                try{
                    $controller->__hook( $server,$this->config, $caller, $response);
                }catch (\Throwable $throwable){
                    $this->hookException($server,$throwable,$data,$client,$response);
                }finally {
                    $this->recycleController($controllerClass,$controller); // 回收控制器
                }
            }else{
                $throwable = new ControllerPoolEmpty('controller pool empty for '.$controllerClass);
                $this->hookException($server,$throwable,$data,$client,$response);
            }
        }
        response :{
            switch ($response->getStatus()){
                case Response::STATUS_OK:{
                    $this->response($server,$client,$response);
                    break;
                }
                case Response::STATUS_RESPONSE_AND_CLOSE:{ // 响应并关闭
                    $this->response($server,$client,$response);
                    $this->close($server, $client, $response);
                    break;
                }
                case Response::STATUS_CLOSE:{ // 不响应，直接关闭连接
                    $this->close($server, $client, $response);
                    break;
                }
            }
        }
    }

    /**
     * 响应客户端
     * @param Server $server
     * @param $client
     * @param Response $response
     */
    private function response(Server $server, $client, Response $response)
    {
        $data = $this->config->getParser()->encode($response,$client); // 编码响应内容
        if($data === null){
            return;
        }
        if($client instanceof WebSocket){
            if($server->exist($client->getFd())){
                $server->push($client->getFd(),$data,$response->getOpCode(),$response->isFinish());
            }
        }else if($client instanceof Tcp){
            if($server->exist($client->getFd())){
                $server->send($client->getFd(),$data);
            }
        }else if($client instanceof Udp){
            $server->sendto($client->getAddress(),$client->getPort(),$data,$client->getServerSocket());
        }
    }

    private function close(Server $server, $client, Response $response)
    {

        // because websocket client extends tcp client, so first if websocket client.
        if ($client instanceof WebSocket && $server->exist($client->getFd())) {
            /**@var \Swoole\WebSocket\Server $server */
            $server->disconnect($client->getFd(), $response->getCode(), $response->getReason());
        } else if($client instanceof Tcp && $server->exist($client->getFd())){
            $server->close($client->getFd(), $response->isReset());
        } // udp没有连接
    }

    private function hookException(Server $server, \Throwable $throwable, string $raw, $client, Response $response)
    {
        if(is_callable($this->config->getOnExceptionHandler())){
            call_user_func($this->config->getOnExceptionHandler(),$server,$throwable,$raw,$client,$response);
        }else{
            $this->close($server, $client, $response);
            throw $throwable;
        }
    }

    /**
     * 生成指定类的key
     * @param string $class
     * @return string
     */
    private function generateClassKey(string $class):string
    {
        return substr(md5($class), 8, 16);
    }

    /**
     * 获取控制器实例对象
     * @param string $class
     * @return mixed
     * @throws \Throwable
     */
    private function getController(string $class)
    {
        $classKey = $this->generateClassKey($class);
        if(!isset($this->$classKey)){
            // 控制器对象缓存保存在调度器实例属性中，而调度器实例在工作进程中保存唯一
            $this->$classKey = new Co\Channel($this->config->getMaxPoolNum()+1); // 多协程共享
            $this->controllerPoolCreateNum[$classKey] = 0; // 记录当前控制器在池中创建数目
        }
        $channel = $this->$classKey;
        //懒惰创建模式
        /** @var Co\Channel $channel */
        if($channel->isEmpty()){ // 如果通道中没有已经创建的控制器对象，则直接实例化
            $createNum = $this->controllerPoolCreateNum[$classKey];
            if($createNum < $this->config->getMaxPoolNum()){
                $this->controllerPoolCreateNum[$classKey] = $createNum + 1;
                try{
                    //防止用户在控制器结构函数做了什么东西导致异常
                    return new $class();
                }catch (\Throwable $exception){
                    $this->controllerPoolCreateNum[$classKey] = $createNum;
                    //直接抛给上层
                    throw $exception;
                }
            }
            return $channel->pop($this->config->getControllerPoolWaitTime());
        }
        // 如果通道中已经缓存该控制器对象，则直接获取
        return $channel->pop($this->config->getControllerPoolWaitTime());
    }

    /**
     * 回收控制器实例对象，当控制器对象调用完成后立即回收
     * @param string $class
     * @param Controller $obj
     */
    private function recycleController(string $class,Controller $obj)
    {
        $classKey = $this->generateClassKey($class);
        /** @var Co\Channel $channel */
        $channel = $this->$classKey;
        $channel->push($obj);
    }
}
