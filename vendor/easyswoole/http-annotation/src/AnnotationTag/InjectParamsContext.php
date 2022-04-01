<?php


namespace EasySwoole\HttpAnnotation\AnnotationTag;

use EasySwoole\Annotation\AbstractAnnotationTag;
/**
 * Class InjectParamsContext，把通过验证的参数，设置到指定的协成上下文中，并通过上下文管理器 EasySwoole\Component\Context\ContextManager 得到对应的参数
 * @package EasySwoole\HttpAnnotation\AnnotationTag
 * @Annotation
 */
class InjectParamsContext extends AbstractAnnotationTag
{
    public $key = 'INJECT_PARAMS'; // 传入的参数key

    public $onlyParamTag = true; // 忽略 @ApiAuth 与 @ApiGroupAuth 定义的参数

    /**
     * @var bool 是否忽略值为 null 的参数
     */
    public $filterNull = false;

    /**
     * @var bool 是否忽略值被empty()判定为true的参数，注意数字0或者是字符串0与空字符串等问题
     */
    public $filterEmpty = false;

    public function tagName(): string
    {
        return 'InjectParamsContext';
    }
}