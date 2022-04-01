<?php

namespace EasySwoole\DatabaseMigrate\Config;

use EasySwoole\Mysqli\Config as MysqliConfig;

/**
 * 包含迁移会用到的所有配置化信息
 *
 * Class Config
 * @package EasySwoole\DatabaseMigrate\Config
 * @author heelie.hj@gmail.com
 * @date 2021-03-31 18:12:56
 */
class Config extends MysqliConfig
{
    /** @var string default migrate table name，迁移记录的数据库表名 */
    protected $migrate_table = "migrations";

    /** @var string migrate path，迁移文件目录的绝对路径 */
    protected $migrate_path;

    /** @var string migrate template file path，迁移模板文件的绝对路径 */
    protected $migrate_template = __DIR__ . '/../Resource/migrate._php';

    /** @var string migrate template class name， 迁移模板类的类名 */
    protected $migrate_template_class_name = 'MigratorClassName';

    /** @var string migrate template table name，迁移模板类的表名 */
    protected $migrate_template_table_name = 'MigratorTableName';

    /** @var string create migrate template file path，迁移模板创建表的模板文件的绝对路径 */
    protected $migrate_create_template = __DIR__ . '/../Resource/migrate_create._php';

    /** @var string alter migrate template file path，迁移模板修改表的模板文件的绝对路径 */
    protected $migrate_alter_template = __DIR__ . '/../Resource/migrate_alter._php';

    /** @var string drop migrate template file path，迁移模板删除表的模板文件的绝对路径 */
    protected $migrate_drop_template = __DIR__ . '/../Resource/migrate_drop._php';


    /** @var string seeder path，数据填充目录绝对路径 */
    protected $seeder_path;

    /** @var string seeder template class name，数据填充模板类的类名 */
    protected $seeder_template_class_name = 'SeederClassName';

    /** @var string seeder template file path，数据填充模板文件的绝对路径 */
    protected $seeder_template = __DIR__ . '/../Resource/seeder._php';


    /** @var string generate migrate template file path，逆向生成迁移文件的模板文件绝对路径 */
    protected $migrate_generate_template = __DIR__ . '/../Resource/migrate_generate._php';

    /** @var string migrate template class name，逆向生成迁移模板SQL语句的DDL代码块 */
    protected $migrate_template_ddl_syntax = 'DDLSyntax';

    // 如果没有配置迁移路径和数据路径，则使用运行命令的当前目录
    protected function initialize(): void
    {
        if (empty($this->migrate_path)) {
            $this->migrate_path = getcwd() . '/Database/Migrates/';
        }
        if (empty($this->seeder_path)) {
            $this->seeder_path = getcwd() . '/Database/Seeds/';
        }
    }

    /**
     * @return string
     */
    public function getMigrateTable(): string
    {
        return $this->migrate_table;
    }

    /**
     * @param string $migrate_table
     */
    public function setMigrateTable(string $migrate_table): void
    {
        $this->migrate_table = $migrate_table;
    }

    /**
     * @return string
     */
    public function getMigratePath(): string
    {
        return $this->migrate_path;
    }

    /**
     * @param string $migrate_path
     */
    public function setMigratePath(string $migrate_path): void
    {
        $this->migrate_path = $migrate_path;
    }

    /**
     * @return string
     */
    public function getMigrateTemplate(): string
    {
        return $this->migrate_template;
    }

    /**
     * @param string $migrate_template
     */
    public function setMigrateTemplate(string $migrate_template): void
    {
        $this->migrate_template = $migrate_template;
    }

    /**
     * @return string
     */
    public function getMigrateTemplateClassName(): string
    {
        return $this->migrate_template_class_name;
    }

    /**
     * @param string $migrate_template_class_name
     */
    public function setMigrateTemplateClassName(string $migrate_template_class_name): void
    {
        $this->migrate_template_class_name = $migrate_template_class_name;
    }

    /**
     * @return string
     */
    public function getMigrateTemplateTableName(): string
    {
        return $this->migrate_template_table_name;
    }

    /**
     * @param string $migrate_template_table_name
     */
    public function setMigrateTemplateTableName(string $migrate_template_table_name): void
    {
        $this->migrate_template_table_name = $migrate_template_table_name;
    }

    /**
     * @return string
     */
    public function getMigrateCreateTemplate(): string
    {
        return $this->migrate_create_template;
    }

    /**
     * @param string $migrate_create_template
     */
    public function setMigrateCreateTemplate(string $migrate_create_template): void
    {
        $this->migrate_create_template = $migrate_create_template;
    }

    /**
     * @return string
     */
    public function getMigrateAlterTemplate(): string
    {
        return $this->migrate_alter_template;
    }

    /**
     * @param string $migrate_alter_template
     */
    public function setMigrateAlterTemplate(string $migrate_alter_template): void
    {
        $this->migrate_alter_template = $migrate_alter_template;
    }

    /**
     * @return string
     */
    public function getMigrateDropTemplate(): string
    {
        return $this->migrate_drop_template;
    }

    /**
     * @param string $migrate_drop_template
     */
    public function setMigrateDropTemplate(string $migrate_drop_template): void
    {
        $this->migrate_drop_template = $migrate_drop_template;
    }

    /**
     * @return string
     */
    public function getSeederPath(): string
    {
        return $this->seeder_path;
    }

    /**
     * @param string $seeder_path
     */
    public function setSeederPath(string $seeder_path): void
    {
        $this->seeder_path = $seeder_path;
    }

    /**
     * @return string
     */
    public function getSeederTemplateClassName(): string
    {
        return $this->seeder_template_class_name;
    }

    /**
     * @param string $seeder_template_class_name
     */
    public function setSeederTemplateClassName(string $seeder_template_class_name): void
    {
        $this->seeder_template_class_name = $seeder_template_class_name;
    }

    /**
     * @return string
     */
    public function getSeederTemplate(): string
    {
        return $this->seeder_template;
    }

    /**
     * @param string $seeder_template
     */
    public function setSeederTemplate(string $seeder_template): void
    {
        $this->seeder_template = $seeder_template;
    }

    /**
     * @return string
     */
    public function getMigrateGenerateTemplate(): string
    {
        return $this->migrate_generate_template;
    }

    /**
     * @param string $migrate_generate_template
     */
    public function setMigrateGenerateTemplate(string $migrate_generate_template): void
    {
        $this->migrate_generate_template = $migrate_generate_template;
    }

    /**
     * @return string
     */
    public function getMigrateTemplateDdlSyntax(): string
    {
        return $this->migrate_template_ddl_syntax;
    }

    /**
     * @param string $migrate_template_ddl_syntax
     */
    public function setMigrateTemplateDdlSyntax(string $migrate_template_ddl_syntax): void
    {
        $this->migrate_template_ddl_syntax = $migrate_template_ddl_syntax;
    }
}
