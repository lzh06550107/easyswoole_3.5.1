<?php

namespace EasySwoole\Spl;

use Swoole\Coroutine;

/**
 * 可作为协程上下文使用
 */
class SplContextArray implements \ArrayAccess,\Countable ,\JsonSerializable ,\IteratorAggregate
{

    private $data = [];
    private $autoClear = false; // 是否在协程关闭时自动销毁该协程上下文件
    private $onContextCreate; // 上下文创建回调函数
    private $onContextDestroy; // 上下文件销毁回调函数

    function __construct(bool $autoClear = true)
    {
        $this->autoClear = $autoClear;
    }

    /**
     * 返回当前协程的上下文，并封装到到ArrayIterator
     * @return \ArrayIterator 这个迭代器允许在遍历数组和对象时删除和更新值与键
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data[$this->cid()]);
    }

    /**
     * 返回当前协程的上下文中指定的偏移值是否存在
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$this->cid()][$offset]);
    }

    /**
     * 获取当前协程的上下文中指定的偏移值
     * @param $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        if(isset($this->data[$this->cid()][$offset])){
            return $this->data[$this->cid()][$offset];
        }
        return null;
    }

    /**
     * 设置当前协程上下文中指定偏移的值
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$this->cid()][$offset] = $value;
    }

    /**
     * 删除当前协程上下文中指定的偏移的值
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$this->cid()][$offset]);
    }


    /************** Count *************/

    /**
     * 返回当前协程上下文大小
     * @return int
     */
    public function count()
    {
        return count($this->data[$this->cid()]);
    }

    /**
     * 销毁协程上下文件数组
     * @param int|null $cid
     */
    function destroy(int $cid = null)
    {
        if($cid === null){
            $cid = Coroutine::getCid();
        }
        if($this->onContextDestroy){ // 如果存在上下文销毁回调函数，则调用
            call_user_func($this->onContextDestroy,$this);
        }
        unset($this->data[$cid]);
    }

    /**
     * ??
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->data[$this->cid()];
    }

    /**
     * 获取当前协程的上下文数组
     * @return array
     */
    public function toArray():array
    {
        return $this->data[$this->cid()];
    }

    /**
     * 批量设置当前协程的上下文
     * @param array $array
     */
    public function loadArray(array $array)
    {
        $this->data[$this->cid()] = $array;
    }

    public function setOnContextCreate(callable $call)
    {
        $this->onContextCreate = $call;
        return $this;
    }

    public function setOnContextDestroy(callable $call)
    {
        $this->onContextDestroy = $call;
        return $this;
    }

    /**
     * 初始化当前协程的上下文并返回协程号
     * @return int
     */
    private function cid():int
    {
        $cid = Coroutine::getCid(); // 获取协程号
        if(!isset($this->data[$cid])){ // 没有设置，则初始化
            $this->data[$cid] = [];
            if($this->onContextCreate){ // 存在上下文回调函数，则调用
                call_user_func($this->onContextCreate,$this);
            }
            if($this->autoClear && $cid > 0){ //
                defer(function ()use($cid){
                    $this->destroy($cid);
                });
            }
        }
        return $cid;
    }
}