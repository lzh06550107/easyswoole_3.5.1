<?php


namespace EasySwoole\Task\AbstractInterface;

/**
 * 任务接口
 */
interface TaskInterface
{
    /**
     * 任务运行体
     * @param int $taskId
     * @param int $workerIndex
     * @return mixed
     */
    function run(int $taskId,int $workerIndex);

    /**
     * 任务异常处理方法
     * @param \Throwable $throwable
     * @param int $taskId
     * @param int $workerIndex
     * @return mixed
     */
    function onException(\Throwable $throwable,int $taskId,int $workerIndex);
}