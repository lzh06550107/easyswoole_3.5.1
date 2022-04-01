<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;


use EasySwoole\Annotation\AbstractAnnotationTag;
/**
 * Class Method，允许请求方式注解
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class Method extends AbstractAnnotationTag
{
    /**
     * @var array，允许的请求方式
     */
    public $allow = [];

    public function tagName(): string
    {
        return 'Method';
    }
}