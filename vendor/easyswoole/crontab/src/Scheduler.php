<?php


namespace EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;
use Swoole\Table;

/**
 * 定时任务调度器
 */
class Scheduler extends AbstractProcess
{
    /** @var Table */
    private $schedulerTable; // 定时任务调度器信息记录共享内存表

    /** @var Crontab */
    private $crontabInstance; // 定时任务管理器对象

    private $timerIds = []; // 任务定时器id

    /**
     * @param $arg 进程对象实例时传入的参数
     */
    protected function run($arg)
    {
        $this->crontabInstance = $arg['crontabInstance']; // 定时任务管理器对象
        $this->schedulerTable = $arg['schedulerTable']; // 定时任务调度器共享内存表对象
        //异常的时候，worker会退出。先清空一遍规则,禁止循环的时候删除key
        $keys = [];
        foreach ($this->schedulerTable as $key => $value) {
            $keys[] = $key;
        }
        foreach ($keys as $key) {
            $this->schedulerTable->del($key);
        }

        $jobs = $arg['jobs']; // 要添加新的定时任务规则
        /**
         * @var  $jobName
         * @var JobInterface $job
         */
        foreach ($jobs as $jobName => $job) {
            $nextTime = CronExpression::factory($job->crontabRule())->getNextRunDate()->getTimestamp();
            $this->schedulerTable->set($jobName, ['taskRule' => $job->crontabRule(), 'taskRunTimes' => 0, 'taskNextRunTime' => $nextTime, 'taskCurrentRunTime' => 0, 'isStop' => 0]);
        }
        $this->cronProcess();
        //60无法被8整除。
        Timer::getInstance()->loop(8 * 1000, function () { // 8s运行一次定时任务
            $this->cronProcess();
        });
    }

    private function cronProcess()
    {
        foreach ($this->schedulerTable as $jobName => $task) {
            if (intval($task['isStop']) == 1) { // 定时任务停止，则不运行该定时任务
                continue;
            }
            $nextRunTime = CronExpression::factory($task['taskRule'])->getNextRunDate()->getTimestamp();
            if ($task['taskNextRunTime'] != $nextRunTime) { // 更新下次运行时间
                $this->schedulerTable->set($jobName, ['taskNextRunTime' => $nextRunTime]);
            }
            //本轮已经创建过任务
            if (isset($this->timerIds[$jobName])) {
                continue;
            }
            $distanceTime = $nextRunTime - time();
            $timerId = Timer::getInstance()->after($distanceTime * 1000, function () use ($jobName) {
                unset($this->timerIds[$jobName]); // 运行的时候清空该定时器
                try {
                    $this->crontabInstance->rightNow($jobName);
                } catch (\Throwable $throwable) {
                    $call = $this->crontabInstance->getConfig()->getOnException();
                    if (is_callable($call)) {
                        call_user_func($call, $throwable);
                    } else {
                        throw $throwable;
                    }
                }
            });
            if ($timerId) {
                $this->timerIds[$jobName] = $timerId;
            }
        }
    }
}