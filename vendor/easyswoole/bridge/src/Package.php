<?php


namespace EasySwoole\Bridge;

/**
 * 和桥接服务交互的命令协议包
 */
class Package
{
    protected $status = self::STATUS_SUCCESS; // 命令执行状态
    protected $command; // 命令名称，协议中只传命令名称
    protected $args; // 命令执行参数
    protected $msg;

    const STATUS_UNIX_CONNECT_ERROR = -1; // unix socket 连接错误
    const STATUS_PACKAGE_ERROR = -2; // 包格式错误
    const STATUS_COMMAND_NOT_EXIST = -3; // 命令不存在
    const STATUS_COMMAND_ERROR = -4; // 命令执行错误

    const STATUS_SUCCESS = 1;

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command): void
    {
        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param mixed $args
     */
    public function setArgs($args): void
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed $msg
     */
    public function setMsg($msg): void
    {
        $this->msg = $msg;
    }
}