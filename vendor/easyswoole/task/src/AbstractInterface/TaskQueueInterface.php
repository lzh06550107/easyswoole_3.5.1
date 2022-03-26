<?php


namespace EasySwoole\Task\AbstractInterface;

use EasySwoole\Task\Package;

/**
 * 任务队列接口
 */
interface TaskQueueInterface
{
    /**
     * 从队列尾部获取一个包
     * @return Package|null
     */
    function pop():?Package;

    /**
     * 推送一个包到队列中
     * @param Package $package
     * @return bool
     */
    function push(Package $package):bool ;
}