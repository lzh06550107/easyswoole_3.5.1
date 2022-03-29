<?php

namespace EasySwoole\Session;

/**
 * 会话处理器接口
 */
interface SessionHandlerInterface
{
    /**
     * 打开
     * @param string $sessionId
     * @param float|null $timeout
     * @return bool
     */
    function open(string $sessionId,?float $timeout = null):bool;

    /**
     * 读取
     * @param string $sessionId
     * @param float|null $timeout
     * @return array|null
     */
    function read(string $sessionId,?float $timeout = null):?array;

    /**
     * 写入
     * @param string $sessionId
     * @param array $data
     * @param float|null $timeout
     * @return bool
     */
    function write(string $sessionId,array $data,?float $timeout = null):bool;

    /**
     * 关闭
     * @param string $sessionId
     * @param float|null $timeout
     * @return bool
     */
    function close(string $sessionId,?float $timeout = null):bool;

    /**
     * 回收
     * @param int $expire
     * @param float|null $timeout
     * @return bool
     */
    function gc(int $expire,?float $timeout = null):bool;
}