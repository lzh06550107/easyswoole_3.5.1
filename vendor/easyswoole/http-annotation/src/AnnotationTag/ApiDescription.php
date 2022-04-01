<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;

use EasySwoole\Annotation\AbstractAnnotationTag;

/**
 * Class ApiDescription，api描述
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class ApiDescription extends AbstractAnnotationTag
{
    /**
     * @var string 描述类型
     */
    public $type = 'text';//text|file

    public function tagName(): string
    {
        return 'ApiDescription';
    }
}