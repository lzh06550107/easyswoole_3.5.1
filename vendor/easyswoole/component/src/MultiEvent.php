<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/30
 * Time: 下午2:37
 */

namespace EasySwoole\Component;

/**
 * 一个事件可以注册多个监听器的事件类
 */
class MultiEvent extends MultiContainer
{
    /**
     * 覆盖添加时间和监听器
     * @param $key
     * @param $item
     * @return MultiEvent|false
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
     * 对同一个事件追加监听器
     * @param $key
     * @param $item
     * @return MultiEvent|false
     */
    function add($key, $item)
    {
        if(is_callable($item)){
            return parent::add($key, $item);
        }else{
            return false;
        }
    }

    /**
     * 触发事件让监听器执行
     * @param $event
     * @param mixed ...$args
     * @return array
     * @throws \Throwable
     */
    public function hook($event, ...$args):array
    {
        $res = [];
        $calls = $this->get($event);
        if(is_array($calls)){
            foreach ($calls as $key => $call){
                $res[$key] =  call_user_func($call,...$args);
            }
        }
        return $res;
    }
}