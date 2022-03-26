<?php

namespace EasySwoole\Component\CoroutineRunner;

use Swoole\Coroutine\Channel;
use Swoole\Coroutine;

// 协程运行器
class Runner
{
    protected $concurrency; // 控制协程并发数
    protected $taskChannel;
    protected $isRunning = false; // 运行器是否正在运行
    protected $runningNum = 0; // 正在运行的协程数
    protected $onException; // 异常处理函数
    /** @var callable|null */
    protected $onLoop; // 每次循环都回调的函数

    function __construct($concurrency = 64,$taskChannelSize = 1024)
    {
        $this->concurrency = $concurrency; // 总共可以有多少个协程运行
        $this->taskChannel = new Channel($taskChannelSize); // 总共可以添加多少个任务
    }

    function setOnException(callable $call):Runner
    {
        $this->onException = $call;
        return $this;
    }

    function setOnLoop(callable $call):Runner
    {
        $this->onLoop = $call;
        return $this;
    }

    function status():array
    {
        return [
            'queueSize'=>$this->taskChannel->length(), // 通道队列的长度
            'concurrency'=>$this->concurrency, // 并发数
            'runningNum'=>$this->runningNum, // 正在运行定时任务数
            'isRunning'=>$this->isRunning // 运行器是否正在运行
        ];
    }

    function addTask(Task $task):Runner
    {
        $this->taskChannel->push($task);
        return $this;
    }

    function queueSize():int
    {
        return $this->taskChannel->length();
    }

    function start(float $waitTime = 30) // 最长运行时间
    {
        if(!$this->isRunning){
            $this->isRunning = true;
            $this->runningNum = 0;
        }
        if($waitTime <=0){
            $waitTime = PHP_INT_MAX;
        }
        $start = time();
        while ($waitTime > 0){
            if(is_callable($this->onLoop)){
                call_user_func($this->onLoop,$this); // 调用循环回调函数
            }
            // 当前正在运行任务数小于并发协程数且任务通道有任务，则执行
            if($this->runningNum <= $this->concurrency && !$this->taskChannel->isEmpty()){
                $task = $this->taskChannel->pop(0.01);// 获取一个任务
                if($task instanceof Task){
                    Coroutine::create(function ()use($task){ // 创建子协程来处理任务
                        $this->runningNum++;
                        $ret = null;
                        $task->setStartTime(microtime(true)); // 记录任务开始执行时间
                        try{
                            $ret = call_user_func($task->getCall()); // 执行任务
                            $task->setResult($ret); // 设置任务结果
                            // 如果返回值不是false，则调用你任务执行成功回调方法
                            if($ret !== false && is_callable($task->getOnSuccess())){
                                call_user_func($task->getOnSuccess(),$task);
                            }else if(is_callable($task->getOnFail())){ // 如果任务执行失败，则执行任务执行失败回调方法
                                call_user_func($task->getOnFail(),$task);
                            }
                        }catch (\Throwable $throwable){
                            if(is_callable($this->onException)){ // 如果任务执行异常，则执行任务异常回调方法
                                call_user_func($this->onException,$throwable,$task);
                            }else{
                                throw $throwable;
                            }
                        }finally{
                            $this->runningNum--; // 当前正在运行任务数减一
                        }
                    });
                }
            }else{ // 当前正在运行任务数达到最大并发协程数，或者通道任务为空，则检测运行器执行是否超时
                if(time() - $start > $waitTime){
                    break; // 超时退出执行器的死循环
                }else if($this->taskChannel->isEmpty() && $this->runningNum <= 0){ // 没有等待执行的任务且正在执行的任务也为空，则退出执行器的死循环
                    break;
                }else{
                    /*
                     * 最小调度粒度为0.01
                     */
                    Coroutine::sleep(0.01); // 切换协程
                }
            }
        }
        $this->isRunning = false;
        $this->runningNum = 0;
    }
}