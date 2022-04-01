<?php

namespace EasySwoole\HttpAnnotation\AnnotationTag;

use EasySwoole\Annotation\AbstractAnnotationTag;

/**
 * Class Controller，控制器注解
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class Controller extends AbstractAnnotationTag
{

    /** 
     * @var string 控制器设置路径前缀
     */
    public $prefix;

    public function tagName(): string
    {
        return 'Controller';
    }
}
