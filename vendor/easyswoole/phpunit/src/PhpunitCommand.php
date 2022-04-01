<?php

namespace EasySwoole\Phpunit;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;

/**
 * 单元测试命令
 */
class PhpunitCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'phpunit';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('--no-coroutine', 'close coroutine'); // 是否开启协程进行单元测试
        return $commandHelp;
    }

    public function desc(): string
    {
        return 'PHP Unit Testing, Support Coroutine and No-Coroutine Testing.';
    }

    public function exec(): ?string
    {
        /*
         * 允许自动的执行一些初始化操作，只初始化一次，请在 EasySwoole 项目根目录下创建 phpunit.php 文件
        */
        if (file_exists(getcwd() . '/phpunit.php')) {
            require_once getcwd() . '/phpunit.php';
        }

        $argv = CommandManager::getInstance()->getOriginArgv();

        // remove phpunit
        array_shift($argv);

        $key = array_search('--no-coroutine', $argv);

        if ($key !== false) {
            $noCoroutine = true;
            unset($argv[$key]);
        } else {
            $noCoroutine = false;
        }

        $_SERVER['argv'] = $argv;
        die(Runner::run($noCoroutine));
    }

}
