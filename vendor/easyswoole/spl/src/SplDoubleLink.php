<?php

namespace EasySwoole\Spl;

/**
 * 双向链表父类，子类继承
 */
class SplDoubleLink
{
    private $next;
    private $pre;

    /**
     * 是否存在下一个节点
     * @return bool
     */
    function hashNext():bool
    {
        return (bool)$this->next;
    }

    /**
     * 是否存在上一个节点
     * @return bool
     */
    function hashPre():bool
    {
        return (bool)$this->pre;
    }

    /**
     * 获取下一个链表节点，如果不存在，则根据参数创建下一个链表节点
     * @param ...$arg
     * @return SplDoubleLink
     */
    function next(...$arg):SplDoubleLink
    {
        if(!$this->next){
            $this->next = $this->newInstance(...$arg);
        }
        return $this->next;
    }

    /**
     * 获取上一个链表节点，如果不存在则根据参数创建上一个链表节点
     * @param ...$arg
     * @return SplDoubleLink
     * @throws \ReflectionException
     */
    function pre(...$arg):SplDoubleLink
    {
        if(!$this->pre){
            $this->pre = $this->newInstance(...$arg);
        }
        return $this->pre;
    }

    /**
     * 删除当前节点上一个链表节点
     * @return $this
     */
    function delPre()
    {
        $this->pre = null;
        return $this;
    }

    /**
     * 删除当前节点下一个链表节点
     * @return $this
     */
    function delNext()
    {
        $this->next = null;
        return $this;
    }

    /**
     * 根据传入的参数实例化子类
     * @param ...$arg
     * @return SplDoubleLink
     * @throws \ReflectionException
     */
    private function newInstance(...$arg):SplDoubleLink
    {
        $ref = new \ReflectionClass(static::class);
        return $ref->newInstanceArgs($arg);
    }

}