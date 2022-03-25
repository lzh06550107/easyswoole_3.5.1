<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\Command\AbstractInterface;

/**
 * 命令帮助类接口
 */
interface CommandHelpInterface
{
    public function addAction(string $actionName, string $desc);

    public function addActionOpt(string $actionOptName, string $desc);
}