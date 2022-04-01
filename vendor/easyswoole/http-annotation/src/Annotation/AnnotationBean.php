<?php

namespace EasySwoole\HttpAnnotation\Annotation;

use EasySwoole\Annotation\AbstractAnnotationTag;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * 注释基类
 */
class AnnotationBean
{
    protected $__otherTags = [];

    public function addAnnotationTag(AbstractAnnotationTag $annotationTag)
    {
        $propertyName = lcfirst($annotationTag->tagName()); // 第一个字母小写
        if (property_exists($this, $propertyName)) {
            if (is_array($this->{$propertyName})) { // 某个标签可能可以同时存在多个
                if ($annotationTag instanceof Param) { // 如果是参数标签，则按参数名称记录
                    $annotationTag->name && $this->{$propertyName}[$annotationTag->name] = $annotationTag;
                } else { // 其它
                    $this->{$propertyName}[] = $annotationTag;
                }
            } else {
                $this->{$propertyName} = $annotationTag;
            }
        } else { // 不属于该对象属性的标签
            $this->__otherTags[$annotationTag->tagName()][] = $annotationTag;
        }
    }

    function getOtherTags(): array
    {
        return $this->__otherTags;
    }
}
