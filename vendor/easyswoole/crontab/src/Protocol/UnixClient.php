<?php


namespace EasySwoole\Crontab\Protocol;

use Swoole\Coroutine\Client;

/**
 * unix socket 客户端操作类
 */
class UnixClient
{
    private $client = null; // swoole协程客户端

    function __construct(string $unixSock, $maxSize)
    {
        $this->client = new Client(SWOOLE_UNIX_STREAM); // unix socket tcp流式
        $this->client->set(
            [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_max_length' => $maxSize
            ]
        ); // 配置客户端自动解包
        $this->client->connect($unixSock, null, 3);
    }

    function __destruct()
    {
        $this->close();
    }

    /**
     * 客户端连接关闭
     */
    function close()
    {
        if ($this->client->isConnected()) {
            $this->client->close();
        }
    }

    /**
     * 发送数据
     * @param string $rawData
     * @return false
     */
    function send(string $rawData)
    {
        if ($this->client->isConnected()) {
            return $this->client->send($rawData);
        } else {
            return false;
        }
    }

    /**
     * 接收数据
     * @param float $timeout
     * @return null
     */
    function recv(float $timeout = 0.1)
    {
        if ($this->client->isConnected()) {
            $ret = $this->client->recv($timeout);
            if (!empty($ret)) {
                return $ret;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}