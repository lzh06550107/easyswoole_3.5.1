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
        //MSG_IPC_NOWAIT 这个参数表示如果没有从消息队列中读取到信息，会立马返回，并返回错误码 MSG_ENOMSG
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
        //你发送的消息很大，而此时的消息队列无法存入的时候，此时消息队列就会阻塞，除非等到有别的进程从消息队列中读取了别的消息，然后消息队列有足够的空间存储你要发送的信息，才能继续执行。
        return msg_send($this->queue,1,\Opis\Closure\serialize($package),false);
    }
}