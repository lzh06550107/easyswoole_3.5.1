<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-21
 * Time: 09:39
 */

namespace EasySwoole\CodeGeneration\UnitTest\Method;

use EasySwoole\CodeGeneration\ClassGeneration\MethodAbstract;
use EasySwoole\CodeGeneration\UnitTest\UnitTestGeneration;
use EasySwoole\CodeGeneration\Utility\Utility;
use EasySwoole\ORM\Utility\Schema\Column;
use EasySwoole\Utility\Random;

/**
 * 单元测试方法抽象类
 */
abstract class UnitTestMethod extends MethodAbstract
{
    /**
     * @var UnitTestGeneration
     */
    protected $classGeneration;

    protected $methodName;
    protected $actionName;

    /**
     * 获取方法名称
     * @return string
     */
    function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * 获取表的测试数据，根据表列的类型自动生成
     * @param $variableName
     * @return string
     */
    protected function getTableTestData($variableName = 'data')
    {
        $data = '';

        Utility::chunkTableColumn($this->classGeneration->getConfig()->getTable(), function (Column $column, string $columnName) use (&$data, $variableName) {
            if ($columnName == $this->classGeneration->getConfig()->getTable()->getPkFiledName()) {
                return false;
            }
            $value = $this->randColumnTypeValue($column);
            $data .= "\${$variableName}['{$columnName}'] = {$value};\n";
        });
        return $data;
    }

    /**
     * 根据列类型，随机生成测试数据
     * @param Column $column
     * @return string|null
     */
    protected function randColumnTypeValue(Column $column)
    {
        $columnType = Utility::convertDbTypeToDocType($column->getColumnType());
        $value = null;
        switch ($columnType) {
            case "int":
                if ($column->getColumnLimit() <= 3) {
                    $value = 'mt_rand(0, 3)';
                } else {
                    $value = "mt_rand(10000, 99999)";
                }
                break;
            case "float":
                if ($column->getColumnLimit() <= 3) {
                    $value = "mt_rand(10, 30) / 10";
                } else {
                    $value = "mt_rand(100000, 999999) / 10";
                }
                break;
            case "string":
                $value = '"测试文本".' . "Random::character(6)";
                break;
            case "mixed":
                $value = "null";
                break;
        }
        return $value;
    }
}
