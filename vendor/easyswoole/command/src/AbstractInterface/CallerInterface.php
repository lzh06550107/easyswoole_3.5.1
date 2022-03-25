<?php


namespace EasySwoole\Command\AbstractInterface;

/**
 * 对输入的命令封装类接口
 */
interface CallerInterface
{
    /**
     * 命令名称
     * @return string
     */
    public function getCommand(): string;

    public function setCommand(string $command);

    /**
     * 除了php外的所有参数
     * @param $params
     * @return mixed
     */
    public function setParams($params);

    public function getParams();

    /**
     * 命令执行入口脚本名称
     * @param string $script
     * @return mixed
     */
    public function setScript(string $script);

    public function getScript(): string;
}