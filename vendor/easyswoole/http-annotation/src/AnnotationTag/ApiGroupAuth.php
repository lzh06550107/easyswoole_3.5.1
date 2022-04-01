<?php

namespace EasySwoole\HttpAnnotation\AnnotationTag;

/**
 * Class ApiGroupAuth，控制器全局参数，作用域在整个控制器
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class ApiGroupAuth extends Param
{
    /**
     * @var array
     */
    public $ignoreAction = [];

    public function tagName(): string
    {
        return 'ApiGroupAuth';
    }
}