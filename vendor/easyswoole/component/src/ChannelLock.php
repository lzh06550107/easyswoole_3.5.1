<?php


namespace EasySwoole\Component;


use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * 进程内通过通道实现的协程锁管理器
 */
class ChannelLock
{
    protected $list; // 可定义多个协程锁
    protected $status = []; // 记录上锁的协程
    use Singleton;

    /**
     * 尝试锁住指定名称的锁
     * @param string $lockName 锁名称
     * @param float $timeout 超时时间,-1为永久不超时 当调用此函数后,会尝试锁住$lockName,成功将返回true,如果之前已经有其他协程锁住了此$lockName,将会阻塞,直到超时返回false(-1用不超时,代表永远阻塞)
     * @return bool 成功加锁返回true
     */
    function lock(string $lockName,float $timeout = -1):bool
    {
        $cid = Coroutine::getCid();
        if(isset($this->status[$cid])){
            return true;
        }
        if(!isset($this->list[$lockName])){
            $this->list[$lockName] = new Channel(1);
        }
        /** @var Channel $channel */
        $channel = $this->list[$lockName];
        // 在通道已满的情况下，push 会挂起当前协程，在约定的时间内，如果没有任何消费者消费数据，将发生超时，底层会恢复当前协程，push 调用立即返回 false，写入失败
        $ret = $channel->push(1,$timeout);
        if($ret){
            $this->status[$cid] = true;
        }
        return $ret;
    }

    /**
     * 解锁
     * @param string $lockName 锁名称
     * @param float $timeout 超时时间,-1为永久不超时 解锁$lockName. 成功后将返回true.
     * @return bool
     */
    function unlock(string $lockName,float $timeout = -1):bool
    {
        $cid = Coroutine::getCid();
        if(!isset($this->status[$cid])){
            return true;
        }
        if(!isset($this->list[$lockName])){ // 如果该锁名称被加锁，但没有通道，则初始化一个
            $this->list[$lockName] = new Channel(1);
        }
        /** @var Channel $channel */
        $channel = $this->list[$lockName];
        if($channel->isEmpty()){ // 刚创建的通道肯定为空，则需要清空加锁状态
            unset($this->status[$cid]);
            return true;
        }else{ // 存在该锁的通道，则
            // 通道为空 自动 yield 当前协程，其他生产者协程 push 生产数据后，通道可读，将重新 resume 当前协程
            $ret = $channel->pop($timeout);
            if($ret){
                unset($this->status[$cid]);
            }
            return $ret;
        }
    }

    /**
     * 尝试锁住$lockName,并在协程结束后自动解锁，禁止在死循环协程中使用该锁，会造成死锁
     * @param string $lockName 锁名称
     * @param float $timeout 超时时间，-1为永久不超时
     * @return bool 是否加锁成功
     */
    function deferLock(string $lockName,float $timeout = -1):bool
    {
        $lock = $this->lock($lockName,$timeout); // 加锁
        if($lock){
            Coroutine::defer(function ()use($lockName){
                $this->unlock($lockName); // 协程关闭的时候解锁
            });
        }
        return $lock;
    }

}