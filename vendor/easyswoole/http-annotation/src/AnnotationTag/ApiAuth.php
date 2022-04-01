<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;

/**
 * Class ApiAuth，方法范围参数，本质与 @Param 标签并无区别，仅仅是在自动生成文档的时候， @Param 被描述为请求参数，而 @ApiAuth 则会被描述为权限参数
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class ApiAuth extends Param
{
    public function tagName(): string
    {
        return 'ApiAuth';
    }
}