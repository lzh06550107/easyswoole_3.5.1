<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/25
 * Time: 下午12:41
 */

namespace EasySwoole\Component;

/**
 * 容器，觉得应该遵循php规范的容器接口，有待优化
 */
class Container
{
    private $container = [];
    private $allowKeys = null; // 允许的键

    function __construct(array $allowKeys = null)
    {
        $this->allowKeys = $allowKeys;
    }

    /**
     * 添加键值对到容器中
     * @param $key 键名称
     * @param $item 值
     * @return $this|false
     */
    function set($key, $item)
    {
        if(is_array($this->allowKeys) && !in_array($key,$this->allowKeys)){
            return false;
        }
        $this->container[$key] = $item;
        return $this;
    }

    /**
     * 删除指定的键值
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
     * 从容器中获取指定键的值
     * @param $key
     * @return mixed|null
     */
    function get($key)
    {
        if(isset($this->container[$key])){
            return $this->container[$key];
        }else{
            return null;
        }
    }

    /**
     * 清空容器
     */
    function clear()
    {
        $this->container = [];
    }

    /**
     * 返回容器数组
     * @return array
     */
    function all():array
    {
        return $this->container;
    }
}