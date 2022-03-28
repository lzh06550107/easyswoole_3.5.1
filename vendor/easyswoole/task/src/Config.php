<?php


namespace EasySwoole\Task;

use EasySwoole\Spl\SplBean;
use EasySwoole\Task\AbstractInterface\TaskQueueInterface;

/**
 * 任务配置类
 */
class Config extends SplBean
{
    protected $tempDir;
    protected $workerNum = 3;
    protected $serverName = 'EasySwoole';
    protected $maxRunningNum = 128; // 最大运行任务数
    protected $maxPackageSize = 1024 * 1024 * 2;//2M 包最大长度
    /**
     * @var float
     */
    protected $timeout = 5.0; // 任务执行超时时间
    protected $onException;
    /**
     * @var TaskQueueInterface
     */
    protected $taskQueue;

    /**
     * @return float|int
     */
    public function getMaxPackageSize()
    {
        return $this->maxPackageSize;
    }

    /**
     * @param float|int $maxPackageSize
     */
    public function setMaxPackageSize($maxPackageSize): void
    {
        $this->maxPackageSize = $maxPackageSize;
    }

    /**
     * @return mixed
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @param mixed $tempDir
     */
    public function setTempDir($tempDir): void
    {
        $this->tempDir = $tempDir;
    }

    /**
     * @return int
     */
    public function getWorkerNum(): int
    {
        return $this->workerNum;
    }

    /**
     * @param int $workerNum
     */
    public function setWorkerNum(int $workerNum): void
    {
        $this->workerNum = $workerNum;
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     */
    public function setServerName(string $serverName): void
    {
        $this->serverName = $serverName;
    }

    /**
     * @return int
     */
    public function getMaxRunningNum(): int
    {
        return $this->maxRunningNum;
    }

    /**
     * @param int $maxRunningNum
     */
    public function setMaxRunningNum(int $maxRunningNum): void
    {
        $this->maxRunningNum = $maxRunningNum;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return mixed
     */
    public function getOnException()
    {
        return $this->onException;
    }

    /**
     * @param mixed $onException
     */
    public function setOnException($onException): void
    {
        $this->onException = $onException;
    }

    /**
     * @return TaskQueueInterface
     */
    public function getTaskQueue(): ?TaskQueueInterface
    {
        return $this->taskQueue;
    }

    /**
     * @param TaskQueueInterface $taskQueue
     */
    public function setTaskQueue(TaskQueueInterface $taskQueue): void
    {
        $this->taskQueue = $taskQueue;
    }

    protected function initialize(): void
    {
        if(empty($this->tempDir)){
            $this->tempDir = getcwd();
        }
    }
}