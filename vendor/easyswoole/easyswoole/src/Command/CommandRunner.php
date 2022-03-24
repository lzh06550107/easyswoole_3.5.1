<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command;


use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Command\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Command\DefaultCommand\Install;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;
use EasySwoole\EasySwoole\Command\DefaultCommand\Task;
use EasySwoole\HttpAnnotation\Utility\DocCommand;
use EasySwoole\Phpunit\PhpunitCommand;

/**
 * 命令运行器，在命令入口文件中实例化并注册常用命令
 */
class CommandRunner
{
    use Singleton;

    public function __construct()
    {
        CommandManager::getInstance()->addCommand(new Install()); // 注册安装命令到命令管理器
        CommandManager::getInstance()->addCommand(new Task()); // 注册任务管理命令
        CommandManager::getInstance()->addCommand(new Crontab()); // 注册定时任务管理命令
        CommandManager::getInstance()->addCommand(new Process()); // 注册进程管理命令
        CommandManager::getInstance()->addCommand(new Server()); // 注册服务器管理命令
        CommandManager::getInstance()->addCommand(new PhpunitCommand()); // 注册单元测试命令

        //预防日后注解库DocCommand有变动影响到主库
        if (class_exists(DocCommand::class)) {
            CommandManager::getInstance()->addCommand(new DocCommand()); // 注册文档生成命令
        }

        if (class_exists(PhpunitCommand::class)) {
            CommandManager::getInstance()->addCommand(new PhpunitCommand()); // 注册单元测试命令
        }
    }

    /**
     * @var 命令执行前的回调函数
     */
    private $beforeCommand;

    public function setBeforeCommand(callable $before)
    {
        $this->beforeCommand = $before;
    }

    public function run(CallerInterface $caller): ResultInterface
    {
        if (is_callable($this->beforeCommand)) {
            // 调用命令执行前的回调，并传入要执行命令封装对象
            call_user_func($this->beforeCommand, $caller);
        }

        Utility::opCacheClear();

        $msg = CommandManager::getInstance()->run($caller);

        $result = new Result();
        $result->setMsg($msg);
        return $result;
    }
}
