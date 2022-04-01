<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;


/**
 * Class ApiRequestExample api接口演示路径
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class ApiRequestExample extends ApiDescription
{
    public function tagName(): string
    {
        return 'ApiRequestExample';
    }
}