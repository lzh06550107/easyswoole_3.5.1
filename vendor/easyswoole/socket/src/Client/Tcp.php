<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/25
 * Time: 下午3:22
 */

namespace EasySwoole\Socket\Client;

/**
 * 封装Tcp客户端连接
 */
class Tcp
{
    protected $reactorId; // TCP 连接所在的 Reactor 线程 ID
    protected $fd; // 连接的文件描述符

    function __construct($fd = null,$reactorId = null)
    {
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }

    /**
     * @return mixed
     */
    public function getReactorId()
    {
        return $this->reactorId;
    }

    /**
     * @param mixed $reactorId
     */
    public function setReactorId($reactorId)
    {
        $this->reactorId = $reactorId;
    }

    /**
     * @return mixed
     */
    public function getFd()
    {
        return $this->fd;
    }

    /**
     * @param mixed $fd
     */
    public function setFd($fd)
    {
        $this->fd = $fd;
    }
}