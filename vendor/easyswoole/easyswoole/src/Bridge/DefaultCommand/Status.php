<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;

/**
 * 桥接进程中获取状态类命令
 */
class Status extends AbstractCommand
{
    public function commandName(): string
    {
        return 'status';
    }

    /**
     * 获取服务器状态命令
     * @param Package $package
     * @param Package $responsePackage
     * @return bool
     */
    protected function server(Package $package, Package $responsePackage)
    {
        $data = ServerManager::getInstance()->getSwooleServer()->stats(); // 桥接子进程共享主进程中创建的服务管理器对象
        $data['runMode'] = Core::getInstance()->runMode();
        $responsePackage->setArgs($data);
        return true;
    }

    /**
     * 获取任务状态命令
     * @param Package $package
     * @param Package $responsePackage
     * @return bool
     */
    protected function task(Package $package, Package $responsePackage)
    {
        $responsePackage->setArgs(TaskManager::getInstance()->status()); // 桥接子进程共享主进程中创建的任务管理器对象
        return true;
    }
}
