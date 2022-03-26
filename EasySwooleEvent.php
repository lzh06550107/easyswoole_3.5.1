<?php


namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

/**
 * easyswoole应用启动过程中回调事件
 */
class EasySwooleEvent implements Event
{
    // \EasySwoole\EasySwoole\Core::initialize中调用
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    // \EasySwoole\EasySwoole\Core::createServer中调用
    public static function mainServerCreate(EventRegister $register)
    {

    }
}