<?php

namespace EasySwoole\Session;

/**
 * 会话管理器
 */
class Session
{
    protected $context = []; // 保存所有会话的上下文对象
    protected $handler; // 会话处理器
    protected $timeout; // 超时时间

    function __construct(SessionHandlerInterface $handler,float $timeout = 3.0)
    {
        $this->handler = $handler;
        $this->timeout = $timeout;
    }

    /**
     * 创建指定会话的上下文
     * @param string $sessionId
     * @param float|null $timeout
     * @return Context|null
     * @throws Exception
     * @throws \Throwable
     */
    function create(string $sessionId,float $timeout = null):?Context
    {
        if($timeout === null){
            $timeout = $this->timeout;
        }
        if(!isset($this->context[$sessionId])){ // 如果不存在，则创建
            try{
                if($this->handler->open($sessionId,$timeout)){ // 从处理器获取上下文信息
                    $this->context[$sessionId] = new Context($this->handler->read($sessionId,$timeout));
                }else{
                    throw new Exception("fail to open sessionId {$sessionId}");
                }
            }catch (\Throwable $exception){
                unset($this->context[$sessionId]);
                $this->close($sessionId,$timeout);
                throw $exception;
            }
        }
        return $this->context[$sessionId];
    }

    /**
     * 保存指定会话
     * @param string $sessionId
     * @param float|null $timeout
     * @return bool|null
     * @throws \Throwable
     */
    function close(string $sessionId,float $timeout = null):?bool
    {
        if(isset($this->context[$sessionId])){
            if($timeout === null){
                $timeout = $this->timeout;
            }
            try{
                /** @var Context $context */
                $context = $this->context[$sessionId]; // 会话上下文写入到处理器中
                $this->handler->write($sessionId,$context->allContext(),$timeout);
            }catch (\Throwable $exception){
                throw $exception;
            } finally {
                unset($this->context[$sessionId]);
                return $this->handler->close($sessionId,$timeout);
            }
        }
        return null;
    }

    function gc(int $expire,float $timeout = null):bool
    {
        if($timeout === null){
            $timeout = $this->timeout;
        }
        return $this->handler->gc($expire,$timeout);
    }
}
