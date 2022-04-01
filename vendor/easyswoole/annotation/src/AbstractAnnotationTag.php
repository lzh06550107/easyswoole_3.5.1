<?php

namespace EasySwoole\Annotation;

/**
 * 所有标签的抽象类
 */
abstract class AbstractAnnotationTag
{
    //当没有指定key的时候，所有的值都在value字段
    public $value;

    private $aliasFrom;

    abstract public function tagName():string;

    /**
     * 来自那个别名
     * @param string|null $from
     * @return string
     */
    public function aliasFrom(?string $from = null):string
    {
        if($from){
            $this->aliasFrom = $from;
        }
        return $this->aliasFrom;
    }

    public function __onParser(){}
}