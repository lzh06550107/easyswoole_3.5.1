<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 01:41
 */

namespace EasySwoole\Component\Process;
use EasySwoole\Component\Timer;
use Swoole\Coroutine;
use Swoole\Event;
use Swoole\Process;
use Swoole\Coroutine\Scheduler;

/**
 * 进程抽象类
 */
abstract class AbstractProcess
{
    private $swooleProcess; // 保存swoole进程对象
    /** @var Config */
    private $config; // 进程配置对象


    /**
     * name  args  false 2 true
     * AbstractProcess constructor.
     * @param string $processName
     * @param null $arg
     * @param bool $redirectStdinStdout
     * @param int $pipeType
     * @param bool $enableCoroutine
     */
    function __construct(...$args)
    {
        $arg1 = array_shift($args);
        if($arg1 instanceof Config){
            $this->config = $arg1;
        }else{
            $this->config = new Config();
            $this->config->setProcessName($arg1); // 进程名称
            $arg = array_shift($args);
            $this->config->setArg($arg); // 
            $redirectStdinStdout = (bool)array_shift($args) ?: false;
            $this->config->setRedirectStdinStdout($redirectStdinStdout);
            $pipeType = array_shift($args);
            $pipeType = $pipeType === null ? Config::PIPE_TYPE_SOCK_DGRAM : $pipeType;
            $this->config->setPipeType($pipeType);
            $enableCoroutine = (bool)array_shift($args) ?: false;
            $this->config->setEnableCoroutine($enableCoroutine);
        }
        // 实例化swoole进程
        $this->swooleProcess = new Process([$this,'__start'],$this->config->isRedirectStdinStdout(),$this->config->getPipeType(),$this->config->isEnableCoroutine()); // 进程中是否开启协程
        Manager::getInstance()->addProcess($this,false); // 需要在程序中其它地方手动关联进程到服务中
    }

    /**
     * 获取swoole进程
     * @return Process
     */
    public function getProcess():Process
    {
        return $this->swooleProcess;
    }

    /**
     * 添加周期定时器
     * @param $ms
     * @param callable $call
     * @return int|null
     */
    public function addTick($ms,callable $call):?int
    {
        return Timer::getInstance()->loop(
            $ms,$call
        );
    }

    /**
     * 清理指定定时器
     * @param int $timerId
     * @return int|null
     */
    public function clearTick(int $timerId):?int
    {
        return Timer::getInstance()->clear($timerId);
    }

    /**
     * 添加一次性延时定时器
     * @param $ms
     * @param callable $call
     * @return int|null
     */
    public function delay($ms,callable $call):?int
    {
        return Timer::getInstance()->after($ms,$call);
    }

    /*
     * 服务启动后才能获得到pid
     */
    public function getPid():?int
    {
        if(isset($this->swooleProcess->pid)){
            return $this->swooleProcess->pid;
        }else{
            return null;
        }
    }

    // swoole子进程创建成功后要回调执行的函数
    function __start(Process $process)
    {
        $table = Manager::getInstance()->getProcessTable();
        // 把进程信息写入进程共享内存表中
        $table->set($process->pid,[
            'pid'=>$process->pid,
            'name'=>$this->config->getProcessName(),
            'group'=>$this->config->getProcessGroup(),
            'startUpTime'=>time(),
            "hash"=>spl_object_hash($this->getProcess())
        ]);
        // 添加一个周期为1s定时器来统计内存使用情况
        \Swoole\Timer::tick(1*1000,function ()use($table,$process){
            $table->set($process->pid,[
                'memoryUsage'=>memory_get_usage(),
                'memoryPeakUsage'=>memory_get_peak_usage(true)
            ]);
        });
        // 设置进程名称
        if(!in_array(PHP_OS,['Darwin','CYGWIN','WINNT']) && !empty($this->getProcessName())){
            $process->name($this->getProcessName());
        }
        // swoole_event_add 函数用于将一个 socket 加入到 swoole 的 reactor 事件监听中
        swoole_event_add($this->swooleProcess->pipe, function(){ // pipe是unixSocket 的文件描述符
            try{
                $this->onPipeReadable($this->swooleProcess); // 子类可重载
            }catch (\Throwable $throwable){
                $this->onException($throwable); // 子类可重载
            }
        });
        // 监听进程关闭信号
        Process::signal(SIGTERM,function ()use($process){
            swoole_event_del($process->pipe); // 从reactor中移除监听的socket
            try{
                $this->onSigTerm(); // 子类可以重载该方法
            }catch (\Throwable $throwable){
                $this->onException($throwable); // 子类可重载
            } finally {
                \Swoole\Timer::clearAll(); // 清除所有定时器
                Process::signal(SIGTERM, null); // 清理监听关闭信号
                Event::exit(); // 事件循环退出
            }
        });
        // 当我们的脚本执行完成或意外死掉导致PHP执行即将关闭时,我们注册的这个函数将会被调用
        register_shutdown_function(function ()use($table,$process) {
            if($table){
                $table->del($process->pid); // 在进程管理表中删除该进程
            }
            $schedule = new Scheduler(); // 协程调度器
            $schedule->add(function (){
                $channel = new Coroutine\Channel(1);
                Coroutine::create(function ()use($channel){
                    try{
                        $this->onShutDown(); // 子类重载该方法
                    }catch (\Throwable $throwable){
                        $this->onException($throwable);
                    }
                    $channel->push(1);
                });
                $channel->pop($this->config->getMaxExitWaitTime()); // 等待最大超时事件，或者调用子类onShutDown方法后继续执行
                \Swoole\Timer::clearAll(); // 清除所有定时器
                Event::exit(); // 事件循环退出
            });
            $schedule->start(); // 启动协程调度器
            \Swoole\Timer::clearAll();
            Event::exit();
        });
        try{
            $this->run($this->config->getArg()); // 调用子类run方法
        }catch (\Throwable $throwable){
            $this->onException($throwable);
        }
        Event::wait(); // 启动事件监听
    }

    public function getArg()
    {
        return $this->config->getArg();
    }

    public function getProcessName()
    {
        return $this->config->getProcessName();
    }

    public function getConfig():Config
    {
        return $this->config;
    }

    protected function onException(\Throwable $throwable,...$args){
        throw $throwable;
    }

    protected abstract function run($arg);

    protected function onShutDown()
    {

    }

    protected function onSigTerm()
    {

    }

    protected function onPipeReadable(Process $process)
    {
        /*
         * 由于Swoole底层使用了epoll的LT模式，因此swoole_event_add添加的事件监听，
         * 在事件发生后回调函数中必须调用read方法读取socket中的数据，否则底层会持续触发事件回调。
         */
        $process->read(); // 从管道中读取数据，同步阻塞读取，没有使用读取的数据
    }
}
