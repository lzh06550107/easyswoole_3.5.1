<?php


namespace EasySwoole\Component\Process\Socket;


use EasySwoole\Component\Process\Config;

/**
 * unix通信进程配置文件
 */
class UnixProcessConfig extends Config
{
    protected $socketFile; // unix socket 文件
    protected $asyncCallback = true; // 是否使用异步回调来处理客户端连接
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
     * @return mixed
     */
    public function getSocketFile()
    {
        return $this->socketFile;
    }

    /**
     * @param mixed $socketFile
     */
    public function setSocketFile($socketFile): void
    {
        $this->socketFile = $socketFile;
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