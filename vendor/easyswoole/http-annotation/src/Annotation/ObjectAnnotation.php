<?php

namespace EasySwoole\HttpAnnotation\Annotation;

use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Controller;

/**
 * 收集类这一层级注解
 */
class ObjectAnnotation extends AnnotationBean
{
    /** @var ApiGroup|null */
    protected $apiGroup; // 收集 ApiGroup 注解标签

    /** @var ApiGroupDescription|null */
    protected $apiGroupDescription; // 收集 ApiGroupDescription 注解标签

    /** @var Controller|null */
    protected $controller; // 收集 Controller 注解标签

    protected $apiGroupAuth = []; // 收集 ApiGroupAuth 注解标签，可以存在多个

    protected $param = []; // 收集 Param 注解标签，可以存在多个

    protected $__methods = []; // 收集该类的所有方法注解，即 MethodAnnotation

    protected $__properties = []; // 收集该类的所有属性注解，即 PropertyAnnotation

    
    function getGroupAuthTag(?string $paramName = null)
    {
        if($paramName){
            if(isset($this->apiGroupAuth[$paramName])){
                return $this->apiGroupAuth[$paramName];
            }
            return null;
        }else{
            return $this->apiGroupAuth;
        }
    }

    public function getParamTag(?string $paramName = null)
    {
        if($paramName){
            if(isset($this->param[$paramName])){
                return $this->param[$paramName];
            }
            return null;
        }else{
            return $this->param;
        }
    }

    function addProperty(PropertyAnnotation $annotation)
    {
        $this->__properties[$annotation->getName()] = $annotation;
        return $this;
    }

    function getProperty(?string $name = null)
    {
        if($name){
            if(isset($this->__properties[$name])){
                return $this->__properties[$name];
            }
            return null;
        }else{
            return $this->__properties;
        }
    }

    function getMethod(?string $name = null)
    {
        if($name){
            if(isset($this->__methods[$name])){
                return $this->__methods[$name];
            }
            return null;
        }else{
            return $this->__methods;
        }
    }

    function addMethod(MethodAnnotation $method)
    {
        $this->__methods[$method->getMethodName()] = $method;
        return $this;
    }

    /**
     * @return ApiGroup|null
     */
    public function getApiGroupTag(): ?ApiGroup
    {
        return $this->apiGroup;
    }

    /**
     * @return ApiGroupDescription|null
     */
    public function getApiGroupDescriptionTag(): ?ApiGroupDescription
    {
        return $this->apiGroupDescription;
    }

    /**
     * @return Controller|null
     */
    public function getController(): ?Controller
    {
        return $this->controller;
    }
}

