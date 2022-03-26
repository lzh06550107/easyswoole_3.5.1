<?php


namespace EasySwoole\Crontab;

/**
 * 定时任务接口
 */
interface JobInterface
{
    /**
     * 定时任务名称
     * @return string
     */
    public function jobName(): string;

    /**
     * 定时任务规则
     * @return string
     */
    public function crontabRule(): string;

    /**
     * 定时任务运行主体
     * @return mixed
     */
    public function run();

    /**
     * 定时任务异常处理方法
     * @param \Throwable $throwable
     * @return mixed
     */
    public function onException(\Throwable $throwable);
}