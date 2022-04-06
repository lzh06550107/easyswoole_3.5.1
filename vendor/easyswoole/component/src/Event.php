<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/25
 * Time: 下午12:42
 */

namespace EasySwoole\Component;

/**
 * 事件类容器，需要用户自定义事件管理器，但是还是存在问题，对同一事件只能注册一个监听器
 */
class Event extends Container
{
    /**
     * 保存事件以及事件监听器
     * @param $key
     * @param $item
     * @return Event|false
     */
    function set($key, $item)
    {
        if(is_callable($item)){
            return parent::set($key, $item);
        }else{
            return false;
        }
    }

    /**
     * 触发指定时间的监听器
     * @param $event
     * @param mixed ...$args
     * @return mixed|null
     * @throws \Throwable
     */
    public function hook($event, ...$args)
    {
        $call = $this->get($event);
        if(is_callable($call)){
            return call_user_func($call,...$args);
        }else{
            return null;
        }
    }
}