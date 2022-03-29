<?php


namespace EasySwoole\SyncInvoker;


use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use Swoole\Coroutine\Socket;

class Worker extends AbstractUnixProcess
{
    private $config;

    function run($arg)
    {
        /** @var Config $config */
        $config = $arg['config']; // 同步调用配置
        $this->config = $config;
        if(is_callable($config->getOnWorkerStart())){
            call_user_func($config->getOnWorkerStart(),$this);
        }
        parent::run($arg);
    }

    function onAccept(Socket $socket)
    {
        /** @var Config $config */
        $config = $this->config;
        $header = $socket->recvAll(4,1); // 接收4个字节长度数据，超时时间为1s

        if(strlen($header) != 4){ // 协议错误
            $socket->close();
            return;
        }

        $allLength = Protocol::packDataLength($header); // 获取数据包的长度
        $data = $socket->recvAll($allLength,1); // 接收整个数据包，超时时间为1s
        if(strlen($data) == $allLength){
            try{ // 同步调用
                $command = \Opis\Closure\unserialize($data);
                $reply = null;
                if($command instanceof Command){
                    $driver = clone $config->getDriver();
                    $reply = $driver->__hook($command); // 调用命令并返回
                }
                $socket->sendAll(Protocol::pack(\Opis\Closure\serialize($reply)));
            }catch (\Throwable $exception){
                throw $exception;
            } finally {
                $socket->close();
            }
        }else{
            $socket->close();
        }
    }
}