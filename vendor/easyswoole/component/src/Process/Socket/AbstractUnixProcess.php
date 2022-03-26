<?php


namespace EasySwoole\Component\Process\Socket;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Exception;
use Swoole\Coroutine\Socket;
use Swoole\Coroutine;

/**
 * Unix Socket连接的进程抽象类
 */
abstract class AbstractUnixProcess extends AbstractProcess
{
    function __construct(UnixProcessConfig $config)
    {
        $config->setEnableCoroutine(true); // 进程中默认开启协程
        if(empty($config->getSocketFile())){
            throw new Exception("socket file is empty at class ".static::class);
        }
        parent::__construct($config);
    }

    public function run($arg)
    {
        if (file_exists($this->getConfig()->getSocketFile()))// 删除socket通讯文件
        {
            unlink($this->getConfig()->getSocketFile());
        }
        $socketServer = new Socket(AF_UNIX,SOCK_STREAM,0); // 使用UNIX管道通讯，流式
        $socketServer->setOption(SOL_SOCKET,SO_LINGER,$this->getConfig()->getLinger());
        if(!$socketServer->bind($this->getConfig()->getSocketFile())){ // 绑定地址和端口
            throw new Exception(static::class.' bind '.$this->getConfig()->getSocketFile(). ' fail case '.$socketServer->errMsg);
        }
        if(!$socketServer->listen(2048)){ // 监听 Socket，监听队列的长度
            throw new Exception(static::class.' listen '.$this->getConfig()->getSocketFile(). ' fail case '.$socketServer->errMsg);
        }
        while (1){
            $client = $socketServer->accept(-1); // 接受客户端发起的连接，-1表示永不超时
            if(!$client){
                return;
            }
            if($this->getConfig()->isAsyncCallback()){ // 是否是异步回调，如果是，则创建子协程来处理客户端连接
                Coroutine::create(function ()use($client){
                    try{
                        $this->onAccept($client); // 子类重写该方法
                    }catch (\Throwable $throwable){
                        $this->onException($throwable,$client);
                    }
                });
            }else{ // 如果是同步，则当前协程来处理客户端连接
                try{
                    $this->onAccept($client); // 子类重写该方法
                }catch (\Throwable $throwable){
                    $this->onException($throwable,$client);
                }
            }
        }
    }

    abstract function onAccept(Socket $socket); // 子类重载该方法
}