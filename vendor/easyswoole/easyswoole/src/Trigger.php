<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/14
 * Time: 下午6:17
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Event;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Utility\DefaultTrigger;
use EasySwoole\Trigger\Location;
use EasySwoole\Trigger\TriggerInterface;

/**
 * 触发器管理器
 */
class Trigger
{
    use Singleton;

    private $trigger; // 触发器对象

    private $onError; // 错误事件监听器
    private $onException; // 异常事件监听器

    function __construct(?TriggerInterface $trigger = null)
    {
        if($trigger == null){
            $trigger = new DefaultTrigger();
        }
        $this->trigger = $trigger;
        $this->onError = new Event(); // 忽略事件名称，会调用所有监听器
        $this->onException = new Event(); // 忽略事件名称，会调用所有监听器
    }

    public function error($msg,int $errorCode = E_USER_ERROR,Location $location = null)
    {
        if($location == null){
            $location = $this->getLocation();
        }
        $this->trigger->error($msg,$errorCode,$location);
        $all = $this->onError->all();
        foreach ($all as $call){
            call_user_func($call,$msg,$errorCode,$location);
        }
    }

    public function throwable(\Throwable $throwable)
    {
        $this->trigger->throwable($throwable);
        $all = $this->onException->all();
        foreach ($all as $call){
            call_user_func($call,$throwable);
        }
    }

    public function onError():Event
    {
        return $this->onError;
    }

    public function onException():Event
    {
        return $this->onException;
    }

    private function getLocation():Location
    {
        $location = new Location();
        $debugTrace = debug_backtrace();
        array_shift($debugTrace);
        $caller = array_shift($debugTrace);
        $location->setLine($caller['line']);
        $location->setFile($caller['file']);
        return $location;
    }
}