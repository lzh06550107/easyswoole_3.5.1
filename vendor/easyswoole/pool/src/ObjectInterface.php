<?php


namespace EasySwoole\Pool;

/**
 * 连接池保存的对象需要实现的接口
 */
interface ObjectInterface
{
    //unset 的时候执行，彻底释放一个池中对象的时候调用
    function gc();
    //使用后,free的时候会执行，回收一个池中对象的时候调用
    function objectRestore();
    //池中对象使用前调用,当返回true，表示该对象可用。返回false，该对象失效，需要释放
    function beforeUse():?bool ;
}