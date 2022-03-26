<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 15:54
 */

namespace EasySwoole\Component;


use Swoole\Coroutine\Channel;

/**
 * 通道管理器
 */
class ChannelManger
{
    use Singleton;
    private $list = []; // 保存创建的所有通道

    /**
     * 添加通道
     * @param $name 通道名称
     * @param $size 通道大小
     */
    function add($name,$size = 1024):void
    {
        if(!isset($this->list[$name])){
            $chan = new Channel($size);
            $this->list[$name] = $chan;
        }
    }

    /**
     * 获取指定名称的通道
     * @param $name
     * @return Channel|null
     */
    function get($name):?Channel
    {
        if(isset($this->list[$name])){
            return $this->list[$name];
        }else{
            return null;
        }
    }
}