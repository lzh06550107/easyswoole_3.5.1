<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;


use EasySwoole\Annotation\AbstractAnnotationTag;

/**
 * Class Di，用于在每次请求进来的时候，从Di实例中取数据，并赋值到对应的属性中
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class Di extends AbstractAnnotationTag
{

    /**
     * @var string，键名称
     */
    public $key;

    public function tagName(): string
    {
        return 'Di';
    }

}