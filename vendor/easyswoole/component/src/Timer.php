<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 11:39
 */

namespace EasySwoole\Component;
use Swoole\Timer as SWTimer;

/**
 * 封装swoole定时器管理类
 */
class Timer
{
    use Singleton;

    protected $timerMap = [];

    /**
     * 设置一个间隔时钟定时器
     * 定时器仅在当前进程空间内有效
     * @param int $ms 指定时间
     * @param callable $callback 时间到期后所执行的函数，必须是可以调用的
     * @param $name 定时器名称
     * @param ...$params 给执行函数传递数据
     * @return int
     */
    function loop(int $ms, callable $callback, $name = null, ...$params): int
    {
        $id = SWTimer::tick($ms, $callback, ...$params);
        if ($name !== null) {
            $this->timerMap[md5($name)] = $id;
        }
        return $id;
    }

    /**
     * 清除定时器
     * @param $timerIdOrName
     * @return bool
     */
    function clear($timerIdOrName): bool
    {
        $tid = null;
        if(is_numeric($timerIdOrName)){
            $tid = $timerIdOrName;
        }else if(isset($this->timerMap[md5($timerIdOrName)])){
            $tid = $this->timerMap[md5($timerIdOrName)];
            unset($this->timerMap[md5($timerIdOrName)]);
        }
        if($tid && SWTimer::info($tid)){
            SWTimer::clear($tid);
            return true;
        }
        return false;
    }

    /**
     * 清除所有的定时器
     * @return bool
     */
    function clearAll(): bool
    {
        $this->timerMap = [];
        SWTimer::clearAll();
        return true;
    }

    /**
     * @param int $ms
     * @param callable $callback
     * @param ...$params
     * @return int
     */
    function after(int $ms, callable $callback, ...$params): int
    {
        return SWTimer::after($ms, $callback, ...$params);
    }

    function list():array 
    {
        return SWTimer::list();
    }
}
