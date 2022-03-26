<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 15:53
 */

namespace EasySwoole\Component;


use Swoole\Atomic;
use Swoole\Atomic\Long;

/**
 * 进程间无锁计数器 Atomic 管理器
 */
class AtomicManager
{
    use Singleton;

    private $list = []; // 32位无符号类型
    private $listForLong = []; // 64 位有符号长整型原子计数

    /**
     * 添加指定名称的32位无符号类型
     * @param $name 名称
     * @param int $int 值
     */
    function add($name,int $int = 0):void
    {
        if(!isset($this->list[$name])){
            $a = new Atomic($int);
            $this->list[$name] = $a;
        }
    }

    /**
     * 64 位有符号长整型
     * @param $name 名称
     * @param int $int 值
     */
    function addLong($name,int $int = 0)
    {
        if(!isset($this->listForLong[$name])){
            $a = new Long($int);
            $this->listForLong[$name] = $a;
        }
    }

    /**
     * 获取 64 位有符号长整型
     * @param $name
     * @return Long|null
     */
    function getLong($name):?Long
    {
        if(isset($this->listForLong[$name])){
            return $this->listForLong[$name];
        }else{
            return null;
        }
    }

    /**
     * 获取 32 位无符号类型
     * @param $name
     * @return Atomic|null
     */
    function get($name):?Atomic
    {
        if(isset($this->list[$name])){
            return $this->list[$name];
        }else{
            return null;
        }
    }
}
