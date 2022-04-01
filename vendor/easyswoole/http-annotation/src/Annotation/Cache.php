<?php

namespace EasySwoole\HttpAnnotation\Annotation;

use EasySwoole\Component\Singleton;

/**
 * 注释的缓存管理器，数据保存在内存中，类名称和解析该类的所有注释对象的映射关系
 */
class Cache
{
    private $data = [];

    use Singleton;

    function set(string $class,ObjectAnnotation $data)
    {
        $this->data[md5($class)] = $data;
    }

    function get(string $class):?ObjectAnnotation
    {
        $key = md5($class);
        if(isset($this->data[$key])){
            return $this->data[$key];
        }
        return null;
    }


    function delete(string $class)
    {
        unset($this->data[md5($class)]);
    }

    function flush()
    {
        $this->data = [];
    }
}