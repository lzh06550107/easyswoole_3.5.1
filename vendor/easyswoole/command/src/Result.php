<?php


namespace EasySwoole\Command;


use EasySwoole\Command\AbstractInterface\ResultInterface;

/**
 * 命令执行结果类
 */
class Result implements ResultInterface
{
    private $result;
    private $msg;

    function getMsg(): ?string
    {
       return $this->msg;
    }

    function setResult($result)
    {
        $this->result = $result;
    }

    function getResult()
    {
        return $this->result;
    }

    function setMsg(?string $msg)
    {
        $this->msg = $msg;
    }
}