<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 10:49
 */

namespace EasySwoole\CodeGeneration\UnitTest;

use EasySwoole\CodeGeneration\ClassGeneration\ClassGeneration;
use EasySwoole\CodeGeneration\UnitTest\Method\Add;
use EasySwoole\CodeGeneration\UnitTest\Method\Del;
use EasySwoole\CodeGeneration\UnitTest\Method\GetList;
use EasySwoole\CodeGeneration\UnitTest\Method\GetOne;
use EasySwoole\CodeGeneration\UnitTest\Method\Update;
use EasySwoole\CodeGeneration\Utility\Utility;
use EasySwoole\Utility\Random;
use Nette\PhpGenerator\ClassType;

/**
 * 单元测试生成类
 */
class UnitTestGeneration extends ClassGeneration
{
    /**
     * @var $config UnitTestConfig
     */
    protected $config;

    function addClassData()
    {
        $this->phpClass->addProperty('modelName', $this->getApiUrl());
        $this->phpNamespace->addUse($this->config->getModelClass()); // 添加模型类命名空间
        $this->phpNamespace->addUse(Random::class); // 添加随机类命名空间
        $this->addGenerationMethod(new Add($this)); // 添加数据测试方法
        $this->addGenerationMethod(new GetOne($this)); // 查询一条数据测试方法
        $this->addGenerationMethod(new Update($this)); // 更新数据测试方法
        $this->addGenerationMethod(new GetList($this)); // 查询多天数据测试方法
        $this->addGenerationMethod(new Del($this)); // 删除一条数据测试方法
    }

    /**
     * 获取控制器的 api url路径
     * @return array|mixed|string|string[]
     */
    protected function getApiUrl()
    {
        $baseNamespace = $this->config->getControllerClass();
        $modelName = str_replace(['App\\HttpController', '\\'], ['', '/'], $baseNamespace);
        return $modelName;
    }

    /**
     * 获取类文件名称
     * @return mixed
     */
    public function getClassName()
    {
        $className = $this->config->getRealTableName() . $this->config->getFileSuffix();
        return $className;
    }

}
