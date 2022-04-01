<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;

use EasySwoole\Annotation\AbstractAnnotationTag;
use EasySwoole\HttpAnnotation\Exception\Annotation\InvalidTag;

/**
 * Class Inject，可注入类并且传入构造函数参数
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class Inject extends AbstractAnnotationTag
{
    public $className; // 类全路径，必填且是字符串且该类存在

    public $args = []; // 传入构造器参数

    public function tagName(): string
    {
        return 'Inject';
    }

    public function __onParser()
    {
        if (empty($this->className)) {
            throw new InvalidTag("Inject for @Inject is require");
        }

        if (!is_string($this->className)) {
            throw new InvalidTag('className for @Inject must to be string');
        }

        if (!class_exists($this->className)) {
            throw new InvalidTag('the class specified by @Inject does not exist');
        }
    }
}