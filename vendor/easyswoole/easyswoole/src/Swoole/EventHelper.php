<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/28
 * Time: 下午5:38
 */

namespace EasySwoole\EasySwoole\Swoole;

/**
 * 事件操作帮助类
 */
class EventHelper
{
    /**
     * 覆盖添加
     * @param EventRegister $register
     * @param string $event
     * @param callable $callback
     * @return void
     */
    public static function register(EventRegister $register,string $event,callable $callback):void
    {
        $register->set($event,$callback);
    }

    /**
     * 追加
     * @param EventRegister $register
     * @param string $event
     * @param callable $callback
     * @return void
     */
    public static function registerWithAdd(EventRegister $register,string $event,callable $callback):void
    {
        $register->add($event,$callback);
    }

    /**
     * 注册 Server 的事件回调函数。重复调用 on 方法时会覆盖上一次的设定
     * @param \Swoole\Server $server
     * @param string $event https://wiki.swoole.com/#/server/events
     * @param callable $callback
     * @return void
     */
    public static function on(\Swoole\Server $server,string $event,callable $callback)
    {
        $server->on($event,$callback);
    }
}