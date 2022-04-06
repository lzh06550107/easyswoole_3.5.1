<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 10:49
 */

namespace EasySwoole\CodeGeneration\ModelGeneration;

use EasySwoole\CodeGeneration\ClassGeneration\ClassGeneration;
use EasySwoole\CodeGeneration\ModelGeneration\Method\AddData;
use EasySwoole\CodeGeneration\ModelGeneration\Method\GetList;
use EasySwoole\CodeGeneration\Utility\Utility;
use Nette\PhpGenerator\ClassType;

/**
 * 模型生成器
 */
class ModelGeneration extends ClassGeneration
{
    /**
     * @var $config ModelConfig
     */
    protected $config;

    function addClassData()
    {
        $this->addClassBaseContent();
        $this->addGenerationMethod(new GetList($this)); // 生成分页查询列表方法
        $this->addGenerationMethod(new AddData($this)); // 生成保存模型对象方法
    }

    /**
     * 新增基础类内容
     * addClassBaseContent
     * @author Tioncico
     * Time: 21:38
     */
    protected function addClassBaseContent(): ClassType
    {
        $table = $this->config->getTable();
        $phpClass = $this->phpClass;
        //配置表名属性
        $phpClass->addProperty('tableName', $table->getTable())
            ->setVisibility('protected');
        foreach ($table->getColumns() as $column) {
            $name = $column->getColumnName(); // 获取列名称
            $comment = $column->getColumnComment(); // 获取列注释
            $columnType = Utility::convertDbTypeToDocType($column->getColumnType()); // 获取列类型
            $phpClass->addComment("@property {$columnType} \${$name} // {$comment}"); // 类属性注释
        }
        return $phpClass;
    }

    /**
     * 根据表和文件后缀来生成类文件名称
     * @return mixed
     */
    public function getClassName()
    {
        $className = $this->config->getRealTableName() . $this->config->getFileSuffix();
        return $className;
    }

}
