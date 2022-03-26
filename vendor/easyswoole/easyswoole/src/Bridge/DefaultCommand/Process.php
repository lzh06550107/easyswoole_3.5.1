<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;

/**
 * 桥接进程中获取进程信息类命令
 */
class Process extends AbstractCommand
{
    public function commandName(): string
    {
        return 'process';
    }

    protected function info(Package $package, Package $responsePackage)
    {
        $responsePackage->setArgs(Manager::getInstance()->info()); // 桥接子进程共享主进程中创建的进程管理器对象
        return true;
    }
}
