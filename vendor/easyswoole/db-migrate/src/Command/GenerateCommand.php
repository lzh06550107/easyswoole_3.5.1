<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\Color;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\DDLSyntax\DDLColumnSyntax;
use EasySwoole\DatabaseMigrate\DDLSyntax\DDLForeignSyntax;
use EasySwoole\DatabaseMigrate\DDLSyntax\DDLIndexSyntax;
use EasySwoole\DatabaseMigrate\DDLSyntax\DDLTableSyntax;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\Utility\File;
use RuntimeException;
use Exception;
use Throwable;

/**
 * Class GenerateCommand，对于已经启动的项目没有做版本迁移，generate命令可以对已存在的表逆向生成迁移文件
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/19 00:24:58
 */
final class GenerateCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate generate';
    }

    public function desc(): string
    {
        return 'database migrate generate';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('-t, --table', 'Generate the migration repository of the specified table, multiple tables can be separated by ","'); // table 指定要生成迁移模板的表，多个表用 ',' 隔开
        $commandHelp->addActionOpt('-i, --ignore', 'Tables that need to be excluded when generate the migration repository, multiple tables can be separated by ","'); // ignore 指定要忽略生成迁移模板的表，多个表用 ',' 隔开
        return $commandHelp;
    }

    /**
     * 逆向生成迁移文件
     * @return string|null
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    public function exec(): ?string
    {
        // need to migrate
        $migrateTables = $this->getExistsTables();
        if ($specifiedTables = $this->getOpt(['t', 'table'])) {
            $specifiedTables = explode(',', $specifiedTables); // 这些表需要存在才行
            array_walk($specifiedTables, function ($tableName) use ($migrateTables) {
                if (!in_array($tableName, $migrateTables)) {
                    throw new RuntimeException(sprintf('Table: "%s" not found.', $tableName));
                }
            });
            $migrateTables = $specifiedTables;
        }

        // ignore table
        $ignoreTables = $this->getIgnoreTables();
        $allTables = array_diff($migrateTables, $ignoreTables); // 最终需要逆向生成的表
        if (empty($allTables)) {
            throw new RuntimeException('No table found.');
        }
        $batchNo = (new RunCommand)->getBatchNo(); // 获取迁移执行批次号
        $outMsg = [];
        foreach ($allTables as $tableName) {
            $this->generate($tableName, $batchNo, $outMsg);
        }
        $outMsg[] = '<success>All table migration repository generation completed.</success>';
        return Color::render(join(PHP_EOL, $outMsg));
    }

    /**
     * 逆向生成迁移文件
     * @param $tableName
     * @param $batchNo
     * @param $outMsg
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    private function generate($tableName, $batchNo, &$outMsg)
    {
        $config = MigrateManager::getInstance()->getConfig();
        $migrateClassName = 'Create' . ucfirst(Util::lineConvertHump($tableName)); // 生成类名称
        $migrateFileName  = Util::genMigrateFileName($migrateClassName); // 生成迁移文件名称
        $migrateFilePath  = $config->getMigratePath() . $migrateFileName; // 迁移文件绝对路径

        $fileName  = basename($migrateFileName, '.php');
        $outMsg[]  = "<brown>Generating: </brown>{$fileName}";
        $startTime = microtime(true);

        $tableSchema     = $config->getDatabase();
        // 根据表逆向生成DDL语法文本
        $createTableDDl  = str_replace(PHP_EOL,
            str_pad(PHP_EOL, strlen(PHP_EOL) + 12, ' ', STR_PAD_RIGHT),
            join(PHP_EOL, array_filter([
                    DDLTableSyntax::generate($tableSchema, $tableName), // 生成DDL表操作
                    DDLColumnSyntax::generate($tableSchema, $tableName), // 生成DDL列操作
                    DDLIndexSyntax::generate($tableSchema, $tableName), // 生成DDL索引操作
                    DDLForeignSyntax::generate($tableSchema, $tableName), // 生成DDL外键操作
                ])
            )
        );

        if (!File::touchFile($migrateFilePath, false)) { // 生成迁移文件
            throw new Exception(sprintf('Migration file "%s" creation failed, file already exists or directory is not writable', $migrateFilePath));
        }

        $contents = str_replace(
            [
                $config->getMigrateTemplateClassName(),
                $config->getMigrateTemplateTableName(),
                $config->getMigrateTemplateDdlSyntax()
            ],
            [
                $migrateClassName,
                $tableName,
                $createTableDDl
            ],
            file_get_contents($config->getMigrateGenerateTemplate())
        );
        if (file_put_contents($migrateFilePath, $contents) === false) {
            throw new Exception(sprintf('Migration file "%s" is not writable', $migrateFilePath));
        }
        // 记录迁移日志
        MigrateManager::getInstance()->insert($config->getMigrateTable(),
            [
                "migration" => $fileName,
                "batch" => $batchNo
            ]
        );
        $outMsg[] = "<green>Generated:  </green>{$fileName} (" . round(microtime(true) - $startTime, 2) . " seconds)";
    }

    /**
     * already exists tables，查询数据库中所有已经存在的表
     *
     * @return array
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    protected function getExistsTables(): array
    {
        $result = MigrateManager::getInstance()->query('SHOW TABLES;');
        if (empty($result)) {
            throw new RuntimeException('No table found.');
        }
        return array_map('current', $result);
    }

    /**
     * ignore tables，忽略表是已经迁移表和用户传入表合集
     *
     * @return array
     */
    protected function getIgnoreTables(): array
    {
        $config = MigrateManager::getInstance()->getConfig();
        $ignoreTables = [$config->getMigrateTable()];
        if ($ignore = $this->getOpt(['i', 'ignore'])) {
            return array_merge($ignoreTables, explode(',', $ignore));
        }
        return $ignoreTables;
    }
}
