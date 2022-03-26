<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/25
 * Time: 下午12:42
 */

namespace EasySwoole\Component;

/**
 * 一个键存在多个值的容器类
 */
class MultiContainer
{
    private $container = [];
    private $allowKeys = null;

    function __construct(array $allowKeys = null)
    {
        $this->allowKeys = $allowKeys;
    }

    /**
     * 追加键值对
     * @param $key
     * @param $item
     * @return $this|false
     */
    function add($key,$item)
    {
        if(is_array($this->allowKeys) && !in_array($key,$this->allowKeys)){
            return false;
        }
        $this->container[$key][] = $item;
        return $this;
    }

    /**
     * 覆盖添加
     * @param $key
     * @param $item
     * @return $this|false
     */
    function set($key,$item)
    {
        if(is_array($this->allowKeys) && !in_array($key,$this->allowKeys)){
            return false;
        }
        $this->container[$key] = [$item];
        return $this;
    }

    /**
     * 删除某键的所有值
     * @param $key
     * @return $this
     */
    function delete($key)
    {
        if(isset($this->container[$key])){
            unset($this->container[$key]);
        }
        return $this;
    }

    /**
     * 获取某键的所有值
     * @param $key
     * @return array|null
     */
    function get($key):?array
    {
        if(isset($this->container[$key])){
            return $this->container[$key];
        }else{
            return null;
        }
    }

    /**
     * 获取整个容器
     * @return array
     */
    function all():array
    {
        return $this->container;
    }

    /**
     * 清空整个容器
     */
    function clear()
    {
        $this->container = [];
    }
}