<?php

namespace EasySwoole\Annotation;

use EasySwoole\DoctrineAnnotation\AnnotationReader;

/**
 * 注解管理器，保存所有注解标签和标签解析器对象
 */
class Annotation
{
    protected $parserTagList = []; // 注解标签解析器实例对象数组，是标签解析器名称和解析器实例对象映射
    protected $aliasMap = []; // 注解标签别名映射
    protected $strictMode = false; // 若为严格模式，如果解析不到正确的数值，则报错

    function __construct(array $parserTagList = [])
    {
        $this->parserTagList = $parserTagList;
    }

    /**
     * 添加注解标签别名
     * @param string $alias
     * @param string $realTagName
     * @return $this
     */
    function addAlias(string $alias,string $realTagName)
    {
        $this->aliasMap[$realTagName] = $alias;
        return $this;
    }

    /**
     * 设置严格模式，若为严格模式，如果解析不到正确的数值，则报错
     * @param bool $is
     * @return $this
     */
    public function strictMode(bool $is)
    {
        $this->strictMode = $is;
        return $this;
    }

    /**
     * 添加标签解析器
     * @param AbstractAnnotationTag $annotationTag
     * @return $this
     * @throws Exception
     */
    function addParserTag(AbstractAnnotationTag $annotationTag):Annotation
    {
        $name = $annotationTag->tagName();
        if(isset($this->aliasMap[$name])){
            throw new Exception("tag alias name {$name} and tag name is duplicate");
        }
        $this->parserTagList[$name] = $annotationTag;
        return $this;
    }

    /**
     * 删除标签解析器
     * @param string $tagName
     * @return $this
     */
    function deleteParserTag(string $tagName):Annotation
    {
        unset($this->parserTagList[$tagName]);
        return $this;
    }

    /**
     * 通过反射类获取注解
     * @param \Reflector $ref
     * @return array
     */
    function getAnnotation(\Reflector $ref):array
    {
        $ret = [];
        $reader = new AnnotationReader();
        if($ref instanceof \ReflectionMethod){ // 如果是方法反射，则读取方法注解
            $temp = $reader->getMethodAnnotations($ref);
        }else if($ref instanceof \ReflectionProperty){ // 如果是属性反射，则读取属性注解
            $temp = $reader->getPropertyAnnotations($ref);
        }else if($ref instanceof \ReflectionClass){ // 如果是类反射，则读取类注解
            $temp = $reader->getClassAnnotations($ref);
        }
        if(!empty($temp)) {
            foreach ($temp as $item){ // 迭代获取的所有解析的注解标签
                if($item instanceof AbstractAnnotationTag){
                    $name = $item->tagName();
                    $item->__onParser(); // 调用该注解标签解析器
                    if(isset($this->parserTagList[$name])){
                        $ret[$name][] = $item; // 注解标签名称和注解标签项映射，可能存在多个
                        if(isset($this->aliasMap[$name])){ // 如果真实的注解标签存在别名，则
                            $alias = clone $item;
                            $alias->aliasFrom($name); // 记录来自那个别名
                            $alias->__onParser(); // 调用别名注解标签解析器
                            $name = $this->aliasMap[$name]; // 获取别名
                            $ret[$name][] = $alias; // 注解标签别名和注解标签项映射
                        }
                    }
                }
            }
        }
        return $ret;
    }
}