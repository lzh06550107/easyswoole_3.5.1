<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\DatabaseMigrate\Validate\Validator;
use EasySwoole\Utility\File;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * Class CreateCommand，当需要新建表、修改表、删除表时，create命令可以创建一个简单的迁移模板文件
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:30:50
 */
final class CreateCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate create';
    }

    public function desc(): string
    {
        return 'database migrate create';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('-a, --alter', 'generate alter migrate template'); // 新建表
        $commandHelp->addActionOpt('-c, --create', 'generate create migrate template'); // 修改表
        $commandHelp->addActionOpt('-d, --drop', 'generate drop migrate template'); // 删除表
        $commandHelp->addActionOpt('-t, --table', 'generate basic migrate template'); // 生成一个基础的迁移模板
        return $commandHelp;
    }

    /**
     * @return string|null
     * @throws Throwable
     */
    public function exec(): ?string
    {
        $config = MigrateManager::getInstance()->getConfig();
        [$migrateName, $migrateTemplate] = $this->getMigrateName(); // 获取创建那个表的迁移文件以及模板文件

        if (empty($migrateName)) {
            throw new InvalidArgumentException('Wrong number of parameters. Hope to get a parameter of migrate name');
        }

        $migrateClassName = $this->checkName($migrateName);

        $migrateFileName = Util::genMigrateFileName($migrateName);

        $migrateFilePath = $config->getMigratePath() . $migrateFileName;


        if (!File::touchFile($migrateFilePath, false)) { // 创建迁移文件
            throw new Exception(sprintf('Migration file "%s" creation failed, file already exists or directory is not writable', $migrateFilePath));
        }

        // 替换模板中的指定字段
        $contents = str_replace([$config->getMigrateTemplateClassName(), $config->getMigrateTemplateTableName()], $migrateClassName, file_get_contents($migrateTemplate));

        if (file_put_contents($migrateFilePath, $contents) === false) { // 写入文件中
            throw new Exception(sprintf('Migration file "%s" is not writable', $migrateFilePath));
        }

        return Color::success(sprintf('Migration file "%s" created successfully', $migrateFilePath));
    }

    /**
     * 根据参数类型来获取表名称和模板路径
     * @return array|null[]
     */
    private function getMigrateName()
    {
        $config = MigrateManager::getInstance()->getConfig();
        if ($migrateName = $this->getOpt(['c', 'create'])) {
            return [$migrateName, $config->getMigrateCreateTemplate()];
        } elseif ($migrateName = $this->getOpt(['a', 'alter'])) {
            return [$migrateName, $config->getMigrateAlterTemplate()];
        } elseif ($migrateName = $this->getOpt(['d', 'drop'])) {
            return [$migrateName, $config->getMigrateDropTemplate()];
        } elseif ($migrateName = $this->getOpt(['t', 'table'])) {
            return [$migrateName, $config->getMigrateTemplate()];
        } elseif ($migrateName = $this->getArg(1)) {
            return [$migrateName, $config->getMigrateTemplate()];
        }
        return [null, null];
    }

    /**
     * 检查表名称是否有效
     * @param $migrateName
     * @return mixed|string
     */
    private function checkName($migrateName)
    {
        if (!Validator::isValidName($migrateName)) {
            throw new InvalidArgumentException('The migrate table name can only consist of letters, numbers and underscores, and cannot start with numbers and underscore');
        }

        if (!Validator::isHumpName($migrateName)) {
            $migrateName = Util::lineConvertHump($migrateName);
        }

        if (strpos($migrateName, '_') === false) {
            $migrateName = ucfirst($migrateName);
        }

        if (Validator::validClass($migrateName, 'migrate')) {
            throw new InvalidArgumentException(sprintf('class "%s" already exists', $migrateName));
        }

        return $migrateName;
    }
}
