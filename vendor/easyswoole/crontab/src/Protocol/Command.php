<?php


namespace EasySwoole\Crontab\Protocol;


use EasySwoole\Spl\SplBean;

/**
 * 定时任务的命令类型类
 */
class Command extends SplBean
{
    const COMMAND_EXEC_JOB = 0x1; // 立即执行定时任务命令类型

    protected $command;
    protected $arg;

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
    public function getArg()
    {
        return $this->arg;
    }

    /**
     * @param mixed $arg
     */
    public function setArg($arg): void
    {
        $this->arg = $arg;
    }
}