<?php


namespace EasySwoole\Component\Process\Socket;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Exception;
use Swoole\Coroutine\Socket;
use Swoole\Coroutine;

/**
 * tcp连接进程抽象类
 */
abstract class AbstractTcpProcess extends AbstractProcess
{
    function __construct(TcpProcessConfig $config)
    {
        $config->setEnableCoroutine(true); // 进程默认开启协程
        if(empty($config->getListenPort())){
            throw new Exception("listen port empty at class ".static::class);
        }
        parent::__construct($config);
    }

    public function run($arg)
    {
        $socket = new Socket(AF_INET,SOCK_STREAM,0); // ip4,tcp
        $socket->setOption(SOL_SOCKET,SO_REUSEPORT,true); // 端口重用
        $socket->setOption(SOL_SOCKET,SO_REUSEADDR,true); // 地址重用
        $socket->setOption(SOL_SOCKET,SO_LINGER,$this->getConfig()->getLinger()); // SO_LINGER选项用来设置延迟关闭的时间,等待套接字发送缓冲区中的数据发送完成
        $ret = $socket->bind($this->getConfig()->getListenAddress(),$this->getConfig()->getListenPort()); // 绑定地址和端口
        if(!$ret){
            throw new Exception(static::class." bind {$this->getConfig()->getListenAddress()}:{$this->getConfig()->getListenPort()} fail case ".$socket->errMsg);
        }
        $ret = $socket->listen(2048); // 监听，并指定监听队列的长度
        if(!$ret){
            throw new Exception(static::class." listen {$this->getConfig()->getListenAddress()}:{$this->getConfig()->getListenPort()} fail case ".$socket->errMsg);
        }

        while (1){ // 主协程进入死循环
            $client = $socket->accept(-1); // 调用此方法会立即挂起当前协程，并加入 EventLoop 监听可读事件，当 Socket 可读有到来的连接时自动唤醒该协程，并返回对应客户端连接的 Socket 对象。
            if(!$client){
                return;
            }
            if($this->getConfig()->isAsyncCallback()){
                // 创建子协程来处理新的客户端连接
                Coroutine::create(function ()use($client){
                    try{
                        $this->onAccept($client);
                    }catch (\Throwable $throwable){
                        $this->onException($throwable,$client);
                    }
                });
            }else{ // 如果是同步回调，则使用主协程来处理客户端连接
                try{
                    $this->onAccept($client);
                }catch (\Throwable $throwable){
                    $this->onException($throwable,$client);
                }
            }
        }
    }

    abstract function onAccept(Socket $socket); // 子类重载该方法
}