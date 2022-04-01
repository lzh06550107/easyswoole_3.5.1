<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use Throwable;

/**
 * Class ResetCommand，根据迁移表的记录，一次性回滚所有迁移
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:25:14
 */
final class ResetCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate reset';
    }

    public function desc(): string
    {
        return 'database migrate reset';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    /**
     * @return string|null
     * @throws \ReflectionException
     */
    public function exec(): ?string
    {
        $waitRollbackFiles = $this->getRollbackFiles();

        $outMsg = [];

        $config = MigrateManager::getInstance()->getConfig();

        foreach ($waitRollbackFiles as $id => $file) {
            $outMsg[]  = "<brown>Migrating: </brown>{$file}";
            $startTime = microtime(true);
            $className = Util::migrateFileNameToClassName($file);
            $ref = new \ReflectionClass($className);
            $sql = call_user_func([$ref->newInstance(), 'down']); // 执行回滚方法
            if ($sql && MigrateManager::getInstance()->query($sql)) {
                MigrateManager::getInstance()->delete($config->getMigrateTable(), ["id" => $id]); // 回滚需要删除迁移记录
            }
            $outMsg[] = "<green>Migrated:  </green>{$file} (" . round(microtime(true) - $startTime, 2) . " seconds)";
        }
        $outMsg[] = "<success>Migration table rollback successfully.</success>";
        return Color::render(implode(PHP_EOL, $outMsg));
    }

    /**
     * 获取所有的回滚文件
     * @return array|string
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    private function getRollbackFiles()
    {
        $config    = MigrateManager::getInstance()->getConfig();
        $tableName = $config->getMigrateTable();
        $sql       = "SELECT `id`,`migration` FROM `{$tableName}` ORDER BY `id` DESC ";
        $readyRollbackFiles = MigrateManager::getInstance()->query($sql); // 执行sql语句
        if (empty($readyRollbackFiles)) {
            return Color::success('No files to be rollback.');
        }
        // 获取所有迁移文件
        $readyRollbackFiles = array_column($readyRollbackFiles, 'migration', 'id');

        foreach ($readyRollbackFiles as $id => $file) {
            $file = $config->getMigratePath() . $file . ".php";
            if (file_exists($file)) {
                Util::requireOnce($file);
            }
        }
        return $readyRollbackFiles;
    }

}
