<?php


namespace EasySwoole\Component;

use Swoole\Coroutine;

/**
 * 协程范围内的单例模式，协程关闭时销毁
 */
trait CoroutineSingleTon
{
    private static $instance = [];

    /**
     * 获取实例，并在协程关闭时自动销毁
     * @param mixed ...$args
     * @return static
     */
    public static function getInstance(...$args)
    {
        $cid = Coroutine::getCid();
        if(!isset(static::$instance[$cid])){
            static::$instance[$cid] = new static(...$args);
            /*
             * 兼容非携程环境
             */
            if($cid > 0){
                Coroutine::defer(function ()use($cid){
                    unset(static::$instance[$cid]);
                });
            }
        }
        return static::$instance[$cid];
    }

    /**
     * 手动销毁，可销毁指定协程的单例
     * @param int|null $ci
     */
    function destroy(int $cid = null)
    {
        if($cid === null){
            $cid = Coroutine::getCid();
        }
        unset(static::$instance[$cid]);
    }
}