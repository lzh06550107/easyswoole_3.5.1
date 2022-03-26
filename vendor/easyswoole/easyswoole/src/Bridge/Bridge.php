<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Bridge\Container;
use EasySwoole\Component\Singleton;
use EasySwoole\Bridge\Bridge as BridgeServer;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Process;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Status;

/**
 * 桥接管理器
 */
class Bridge extends BridgeServer
{
    use Singleton;

    /**
     * 在主服务启动时，初始化桥接管理器并注册常用命令
     * @param Container|null $container
     */
    function __construct(Container $container = null)
    {
        parent::__construct($container); // 如果不传入容器，会初始化一个容器对象
        $this->getCommandContainer()->set(new Crontab()); // 存储定时任务命令
        $this->getCommandContainer()->set(new Process()); // 存储进程命令
        $this->getCommandContainer()->set(new Status()); // 存储状态命令
        $this->setSocketFile(EASYSWOOLE_TEMP_DIR . '/bridge.sock'); // 设置桥接通讯文件
    }
}
