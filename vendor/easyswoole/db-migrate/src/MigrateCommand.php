<?php

namespace EasySwoole\DatabaseMigrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\DDL\Blueprint\Create\Table as CreateTable;
use EasySwoole\DDL\DDLBuilder;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\Command\CreateCommand;
use EasySwoole\DatabaseMigrate\Command\GenerateCommand;
use EasySwoole\DatabaseMigrate\Command\ResetCommand;
use EasySwoole\DatabaseMigrate\Command\RollbackCommand;
use EasySwoole\DatabaseMigrate\Command\RunCommand;
use EasySwoole\DatabaseMigrate\Command\SeedCommand;
use EasySwoole\DatabaseMigrate\Command\StatusCommand;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Swoole\Timer;
use Throwable;
use function Swoole\Coroutine\run;

/**
 * Class MigrateCommand，迁移一级命令
 * @package EasySwoole\DatabaseMigrate\Command
 * @author heelie.hj@gmail.com
 * @date 2020/9/4 22:16:48
 */
class MigrateCommand extends CommandAbstract
{
    private $command = [
        'create'   => CreateCommand::class, // 创建一个迁移模板
        'generate' => GenerateCommand::class, // 对已存在的表生成适配当前迁移工具的迁移模板
        'reset'    => ResetCommand::class, // 根据迁移表的记录，一次性回滚所有迁移
        'rollback' => RollbackCommand::class, // 回滚迁移记录，默认回滚上一次的迁移
        'run'      => RunCommand::class, // 对所有未迁移的文件执行迁移操作
        'seed'     => SeedCommand::class, // 数据填充工具
        'status'   => StatusCommand::class, // 展示成功迁移的数据，即为迁移表内的数据
    ];

    /**
     * 该方法仅仅是一个代理作用
     * @return string
     */
    public function commandName(): string
    {
        try {
            $option = $this->getArg(0); // 根据参数获取二级命令
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__); // 调用commandName方法
            }
        } catch (Throwable $throwable) {
        }
        return 'migrate';
    }

    /**
     * 该方法仅仅是一个代理作用
     * @return string
     */
    public function desc(): string
    {
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__);
            }
        } catch (Throwable $throwable) {
        }
        return 'database migrate tool';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__, [$commandHelp]);
            }
        } catch (Throwable $throwable) {
            //do something
        } finally {
            // $commandHelp->addActionOpt('-m, --mode[=dev]', 'Run mode');
            $commandHelp->addActionOpt('-h, --help', 'Get help');
        }
        $commandHelp->addAction('create', 'Create the migration repository');
        $commandHelp->addAction('generate', 'Generate migration repository for existing tables');
        $commandHelp->addAction('run', 'Run all migrations');
        $commandHelp->addAction('rollback', 'Rollback the last database migration');
        // $commandHelp->addAction('fresh', 'Drop all tables and re-run all migrations');
        // $commandHelp->addAction('refresh', 'Reset and re-run all migrations');
        $commandHelp->addAction('reset', 'Rollback all database migrations');
        $commandHelp->addAction('seed', 'Data filling tool');
        $commandHelp->addAction('status', 'Show the status of each migration');
        return $commandHelp;
    }

    /**
     * 执行命令
     * @return string|null
     * @throws ReflectionException
     */
    public function exec(): ?string
    {
        $closure = function () use (&$result) {
            $this->checkDefaultMigrateTable();
            $result = $this->callOptionMethod($this->getArg(0), "exec");
        };
        if (Coroutine::getCid() == -1) {
            Timer::clearAll();
            run($closure);
        } else {
            $closure();
        }
        return $result ?? "";
    }

    /**
     * 调用二级命令
     * @param $option
     * @param $method
     * @param $args
     * @return mixed
     * @throws ReflectionException
     */
    private function callOptionMethod($option, $method, $args = [])
    {
        if (!$option) {
            return CommandManager::getInstance()->displayCommandHelp('migrate');
        }
        
        if (!isset($this->command[$option])) {
            throw new InvalidArgumentException('Migration command error');
        }
        $ref = new ReflectionClass($this->command[$option]);
        return call_user_func([$ref->newInstance(), $method], ...$args);
    }

    /**
     * 检查迁移表是否存在
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    private function checkDefaultMigrateTable()
    {
        $config      = MigrateManager::getInstance()->getConfig();
        $sql         = 'SHOW TABLES LIKE \'' . $config->getMigrateTable() . '\'';
        $tableExists = MigrateManager::getInstance()->query($sql);
        empty($tableExists) and $this->createDefaultMigrateTable();
    }

    /**
     * 创建默认迁移表
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    private function createDefaultMigrateTable()
    {
        $config = MigrateManager::getInstance()->getConfig();
        $sql    = DDLBuilder::create($config->getMigrateTable(), function (CreateTable $table) {
            $table->setIfNotExists()->setTableAutoIncrement(1);
            $table->setTableEngine(Engine::INNODB);
            $table->setTableCharset(Character::UTF8MB4_GENERAL_CI);
            $table->int('id', 10)->setIsUnsigned()->setIsAutoIncrement()->setIsPrimaryKey();
            $table->varchar('migration', 255)->setColumnCharset(Character::UTF8MB4_GENERAL_CI)->setIsNotNull();
            $table->int('batch', 10)->setIsNotNull();
            $table->normal('ind_batch', 'batch');
        });

        if (MigrateManager::getInstance()->query($sql) === false) { // 执行失败抛出异常
            throw new RuntimeException('Create default migrate table fail.' . PHP_EOL . ' SQL: ' . $sql);
        }
    }
}
