<?php


namespace EasySwoole\Component;

use Swoole\Coroutine\Channel;
use Swoole\Coroutine;

/**
 * 并发执行某些不相干的请求，并得到结果
 */
class Csp
{
    private $chan; // 通道
    private $count = 0; // 并发任务数
    private $success = 0; // 执行成功的任务数
    private $task = []; // 任务回调函数

    /**
     * 初始化通道对象
     * @param int $size
     */
    function __construct(int $size = 8)
    {
        $this->chan = new Channel($size);
    }

    /**
     * 添加并发任务
     * @param $itemName 任务名称
     * @param callable $call 任务回调函数
     * @return $this
     */
    function add($itemName,callable $call):Csp
    {
        $this->count = 0;
        $this->success = 0;
        $this->task[$itemName] = $call;
        return $this;
    }

    /**
     * 返回成功任务数
     * @return int
     */
    function successNum():int
    {
        return $this->success;
    }

    /**
     * 启动并发执行任务
     * @param float|null $timeout 任务执行超时时间
     * @return array
     */
    function exec(?float $timeout = 5)
    {
        if($timeout <= 0){
            $timeout = PHP_INT_MAX;
        }
        $this->count = count($this->task);
        foreach ($this->task as $key => $call){ // 循环创建协程，key是任务名称
            Coroutine::create(function ()use($key,$call){
                $data = call_user_func($call);
                $this->chan->push([ // 把执行结果推送到通道中
                    'key'=>$key, // 任务名称
                    'result'=>$data // 执行结果
                ]);
            });
        }
        $result = [];
        $start = microtime(true);
        while($this->count > 0)
        {
            $temp = $this->chan->pop(1); // 循环等待，通道没有数据会切换协程
            if(is_array($temp)){
                $key = $temp['key']; // 任务名称
                $result[$key] = $temp['result']; // 任务执行结果
                $this->count--; // 任务数减一
                $this->success++; // 任务执行成功数加一
            }
            if(microtime(true) - $start > $timeout){
                break;
            }
        }
        return $result;
    }
}