<?php

namespace EasySwoole\HttpAnnotation\Annotation;

use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\CircuitBreaker;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;

/**
 * 方法注释
 */
class MethodAnnotation extends AnnotationBean
{
    protected $__name;

    protected $api; // Api注解标签
    protected $apiAuth = []; // ApiAuth注解标签
    protected $apiDescription; // ApiDescription注解标签
    protected $apiFail = []; // ApiFail注解标签
    protected $apiFailParam = []; // ApiFailParam注解标签
    protected $apiRequestExample = []; // ApiRequestExample注解标签
    protected $apiSuccess = []; // ApiSuccess注解标签
    protected $apiSuccessParam = []; // ApiSuccessParam注解标签
    protected $circuitBreaker; // CircuitBreaker注解标签
    protected $injectParamsContext; // InjectParamsContext注解标签
    protected $method; // Method注解标签
    // 参数覆盖优先顺序 @Param > @ApiAuth > @ApiGroupAuth
    protected $param = []; // 参数注解标签，总共有三个Param、ApiAuth、ApiGroupAuth

    function __construct(string $name)
    {
        $this->__name = $name;
    }

    function getApiTag():?Api
    {
        return $this->api;
    }

    function getApiDescriptionTag():?ApiDescription
    {
        return $this->apiDescription;
    }

    function getCircuitBreakerTag():?CircuitBreaker
    {
        return $this->circuitBreaker;
    }

    function getInjectParamsContextTag():?InjectParamsContext
    {
        return $this->injectParamsContext;
    }

    function getMethodTag():?Method
    {
        return $this->method;
    }

    public function getApiAuth(?string $name = null)
    {
        if($name){
            if(isset($this->apiAuth[$name])){
                return $this->apiAuth[$name];
            }
            return null;
        }
        return $this->apiAuth;
    }

    /**
     * @return array
     */
    public function getApiFail(): array
    {
        return $this->apiFail;
    }
    
    public function getApiFailParam(?string $name = null)
    {
        if($name){
            if(isset($this->apiFailParam[$name])){
                return $this->apiFailParam[$name];
            }
            return null;
        }
        return $this->apiFailParam;
    }

    /**
     * @return array
     */
    public function getApiRequestExample(): array
    {
        return $this->apiRequestExample;
    }

    /**
     * @return array
     */
    public function getApiSuccess(): array
    {
        return $this->apiSuccess;
    }


    public function getApiSuccessParam(?string $name = null)
    {
        if($name){
            if(isset($this->apiSuccessParam[$name])){
                return $this->apiSuccessParam[$name];
            }
            return null;
        }
        return $this->apiSuccessParam;
    }

    public function getParamTag(?string $name = null)
    {
        if($name){
            if(isset($this->param[$name])){
                return $this->param[$name];
            }
            return null;
        }
        return $this->param;
    }

    /**
     * @return mixed
     */
    public function getMethodName()
    {
        return $this->__name;
    }
}