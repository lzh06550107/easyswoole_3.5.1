<?php

namespace EasySwoole\HttpAnnotation\Annotation;

use EasySwoole\HttpAnnotation\AnnotationTag\Context;
use EasySwoole\HttpAnnotation\AnnotationTag\Di;
use EasySwoole\HttpAnnotation\AnnotationTag\Inject;

/**
 * 属性注解
 */
class PropertyAnnotation extends AnnotationBean
{
    protected $name;

    /** @var Di|null */
    protected $di; // 用于在每次请求进来的时候，从IOC中取数据，并赋值到对应的属性中

    /** @var Context|null */
    protected $context; // 用于在每次请求进来的时候，从上下文管理器中取数据，并赋值到对应的属性中

    /**
     * @var Inject|null
     */
    protected $inject; // 可注入类并且传入构造函数参数

    function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Di|null
     */
    public function getDiTag(): ?Di
    {
        return $this->di;
    }

    public function getInjectTag():?Inject
    {
        return $this->inject;
    }

    /**
     * @return Context|null
     */
    public function getContextTag(): ?Context
    {
        return $this->context;
    }

}