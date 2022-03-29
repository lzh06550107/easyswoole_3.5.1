<?php

namespace EasySwoole\Spl;

/**
 * 支持数组访问，计算数组大小和可迭代
 * ArrayIterator迭代器会把对象或数组封装为一个可以通过foreach来操作的类
 */
class SplStrictArray implements \ArrayAccess ,\Countable ,\IteratorAggregate
{
    private $class;
    private $data = [];

    function __construct(string $itemClass)
    {
        $this->class = $itemClass;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        if(isset($this->data[$offset])){
            return $this->data[$offset];
        }else{
            return null;
        }
    }

    public function offsetSet($offset, $value)
    {
        if(is_a($value,$this->class)){
            $this->data[$offset] = $value;
            return true;
        }
        throw new \Exception("StrictArray can only set {$this->class} object");
    }

    public function offsetUnset($offset)
    {
        if(isset($this->data[$offset])){
            unset($this->data[$offset]);
            return true;
        }else{
            return false;
        }
    }

    public function count()
    {
        return count($this->data);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}