<?php


namespace EasySwoole\EasySwoole\AbstractInterface;


use EasySwoole\EasySwoole\Swoole\EventRegister;

/**
 * 应用启动的事件接口
 */
interface Event
{
    /**
     * 应用初始化前的回调
     * @return mixed
     */
    public static function initialize();

    /**
     * 应用主服务器创建前的回调
     * @param EventRegister $register
     * @return mixed
     */
    public static function mainServerCreate(EventRegister $register);

}