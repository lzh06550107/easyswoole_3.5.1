<?php


namespace EasySwoole\Command\AbstractInterface;

/**
 * 命令接口，用户自定义命令实现该接口
 *
 * 注意：实现该接口的所有命令都是在临时进程中执行，该进程没有开启协程，但 server start 命令除外
 *
 */
interface CommandInterface
{
    /**
     * 命令名称
     * @return string
     */
    public function commandName(): string;

    /**
     * 命令执行的主体
     * @return string|null
     */
    public function exec(): ?string;

    /**
     * 命令帮助示例
     * @param CommandHelpInterface $commandHelp
     * @return CommandHelpInterface
     */
    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface;

    /**
     * 命令描述
     * @return string
     */
    public function desc(): string;
}