<?php


namespace EasySwoole\SyncInvoker;

use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use Swoole\Server;

/**
 * 同步调用管理器
 */
class SyncInvoker
{
    protected $config; // 配置对象

    public function __construct(Config $config = null)
    {
        if($config == null){
            $config = new Config();
        }
        $this->config = $config;
    }

    function getConfig():Config
    {
        return $this->config;
    }

    /**
     * 创建同步调用工作进程
     * @return array
     * @throws \EasySwoole\Component\Process\Exception
     */
    public function __generateWorkerProcess():array
    {
        $ret = [];
        for ($i = 0;$i < $this->config->getWorkerNum();$i++){
            $config = new UnixProcessConfig(); // unix socket 配置对象
            $config->setProcessGroup("{$this->config->getServerName()}.SyncInvoker");
            $config->setProcessName("{$this->config->getServerName()}.SyncInvoker.Worker.{$i}");
            $config->setSocketFile($this->getSocket($i));
            $config->setAsyncCallback($this->config->isAsyncAccept());
            $config->setArg([
                'workerIndex'=>$i,
                'config'=>$this->config
            ]);
            $ret[] = new Worker($config); // 创建同步调用工作进程
        }
        return $ret;
    }

    /**
     * 调用同步方法
     * @param float|null $timeout
     * @return Client
     */
    function invoke(float $timeout = null)
    {
        if($timeout === null){
            $timeout = $this->config->getTimeout();
        }
        mt_srand();
        $id = mt_rand(0,$this->config->getWorkerNum() -1);
        //
        return new Client($this->getSocket($id),$this->getConfig()->getMaxPackageSize(),$timeout);
    }

    private function getSocket(int $index)
    {
        return "{$this->config->getTempDir()}/SyncInvoker.Worker.{$index}.sock";
    }

    /**
     * 关联同步调用工作进程到主服务中
     * @param Server $server
     * @return void
     */
    public function attachServer(Server $server)
    {
        $list = $this->__generateWorkerProcess();
        /** @var AbstractUnixProcess $process */
        foreach ($list as $process){
            $server->addProcess($process->getProcess());
        }
    }
}