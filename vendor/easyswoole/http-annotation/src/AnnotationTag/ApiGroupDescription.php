<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;

/**
 * Class ApiGroupDescription，api分组描述
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class ApiGroupDescription extends ApiDescription
{
    public function tagName(): string
    {
       return 'ApiGroupDescription';
    }
}