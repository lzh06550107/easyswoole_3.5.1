<?php


namespace EasySwoole\Bridge;

use Swoole\Coroutine\Socket;

/**
 * 命令接口，桥接进程中要执行命令接口
 */
interface CommandInterface
{
    /**
     * 命令名称
     * @return string
     */
    public function commandName():string;

    /**
     * 命令执行主体方法
     * @param Package $package 命令请求包
     * @param Package $responsePackage 命令响应包
     * @param Socket $socket 客户端连接
     * @return mixed
     */
    public function exec(Package $package,Package $responsePackage,Socket $socket);
}
