<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 10:38
 */

namespace EasySwoole\CodeGeneration\ClassGeneration;

/**
 * 方法生成的抽象类
 */
abstract class MethodAbstract
{
    /**
     * @var ClassGeneration
     */
    protected $classGeneration;

    /**
     * @var \Nette\PhpGenerator\Method
     */
    protected $method;

    function __construct(ClassGeneration $classGeneration)
    {
        $this->classGeneration = $classGeneration;
        $method = $classGeneration->getPhpClass()->addMethod($this->getMethodName());
        $this->method = $method;
    }

    function run()
    {
        $this->addComment(); // 添加方法注释
        $this->addMethodBody(); // 添加方法主体
    }

    /**
     * 获取方法注释
     */
    function addComment()
    {
        return;
    }

    /**
     * 获取方法主体
     * @return mixed
     */
    abstract function addMethodBody();

    /**
     * 获取方法名称
     * @return string
     */
    abstract function getMethodName(): string;
}
