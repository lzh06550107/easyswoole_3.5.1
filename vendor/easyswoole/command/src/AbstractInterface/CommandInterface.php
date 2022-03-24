<?php


namespace EasySwoole\Command\AbstractInterface;

/**
 * 命令接口
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