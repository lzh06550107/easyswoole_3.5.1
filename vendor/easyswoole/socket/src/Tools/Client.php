<?php

namespace EasySwoole\Socket\Tools;
use Swoole\Coroutine\Client as SwooleClient;

/**
 * 客户端封装
 */
class Client
{
    private $client = null;

    function __construct(string $unixSock,?int $port = null)
    {
        if($port > 0){
            $this->client = new SwooleClient(SWOOLE_SOCK_TCP); // ip tcp连接
        }else{
            $this->client = new SwooleClient(SWOOLE_UNIX_STREAM); // unix socket tcp连接
        }

        $this->client->set(
            [
                'open_length_check' => true, // 包长检测提供了固定包头 + 包体这种格式协议的解析。启用后，可以保证 Worker 进程 onReceive 每次都会收到一个完整的数据包。
                'package_length_type'   => 'N', // 包头中某个字段作为包长度的值
                'package_length_offset' => 0, // length 长度值在包头的第几个字节
                'package_body_offset'   => 4, // 从第几个字节开始计算长度
                'package_max_length'    => 1024*1024 // 设置最大数据包尺寸，单位为字节
            ]
        );
        $this->client->connect($unixSock, $port, 3);
    }

    function client():SwooleClient
    {
        return $this->client;
    }

    function close()
    {
        if($this->client->isConnected()){
            $this->client->close();
        }
    }

    function __destruct()
    {
        $this->close();
        $this->client = null;
    }

    function send(string $rawData)
    {
        if($this->client->isConnected()){
            return $this->client->send(Protocol::pack($rawData));
        }else{
            return false;
        }
    }

    function recv(float $timeout = 0.1)
    {
        if($this->client->isConnected()){
            $ret = $this->client->recv($timeout);
            if(!empty($ret)){
                return Protocol::unpack($ret);
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
}