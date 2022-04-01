<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;


use EasySwoole\Annotation\AbstractAnnotationTag;
use EasySwoole\HttpAnnotation\Exception\Annotation\InvalidTag;

/**
 * Class Api，标记方法为api
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class Api extends AbstractAnnotationTag
{
    /**
     * @var string，路由（可注册到fast-route）
     */
    public $path;
    /**
     * @var string，注解文档的api标题
     */
    public $name; // 必填

    /**
     * @var string，api描述（新版本建议使用@ApiDescription）
     */
    public $description;

    /**
     * @var bool，注解文档标注此api为废弃（将会不可访问,返回423状态）
     */
    public $deprecated;
    /**
     * @var string，接口版本
     */
    public $version = '1.0.0';

    /**
     * @var bool，是否忽略控制器中定义的路径前缀
     */
    public $ignorePrefix = false;

    public function tagName(): string
    {
        return  'Api';
    }

    function __onParser()
    {
        if(empty($this->name)){
            throw new InvalidTag("name for Api tag is require");
        }
    }
}
