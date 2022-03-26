<?php


namespace EasySwoole\Component\CoroutineRunner;

/**
 * 任务封装类
 */
class Task
{
    protected $timeout = 3; // 任务执行超时时间
    /** @var callable */
    protected $call; // 任务执行主体
    /** @var callable */
    protected $onSuccess; // 任务执行成功回调
    /** @var callable */
    protected $onFail; // 任务执行失败回调
    /** @var float */
    protected $startTime; // 任务开始执行时间

    protected $result; // 任务执行结果

    function __construct(callable $call)
    {
        $this->call = $call;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return callable
     */
    public function getCall(): callable
    {
        return $this->call;
    }

    /**
     * @param callable $call
     */
    public function setCall(callable $call): void
    {
        $this->call = $call;
    }

    /**
     * @return callable
     */
    public function getOnSuccess():? callable
    {
        return $this->onSuccess;
    }

    /**
     * @param callable $onSuccess
     */
    public function setOnSuccess(callable $onSuccess): void
    {
        $this->onSuccess = $onSuccess;
    }

    /**
     * @return callable
     */
    public function getOnFail():? callable
    {
        return $this->onFail;
    }

    /**
     * @param callable $onFail
     */
    public function setOnFail(callable $onFail): void
    {
        $this->onFail = $onFail;
    }

    /**
     * @return float
     */
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * @param float $startTime
     */
    public function setStartTime(float $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result): void
    {
        $this->result = $result;
    }
}
