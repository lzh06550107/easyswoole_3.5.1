<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/25
 * Time: 下午4:10
 */

namespace EasySwoole\Socket\AbstractInterface;

use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;
use EasySwoole\Socket\Client\Tcp;
use EasySwoole\Socket\Client\Udp;
use EasySwoole\Socket\Client\WebSocket;
use EasySwoole\Socket\Config;

/**
 * 控制器抽象类
 */
abstract class Controller
{
    private $response;
    private $caller;
    private $config;
    private $server;

    private $allowMethods = []; // 子类中允许调用的方法
    private $defaultProperties = []; // 收集子类public/protected属性的默认值

    function __construct()
    {
        //支持在子类控制器中以private，protected来修饰某个方法不可见
        $list = [];
        $ref = new \ReflectionClass(static::class); // 创建子类的反射对象
        $public = $ref->getMethods(\ReflectionMethod::IS_PUBLIC); // 获取子类所有公共方法
        foreach ($public as $item){
            array_push($list,$item->getName());
        }
        $this->allowMethods = array_diff($list,
            [
                '__hook','__destruct',
                '__clone','__construct','__call',
                '__callStatic','__get','__set',
                '__isset','__unset','__sleep',
                '__wakeup','__toString','__invoke',
                '__set_state','__clone','__debugInfo'
            ]
        );

        //获取，生成属性默认值
        $ref = new \ReflectionClass(static::class);
        $properties = $ref->getProperties();
        foreach ($properties as $property){
            //不重置静态变量
            if(($property->isPublic() || $property->isProtected()) && !$property->isStatic()){
                $name = $property->getName();
                $this->defaultProperties[$name] = $this->$name;
            }
        }
    }

    protected function actionNotFound(?string $actionName)
    {

    }

    protected function afterAction(?string $actionName)
    {

    }

    protected function onException(\Throwable $throwable):void
    {
        throw $throwable;
    }

    /*
     * 请求处理前的回调方法，返回false的时候为拦截
     */
    protected function onRequest(?string $actionName):bool
    {
        return true;
    }

    protected function response():Response
    {
        return $this->response;
    }

    /**
     * 立即响应
     * @param string $string
     * @author LZH
     * @since 2022/03/28
     */
    protected function responseImmediately(string $string)
    {
        $client = $this->caller->getClient();
        if($client instanceof WebSocket){
            $this->server->push($client->getFd(),$string);
        }else if($client instanceof Tcp){
            $this->server->send($client->getFd(),$string);
        }else if($client instanceof Udp){
            $this->server->sendto($client->getAddress(),$client->getPort(),$string,$client->getServerSocket());
        }
    }

    protected function caller():Caller
    {
        return $this->caller;
    }

    protected function gc()
    {
        //恢复默认值
        foreach ($this->defaultProperties as $property => $value){
            $this->$property = $value;
        }
    }

    public function __hook(\Swoole\Server $server,Config $config,Caller $request,Response $response)
    {
        $this->caller = $request;
        $this->response = $response;
        $this->config = $config;
        $this->server = $server;
        $actionName = $request->getAction();
        try{
            if($this->onRequest($actionName) !== false){ // 如果返回true，则继续执行请求方法
                if(in_array($actionName,$this->allowMethods)){
                    $this->$actionName();
                }else{
                    $this->actionNotFound($actionName); // 请求方法不存在
                }
            }
        }catch (\Throwable $throwable){
            //若没有重构onException，直接抛出给上层
            $this->onException($throwable);
        }finally{
            try{
                $this->afterAction($actionName); // 请求方法执行完成后回调
            }catch (\Throwable $throwable){
                $this->onException($throwable);
            }finally{
                try{
                    $this->gc(); // 恢复默认属性值
                }catch (\Throwable $throwable){
                    $this->onException($throwable);
                }
            }
        }
    }
}