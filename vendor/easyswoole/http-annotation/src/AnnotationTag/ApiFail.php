<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;


use EasySwoole\Annotation\AbstractAnnotationTag;

/**
 * Class ApiFail，api接口失败返回结构样例
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class ApiFail extends ApiDescription
{
    public function tagName(): string
    {
        return 'ApiFail';
    }
}