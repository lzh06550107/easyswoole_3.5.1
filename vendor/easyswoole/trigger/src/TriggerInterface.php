<?php


namespace EasySwoole\Trigger;

/**
 * 触发器接口
 */
interface TriggerInterface
{
    /**
     * 触发错误
     * @param $msg
     * @param int $errorCode
     * @param Location|null $location
     * @return mixed
     */
    public function error($msg,int $errorCode = E_USER_ERROR,Location $location = null);

    /**
     * 抛出异常
     * @param \Throwable $throwable
     * @return mixed
     */
    public function throwable(\Throwable $throwable);
}