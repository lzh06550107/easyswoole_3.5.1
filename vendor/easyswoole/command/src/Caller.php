<?php


namespace EasySwoole\Command;


use EasySwoole\Command\AbstractInterface\CallerInterface;

/**
 * 对输入的命令进行封装，保存输入命令的各个部分
 */
class Caller implements CallerInterface
{
    private $script; // 命令入口脚本名称
    private $command; // 命令名称
    private $params; // 命令参数

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command)
    {
        $this->command = $command;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setScript(string $script)
    {
        $this->script = $script;
    }

    public function getScript(): string
    {
        return $this->script;
    }
}