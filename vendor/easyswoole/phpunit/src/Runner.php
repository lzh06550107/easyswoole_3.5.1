<?php

namespace EasySwoole\Phpunit;

use PHPUnit\TextUI\Command;
use Swoole\ExitException;
use Swoole\Timer;
use Swoole\Coroutine\Scheduler;

class Runner
{
    public static function run($noCoroutine = true)
    {
        if ($noCoroutine) { // 如果不开启协程，则
            return Command::main(false);
        }

        // 如果开启协程，则实例化调度器对象，采用协程容器去执行测试用例
        $exitCode = null;
        $scheduler = new Scheduler();
        // 添加一个协程来运行单元测试
        $scheduler->add(function () use (&$exitCode) {
            try {
                $exitCode = Command::main(false);
            } catch (\Throwable $throwable) {
                /**
                 * 屏蔽 swoole exit错误
                 */
                if (strpos($throwable->getMessage(),'swoole exit') === false) {
                    throw $throwable;
                }
            } finally {
                Timer::clearAll(); // 清空事件循环所有定时器
            }
        });
        $scheduler->start(); // 启动调度器

        return $exitCode; // 返回退出码
    }
}
