<?php

namespace EasySwoole\HttpAnnotation\Annotation;

/**
 * 注释解析器接口
 */
interface ParserInterface
{
    /**
     * 通过反射来解析注释类
     * @param \ReflectionClass $reflectionClass
     * @return ObjectAnnotation
     */
    function parseObject(\ReflectionClass  $reflectionClass):ObjectAnnotation;
}