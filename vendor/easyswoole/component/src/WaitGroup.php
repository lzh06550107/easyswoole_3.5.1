<?php


namespace EasySwoole\Component;


use Swoole\Coroutine\Channel;

/**
 * 进程内协程同步工具
 */
class WaitGroup
{
    private $count = 0; // 并发协程数
    /** @var Channel  */
    private $channel; // 通道对象
    private $success = 0;
    private $size;

    public function __construct(int $size = 128)
    {
        $this->size = $size;
        $this->reset();
    }

    public function add()
    {
        $this->count++;
    }

    function successNum():int
    {
        return $this->success;
    }

    /**
     * 完成一个协程，推送一个记录到通道
     */
    public function done()
    {
        $this->channel->push(1);
    }

    /**
     * 等待所有的协程执行完成
     * @param float|null $timeout
     */
    public function wait(?float $timeout = 15)
    {
        if($timeout <= 0){
            $timeout = PHP_INT_MAX;
        }
        $this->success = 0;
        $left = $timeout;
        while(($this->count > 0) && ($left > 0))
        {
            $start = round(microtime(true),3);
            if($this->channel->pop($left) === 1) // pop方法在通道为空时，自定切换协程
            {
                $this->count--; // 并发
                $this->success++;
            }
            $left = $left - (round(microtime(true),3) - $start);
        }
    }

    /**
     * 关闭通道，清空对象属性并重新创建对象
     */
    function reset()
    {
        $this->close();
        $this->count = 0;
        $this->success = 0;
        $this->channel = new Channel($this->size);
    }

    function close()
    {
        if($this->channel){
            $this->channel->close();
            $this->channel = null;
        }
    }

    function __destruct()
    {
        $this->close();
    }
}