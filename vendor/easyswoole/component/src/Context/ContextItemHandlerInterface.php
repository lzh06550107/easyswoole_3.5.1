<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-06
 * Time: 23:02
 */

namespace EasySwoole\Component\Context;

/**
 * 协程自定义处理项
 * 希望在协程环境中，get的时候执行一次创建，在协程退出的时候可以进行回收，就可以注册一个上下文处理项来实现
 */
interface ContextItemHandlerInterface
{
    // 初始创建
    function onContextCreate();
    // 销毁
    function onDestroy($context);
}