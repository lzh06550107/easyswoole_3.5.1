<?php


namespace EasySwoole\Component\Process\Socket;


use EasySwoole\Component\Process\Config;

/**
 * tcp进程连接配置类
 */
class TcpProcessConfig extends Config
{
    protected $listenAddress = '0.0.0.0';
    protected $listenPort; // 监听端口
    protected $asyncCallback = true; // 异步回调，则使用子协程来处理新的客户端socket连接
    protected $linger = [ 'l_linger' => 0, 'l_onoff' => 0];

    /**
     * @return int[]
     */
    public function getLinger(): array
    {
        return $this->linger;
    }

    /**
     * @param int[] $linger
     */
    public function setLinger(array $linger): void
    {
        $this->linger = $linger;
    }

    /**
     * @return string
     */
    public function getListenAddress(): string
    {
        return $this->listenAddress;
    }

    /**
     * @param string $listenAddress
     */
    public function setListenAddress(string $listenAddress): void
    {
        $this->listenAddress = $listenAddress;
    }

    /**
     * @return mixed
     */
    public function getListenPort()
    {
        return $this->listenPort;
    }

    /**
     * @param mixed $listenPort
     */
    public function setListenPort($listenPort): void
    {
        $this->listenPort = $listenPort;
    }
    /**
     * @return bool
     */
    public function isAsyncCallback(): bool
    {
        return $this->asyncCallback;
    }

    /**
     * @param bool $asyncCallback
     */
    public function setAsyncCallback(bool $asyncCallback): void
    {
        $this->asyncCallback = $asyncCallback;
    }

}