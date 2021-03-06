<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 16:41
 */

namespace EasySwoole\CodeGeneration;

use EasySwoole\CodeGeneration\ControllerGeneration\ControllerConfig;
use EasySwoole\CodeGeneration\ControllerGeneration\ControllerGeneration;
use EasySwoole\CodeGeneration\ModelGeneration\ModelGeneration;
use EasySwoole\CodeGeneration\ModelGeneration\ModelConfig;
use EasySwoole\CodeGeneration\UnitTest\UnitTestConfig;
use EasySwoole\CodeGeneration\UnitTest\UnitTestGeneration;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\ORM\Db\Connection;
use PHPUnit\Framework\TestCase;

/**
 * 代码生成管理器
 */
class CodeGeneration
{
    protected $schemaInfo;
    protected $modelGeneration;
    protected $controllerGeneration;
    protected $modelBaseNameSpace = "App\\Model";
    protected $controllerBaseNameSpace = "App\\HttpController";
    protected $unitTestBaseNameSpace = "UnitTest";
    protected $rootPath;

    function __construct(string $tableName, Connection $connection)
    {
        $tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection, $tableName);
        $schemaInfo = $tableObjectGeneration->generationTable();
        $this->schemaInfo = $schemaInfo;
    }

    /**
     * 获取模型生成器
     * @param $path 生成的模型所在目录
     * @param $tablePre
     * @param $extendClass
     * @return ModelGeneration
     * @throws \Exception
     */
    function getModelGeneration($path, $tablePre = '', $extendClass = \EasySwoole\ORM\AbstractModel::class): ModelGeneration
    {
        $modelConfig = new ModelConfig($this->schemaInfo, $tablePre, "{$this->modelBaseNameSpace}{$path}", $extendClass);
        $modelConfig->setRootPath($this->getRootPath());
        $modelGeneration = new ModelGeneration($modelConfig);
        $this->modelGeneration = $modelGeneration;
        return $modelGeneration;
    }

    /**
     * 获取控制器生成器
     * @param ModelGeneration $modelGeneration
     * @param $path
     * @param $tablePre
     * @param $extendClass
     * @return ControllerGeneration
     * @throws \Exception
     */
    function getControllerGeneration(ModelGeneration $modelGeneration, $path, $tablePre = '', $extendClass = AnnotationController::class): ControllerGeneration
    {
        $controllerConfig = new ControllerConfig($modelGeneration->getConfig()->getNamespace() . '\\' . $modelGeneration->getClassName(), $this->schemaInfo, $tablePre, "{$this->controllerBaseNameSpace}{$path}", $extendClass);
        $controllerConfig->setRootPath($this->getRootPath());
        $controllerGeneration = new ControllerGeneration($controllerConfig);
        $this->controllerGeneration = $controllerGeneration;
        return $controllerGeneration;
    }

    /**
     * 获取单元测试生成器
     * @param ModelGeneration $modelGeneration
     * @param ControllerGeneration $controllerGeneration
     * @param $path
     * @param $tablePre
     * @param $extendClass
     * @return UnitTestGeneration
     * @throws \Exception
     */
    function getUnitTestGeneration(ModelGeneration $modelGeneration, ControllerGeneration $controllerGeneration, $path, $tablePre = '', $extendClass = TestCase::class): UnitTestGeneration
    {
        $controllerConfig = new UnitTestConfig($modelGeneration->getConfig()->getNamespace() . '\\' . $modelGeneration->getClassName(), $controllerGeneration->getConfig()->getNamespace() . '\\' . $controllerGeneration->getClassName(), $this->schemaInfo, $tablePre, "{$this->unitTestBaseNameSpace}{$path}", $extendClass);
        $controllerConfig->setRootPath($this->getRootPath());
        $unitTestGeneration = new UnitTestGeneration($controllerConfig);
        return $unitTestGeneration;
    }

    /**
     * 模型生成
     * @param $path 生成的模型所在目录
     * @param $tablePre
     * @param $extendClass
     * @return bool|int|string
     */
    function generationModel($path, $tablePre = '', $extendClass = \EasySwoole\ORM\AbstractModel::class)
    {
        $modelGeneration = $this->getModelGeneration($path, $tablePre, $extendClass);
        $result = $modelGeneration->generate();
        return $result;
    }

    /**
     * 控制器生成
     * @param $path
     * @param ModelGeneration|null $modelGeneration
     * @param $tablePre
     * @param $extendClass
     * @return bool|int|string
     * @throws \Exception
     */
    function generationController($path, ?ModelGeneration $modelGeneration = null, $tablePre = '', $extendClass = AnnotationController::class)
    {
        $modelGeneration = $modelGeneration ?? $this->modelGeneration;
        $controllerGeneration = $this->getControllerGeneration($modelGeneration, $path, $tablePre, $extendClass);
        $result = $controllerGeneration->generate();
        return $result;
    }

    /**
     * 单元测试生成
     * @param $path
     * @param ModelGeneration|null $modelGeneration
     * @param ControllerGeneration|null $controllerGeneration
     * @param $tablePre
     * @param $extendClass
     * @return bool|int|string
     * @throws \Exception
     */
    function generationUnitTest($path, ?ModelGeneration $modelGeneration = null, ?ControllerGeneration $controllerGeneration = null, $tablePre = '', $extendClass = TestCase::class)
    {
        $modelGeneration = $modelGeneration ?? $this->modelGeneration;
        $controllerGeneration = $controllerGeneration ?? $this->controllerGeneration;
        $controllerGeneration = $this->getUnitTestGeneration($modelGeneration, $controllerGeneration, $path, $tablePre, $extendClass);
        $result = $controllerGeneration->generate();
        return $result;
    }

    /**
     * @return string
     */
    public function getModelBaseNameSpace(): string
    {
        return $this->modelBaseNameSpace;
    }

    /**
     * @param string $modelBaseNameSpace
     */
    public function setModelBaseNameSpace(string $modelBaseNameSpace): void
    {
        $this->modelBaseNameSpace = $modelBaseNameSpace;
    }

    /**
     * @return string
     */
    public function getControllerBaseNameSpace(): string
    {
        return $this->controllerBaseNameSpace;
    }

    /**
     * @param string $controllerBaseNameSpace
     */
    public function setControllerBaseNameSpace(string $controllerBaseNameSpace): void
    {
        $this->controllerBaseNameSpace = $controllerBaseNameSpace;
    }

    /**
     * @return string
     */
    public function getUnitTestBaseNameSpace(): string
    {
        return $this->unitTestBaseNameSpace;
    }

    /**
     * @param string $unitTestBaseNameSpace
     */
    public function setUnitTestBaseNameSpace(string $unitTestBaseNameSpace): void
    {
        $this->unitTestBaseNameSpace = $unitTestBaseNameSpace;
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        if (empty($this->rootPath)) {
            $this->rootPath = getcwd();
        }
        return $this->rootPath;
    }

    /**
     * @param string $rootPath
     */
    public function setRootPath(string $rootPath): void
    {
        $this->rootPath = $rootPath;
    }
}
