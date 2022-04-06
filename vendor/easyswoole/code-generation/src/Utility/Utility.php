<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 19-6-11
 * Time: 下午9:05
 */

namespace EasySwoole\CodeGeneration\Utility;

use EasySwoole\ORM\Utility\Schema\Table;

/**
 * 工具类
 */
class Utility
{
    /**
     * 获取绝对文件路径的命名空间
     * @param $rootPath
     * @param $namespace
     * @return mixed|string
     */
    static function getNamespacePath($rootPath, $namespace)
    {
        $composerJson = json_decode(file_get_contents($rootPath . '/composer.json'), true);
        if (isset($composerJson['autoload']['psr-4']["{$namespace}\\"])) {
            return $composerJson['autoload']['psr-4']["{$namespace}\\"];
        }
        if (isset($composerJson['autoload-dev']['psr-4']["{$namespace}\\"])) {
            return $composerJson['autoload-dev']['psr-4']["{$namespace}\\"];
        }
        return "$namespace/";
    }

    /**
     * 转换数据库类型为doc文档类型
     * convertDbTypeToDocType
     * @param $fieldType
     * @return string
     * @author Tioncico
     * Time: 19:49
     */
    static function convertDbTypeToDocType($fieldType)
    {
        if (in_array($fieldType, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'])) {
            $newFieldType = 'int';
        } elseif (in_array($fieldType, ['float', 'double', 'real', 'decimal', 'numeric'])) {
            $newFieldType = 'float';
        } elseif (in_array($fieldType, ['char', 'varchar', 'text'])) {
            $newFieldType = 'string';
        } else {
            $newFieldType = 'mixed';
        }
        return $newFieldType;
    }

    /**
     * 遍历表所有列，并使用回调函数处理
     * @param Table $table
     * @param callable $callback
     */
    static function chunkTableColumn(Table $table, callable $callback)
    {
        foreach ($table->getColumns() as $column) {
            $columnName = $column->getColumnName();
            $result = $callback($column, $columnName);
            if ($result === true) {
                break;
            }
        }
    }

    /**
     * 获取模型名称
     * @param $modelClass
     * @return false|mixed|string
     */
    static function getModelName($modelClass)
    {
        $modelNameArr = (explode('\\', $modelClass));
        $modelName = end($modelNameArr);
        return $modelName;
    }
}
