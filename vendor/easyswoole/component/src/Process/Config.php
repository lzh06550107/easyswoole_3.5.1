<?php


namespace EasySwoole\Component\Process;

use EasySwoole\Spl\SplBean;

// 进程配置类
class Config extends SplBean
{
    const PIPE_TYPE_NONE = 0; // 如果子进程内没有进程间通信，可以设置为 0
    const PIPE_TYPE_SOCK_STREAM = 1; // 进程间通讯，Unix Socket，TCP包
    const PIPE_TYPE_SOCK_DGRAM = 2; // 进程间通讯，Unix Socket，UDP包

    protected $processName; // 进程名称
    /** @var string */
    protected $processGroup = null; // 进程组名称
    protected $arg; // 传递给同步调用工作进程的参数
    protected $redirectStdinStdout = false; // 是否重定向进程输入和输出
    protected $pipeType = self::PIPE_TYPE_SOCK_DGRAM;
    protected $enableCoroutine = false; // 进程是否开启协程
    protected $maxExitWaitTime = 3; // 进程退出最大等待时间

    /**
     * @return mixed
     */
    public function getProcessName()
    {
        return $this->processName;
    }

    /**
     * @param mixed $processName
     */
    public function setProcessName($processName): void
    {
        $this->processName = $processName;
    }

    /**
     * @return mixed
     */
    public function getArg()
    {
        return $this->arg;
    }

    /**
     * @param mixed $arg
     */
    public function setArg($arg): void
    {
        $this->arg = $arg;
    }

    /**
     * @return bool
     */
    public function isRedirectStdinStdout(): bool
    {
        return $this->redirectStdinStdout;
    }

    /**
     * @param bool $redirectStdinStdout
     */
    public function setRedirectStdinStdout(bool $redirectStdinStdout): void
    {
        $this->redirectStdinStdout = $redirectStdinStdout;
    }

    /**
     * @return int
     */
    public function getPipeType(): int
    {
        return $this->pipeType;
    }

    /**
     * @param int $pipeType
     */
    public function setPipeType(int $pipeType): void
    {
        $this->pipeType = $pipeType;
    }

    /**
     * @return bool
     */
    public function isEnableCoroutine(): bool
    {
        return $this->enableCoroutine;
    }

    /**
     * @param bool $enableCoroutine
     */
    public function setEnableCoroutine(bool $enableCoroutine): void
    {
        $this->enableCoroutine = $enableCoroutine;
    }

    /**
     * @return int
     */
    public function getMaxExitWaitTime(): int
    {
        return $this->maxExitWaitTime;
    }

    /**
     * @param int $maxExitWaitTime
     */
    public function setMaxExitWaitTime(int $maxExitWaitTime): void
    {
        $this->maxExitWaitTime = $maxExitWaitTime;
    }

    /**
     * @return string
     */
    public function getProcessGroup(): ?string
    {
        return $this->processGroup;
    }

    /**
     * @param string $processGroup
     */
    public function setProcessGroup(string $processGroup): void
    {
        $this->processGroup = $processGroup;
    }
}