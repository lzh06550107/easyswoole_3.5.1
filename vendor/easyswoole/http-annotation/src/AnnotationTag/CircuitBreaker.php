<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;


use EasySwoole\Annotation\AbstractAnnotationTag;

/**
 * Class CircuitBreaker，熔断注解
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class CircuitBreaker extends AbstractAnnotationTag
{
    /**
     * @var float 超时时间
     */
    public $timeout = 3.0;
    /**
     * @var string 失败执行的动作方法
     */
    public $failAction;

    public function tagName(): string
    {
        return 'CircuitBreaker';
    }
}