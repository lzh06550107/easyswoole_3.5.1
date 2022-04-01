<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;

/**
 * Class ApiSuccess，api接口成功返回结构样例
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class ApiSuccess extends ApiDescription
{
    public function tagName(): string
    {
        return 'ApiSuccess';
    }
}