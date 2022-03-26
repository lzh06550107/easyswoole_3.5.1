<?php


namespace EasySwoole\Task;

use EasySwoole\Task\AbstractInterface\TaskQueueInterface;

/**
 * 基于unix系统内核消息队列实现
 */
class MessageQueue implements TaskQueueInterface
{
    private $queue; // 内核消息队列
    private $key; // 消息队列key

    function __construct(string $key = null)
    {
        if($key === null){
            $key = ftok(__FILE__, 'a'); //生成一个消息队列的key
        }
        $this->key = $key;
        $this->queue = msg_get_queue($key, 0666); //产生一个消息队列
    }

    function getQueueKey()
    {
        return $this->key;
    }

    /*
     * 清空一个queue,并不是删除
     */
    function clearQueue()
    {

        msg_remove_queue($this->queue); //移除消息队列
        $this->queue = msg_get_queue($this->key , 0666); //产生一个消息队列
    }

    function pop(): ?Package
    {
        //从消息队列中读取一条消息
        msg_receive($this->queue, 1, $message_type, 1024, $package,false,MSG_IPC_NOWAIT);
        $package = \Opis\Closure\unserialize($package);
        if($package instanceof Package){
            return $package;
        }
        return null;
    }

    function push(Package $package): bool
    {
        //将一条消息加入消息队列
        return msg_send($this->queue,1,\Opis\Closure\serialize($package),false);
    }
}