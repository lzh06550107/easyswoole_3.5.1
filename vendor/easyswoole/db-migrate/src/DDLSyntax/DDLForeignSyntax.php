<?php

namespace EasySwoole\DatabaseMigrate\DDLSyntax;

use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Utility\Util;
use EasySwoole\Mysqli\Exception\Exception;

/**
 * 生成DDL外键操作
 */
class DDLForeignSyntax
{
    /**
     * 根据表的外键来生成DDL索引操作语句
     * @param string $tableSchema
     * @param string $tableName
     * @return string
     * @throws Exception
     * @throws \Throwable
     */
    public static function generate(string $tableSchema, string $tableName)
    {
        $foreAttrs  = self::getForeignAttribute($tableSchema, $tableName);
        $foreAttrs  = Util::arrayBindKey($foreAttrs, 'CONSTRAINT_NAME');
        $foreignDDl = array_map([__CLASS__, 'genForeignDDLSyntax'], $foreAttrs);
        return join(PHP_EOL, $foreignDDl);
    }

    /**
     * 获取表的外键信息
     * @param string $tableSchema
     * @param string $tableName
     * @return mixed|void
     * @throws Exception
     * @throws \Throwable
     */
    private static function getForeignAttribute(string $tableSchema, string $tableName)
    {
        $columns = join(',', [
            '`CONSTRAINT_NAME`',
            '`COLUMN_NAME`',
            '`TABLE_NAME`',
            '`TABLE_SCHEMA`',
            '`REFERENCED_TABLE_NAME`',
            '`REFERENCED_COLUMN_NAME`',
        ]);
        $sql     = "SELECT {$columns}
                FROM `information_schema`.`KEY_COLUMN_USAGE` 
                WHERE `TABLE_SCHEMA`='{$tableSchema}' 
                AND `TABLE_NAME`='{$tableName}'
                AND `REFERENCED_TABLE_SCHEMA`='{$tableSchema}';";
        return MigrateManager::getInstance()->query($sql);
    }

    /**
     * 获取表的外键约束
     * @param string $constraintSchema
     * @param string $constraintName
     * @param string $tableName
     * @param string $referencedTableName
     * @return mixed|void
     * @throws Exception
     * @throws \Throwable
     */
    private static function getForeignConstraints(string $constraintSchema, string $constraintName, string $tableName, string $referencedTableName)
    {
        $columns = join(',', [
            '`DELETE_RULE`',
            '`UPDATE_RULE`',
        ]);
        $sql     = "SELECT {$columns}
                FROM `information_schema`.`REFERENTIAL_CONSTRAINTS` 
                WHERE `CONSTRAINT_SCHEMA`='{$constraintSchema}' 
                AND `CONSTRAINT_NAME`='{$constraintName}'
                AND `TABLE_NAME`='{$tableName}'
                AND `REFERENCED_TABLE_NAME`='{$referencedTableName}';";
        return MigrateManager::getInstance()->query($sql);
    }

    /**
     * 生成表的DDL外键操作语句
     * @param $indAttrs
     * @return string
     * @throws Exception
     * @throws \Throwable
     */
    private static function genForeignDDLSyntax($indAttrs): string
    {
        $tableName            = current($indAttrs)['TABLE_NAME'];
        $tableSchema          = current($indAttrs)['TABLE_SCHEMA'];
        $constraintName       = current($indAttrs)['CONSTRAINT_NAME'];
        $columnName           = array_column($indAttrs, 'COLUMN_NAME');
        $referencedTableName  = current($indAttrs)['REFERENCED_TABLE_NAME'];
        $referencedColumnName = array_column($indAttrs, 'REFERENCED_COLUMN_NAME');

        $ddlSyntax = "\$table->foreign('{$constraintName}', ['" . join('\', \'', $columnName) . "'], '{$referencedTableName}', ['" . join('\', \'', $referencedColumnName) . "'])";

        $foreignConstraints = self::getForeignConstraints($tableSchema, $constraintName, $tableName, $referencedTableName);

        if ($foreignConstraint = current($foreignConstraints)) {
            $ddlSyntax .= $foreignConstraint['DELETE_RULE'] ? "->setOnDelete('{$foreignConstraint['DELETE_RULE']}')" : '';
            $ddlSyntax .= $foreignConstraint['UPDATE_RULE'] ? "->setOnUpdate('{$foreignConstraint['UPDATE_RULE']}')" : '';
        }
        return $ddlSyntax . ';';
    }
}
