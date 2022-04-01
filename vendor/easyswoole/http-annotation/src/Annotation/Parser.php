<?php

namespace EasySwoole\HttpAnnotation\Annotation;

use EasySwoole\Annotation\Annotation;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFailParam;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccessParam;
use EasySwoole\HttpAnnotation\AnnotationTag\CircuitBreaker;
use EasySwoole\HttpAnnotation\AnnotationTag\Context;
use EasySwoole\HttpAnnotation\AnnotationTag\Controller;
use EasySwoole\HttpAnnotation\AnnotationTag\Di;
use EasySwoole\HttpAnnotation\AnnotationTag\Inject;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * 注释解析器类
 */
class Parser implements ParserInterface
{
    private $annotation;

    /**
     * 在解析器构造函数中初始化注释管理器对象并初始化所有注释标签解析器对象
     * @param Annotation|null $annotation
     * @throws \EasySwoole\Annotation\Exception
     */
    function __construct(?Annotation $annotation = null)
    {
        if ($annotation == null) {
            $annotation = new Annotation();
            static::preDefines([
                "POST" => "POST",
                "GET" => "GET",
                "PUT" => "PUT",
                "DELETE" => "DELETE",
                "PATCH" => "PATCH",
                "HEAD" => "HEAD",
                "OPTIONS" => "OPTIONS",
                'COOKIE' => 'COOKIE',
                'HEADER' => 'HEADER',
                'FILE' => 'FILE',
                'DI' => 'DI',
                'CONTEXT' => 'CONTEXT',
                'RAW' => 'RAW',
                'JSON' => 'JSON',
                'SESSION' => 'SESSION',
                'ROUTER_PARAMS' => 'ROUTER_PARAMS'
            ]);
            $annotation->addParserTag(new Api());
            $annotation->addParserTag(new ApiAuth());
            $annotation->addParserTag(new ApiDescription());
            $annotation->addParserTag(new ApiFail());
            $annotation->addParserTag(new ApiFailParam());
            $annotation->addParserTag(new ApiGroup());
            $annotation->addParserTag(new ApiGroupAuth());
            $annotation->addParserTag(new ApiGroupDescription());
            $annotation->addParserTag(new ApiRequestExample());
            $annotation->addParserTag(new ApiSuccessParam());
            $annotation->addParserTag(new ApiSuccess());
            $annotation->addParserTag(new CircuitBreaker());
            $annotation->addParserTag(new Context());
            $annotation->addParserTag(new Di());
            $annotation->addParserTag(new Inject());
            $annotation->addParserTag(new InjectParamsContext());
            $annotation->addParserTag(new Method());
            $annotation->addParserTag(new Param());
            $annotation->addParserTag(new Controller());
        }
        $this->annotation = $annotation;
    }

    /**
     * @return Annotation|null
     */
    public function getAnnotation(): Annotation
    {
        return $this->annotation;
    }

    /**
     * 解析对象
     * @param \ReflectionClass $reflectionClass
     * @return ObjectAnnotation
     */
    function parseObject(\ReflectionClass $reflectionClass): ObjectAnnotation
    {
        $parent = $reflectionClass->getParentClass();
        if ($parent && $parent->getName() !== AnnotationController::class) {
            if (Cache::getInstance()->get($parent->getName())) {
                $parent = Cache::getInstance()->get($parent->getName());
            } else {
                $parent = $this->parseObject($parent); // 递归解析，直到父类是 AnnotationController::class 类
            }
        }
        
        $cache = Cache::getInstance()->get($reflectionClass->getName()); // 如果缓存中存在，则直接返回已经解析的注释信息
        if ($cache) {
            return $cache;
        }
        
        $objectAnnotation = new ObjectAnnotation(); // 收集类层级的注释

        //获取class的标签注解
        //先获取父类中的类层级标签注解
        if ($parent instanceof ObjectAnnotation) { // 如果父类已经解析过了，就直接从该父类的 ObjectAnnotation 中继承所有已经解析的标签
            if ($parent->getApiGroupTag()) { // 接口组注释标签
                $objectAnnotation->addAnnotationTag($parent->getApiGroupTag());
            }
            if ($parent->getApiGroupDescriptionTag()) {
                $objectAnnotation->addAnnotationTag($parent->getApiGroupDescriptionTag());
            }
            foreach ($parent->getGroupAuthTag() as $tag) {
                $objectAnnotation->addAnnotationTag($tag);
            }
            foreach ($parent->getParamTag() as $tag) {
                $objectAnnotation->addAnnotationTag($tag);
            }
            foreach ($parent->getOtherTags() as $otherTag) {
                $objectAnnotation->addAnnotationTag($otherTag);
            }
        }
        
        //然后获取当前类中的类层级标签注解
        $tagList = $this->annotation->getAnnotation($reflectionClass); // 通过注解管理器来解析获取标签注解
        //把当前类层级的标签注解存入$objectAnnotation
        array_walk_recursive($tagList, function ($item) use ($objectAnnotation) {
            $objectAnnotation->addAnnotationTag($item);
        });

        //获取class的方法注解
        //先获取父类中的方法注解
        if ($parent instanceof ObjectAnnotation) {
            foreach ($parent->getMethod() as $method) {
                $objectAnnotation->addMethod($method);
            }
        }
        
        //然后获取当前类中的方法注解
        foreach ($reflectionClass->getMethods() as $method) {
            $tagList = $this->annotation->getAnnotation($method); // 通过注解管理器来解析获取标签注解
            $method = new MethodAnnotation($method->getName()); // 封装到方法注解中
            array_walk_recursive($tagList, function ($item) use ($method) {
                $method->addAnnotationTag($item);
            });
            $objectAnnotation->addMethod($method); // 把方法注解添加到对象注解中
        }

        //获取class的成员属性注解
        //先获取父类中属性注解
        if ($parent instanceof ObjectAnnotation) {
            foreach ($parent->getProperty() as $property) {
                $objectAnnotation->addProperty($property);
            }
        }
        
        //然后获取当前类中属性注解
        foreach ($reflectionClass->getProperties() as $property) {
            $tagList = $this->annotation->getAnnotation($property); // 通过注解管理器来解析获取标签注解
            $property = new PropertyAnnotation($property->getName()); // 封装到属性注解中
            array_walk_recursive($tagList, function ($item) use ($property) {
                $property->addAnnotationTag($item);
            });
            $objectAnnotation->addProperty($property); // 把属性注解添加到对象注解中
        }

        Cache::getInstance()->set($reflectionClass->getName(), $objectAnnotation); // 缓存对象注解
        return $objectAnnotation;
    }

    /**
     * 预定义常量
     * @param $defines
     */
    public static function preDefines($defines = [])
    {
        foreach ($defines as $key => $val) {
            if (!defined($key)) {
                define($key, $val);
            }
        }
    }
}
