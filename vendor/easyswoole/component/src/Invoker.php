<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午4:12
 */

namespace EasySwoole\Component;

use \Swoole\Process;

// 给方法提供超时机制，如果在指定时间内没有返回，则结果函数执行
class Invoker
{
    /*
     * 以下方法仅供同步方法使用
     * 
     */
    public static function exec(callable $callable,$timeOut = 100 * 1000,...$params)
    {
        pcntl_async_signals(true); // 启用异步信号处理
        pcntl_signal(SIGALRM, function () { // 安装一个信号处理器，这里处理时钟信号
            // 高精度定时器，是操作系统 setitimer 系统调用的封装，可以设置微秒级别的定时器。定时器会触发信号，需要与 Process::signal 或 pcntl_signal 配合使用。
            Process::alarm(-1); // 如果为负数表示清除定时器
            throw new \RuntimeException('func timeout');
        });
        try
        {
            Process::alarm($timeOut); // 定时器间隔时间，表示为真实时间，触发 SIGALAM 信号。指定超时时间到了则触发时钟信号
            $ret = call_user_func($callable,...$params); // 调用函数
            Process::alarm(-1); // 调用完成后立即清除定时器
            return $ret;
        }
        catch(\Throwable $throwable)
        {
            throw $throwable; // 捕获函数执行超时，并抛出异常
        }
    }
}