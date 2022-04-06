<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 10:19
 */

namespace EasySwoole\CodeGeneration\ClassGeneration;

use EasySwoole\Utility\File;
use Nette\PhpGenerator\PhpNamespace;

/**
 * 类生成器
 */
class ClassGeneration
{
    /**
     * @var $config Config;
     */
    protected $config; // 配置
    protected $phpClass;
    protected $phpNamespace;
    protected $methodGenerationList = [];

    /**
     * BeanBuilder constructor.
     * @param        $config
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        File::createDirectory($config->getDirectory()); // 创建目录
        $phpNamespace = new PhpNamespace($this->config->getNamespace()); // 获取配置的命名空间
        $this->phpNamespace = $phpNamespace;
        $className = $this->getClassName(); // 获取配置的类名称
        $phpClass = $phpNamespace->addClass($className);
        $this->phpClass = $phpClass;
        $this->addExtend();
    }

    protected function addExtend()
    {
        $extendClass = $this->config->getExtendClass();
        if (!empty($extendClass)) {
            $this->phpNamespace->addUse($this->config->getExtendClass());
            $this->phpClass->addExtend($this->config->getExtendClass());
        }
    }

    /**
     * 
     * @return bool|int|string
     */
    final function generate()
    {
        $this->addComment(); // 给生成的类添加注释
        $this->addClassData(); // 生成类数据
        /**
         * @var $method MethodAbstract
         */
        foreach ($this->methodGenerationList as $method) {
            $method->run(); // 生成类方法
        }
        return $this->createPHPDocument(); // 生成文档注释
    }

    function addClassData()
    {

    }

    /**
     * 添加类注释
     */
    protected function addComment()
    {
        $this->phpClass->addComment("{$this->getClassName()}");
        $this->phpClass->addComment("Class {$this->getClassName()}");
        $this->phpClass->addComment('Create With ClassGeneration');
    }

    /**
     * 子类可以覆盖，提供类注释
     * @return mixed
     */
    protected function getClassName()
    {
        return $this->config->getClassName();
    }

    /**
     * 生成文档注释
     * @return bool|int
     * @author Tioncico
     * Time: 19:49
     */
    protected function createPHPDocument()
    {
        $fileName = $this->config->getDirectory() . '/' . $this->getClassName();
        $content = "<?php\n\n{$this->phpNamespace}\n";
        $result = File::createFile($fileName . '.php', $content);
        return $result == false ? $result : $fileName . '.php';
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return \Nette\PhpGenerator\ClassType
     */
    public function getPhpClass(): \Nette\PhpGenerator\ClassType
    {
        return $this->phpClass;
    }

    /**
     * @return PhpNamespace
     */
    public function getPhpNamespace(): PhpNamespace
    {
        return $this->phpNamespace;
    }

    public function addGenerationMethod(MethodAbstract $abstract)
    {
        $this->methodGenerationList[$abstract->getMethodName()] = $abstract;
    }


}
