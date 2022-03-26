<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Bridge\CommandInterface;
use EasySwoole\Bridge\Package;
use Swoole\Coroutine\Socket;

/**
 * 桥接进程中的抽象命令类
 */
abstract class AbstractCommand implements CommandInterface
{
    public function exec(Package $package, Package $responsePackage, Socket $socket)
    {
        $action = $package->getArgs()['action'] ?? '';
        if (!method_exists($this, $action)) {
            $responsePackage->setStatus($responsePackage::STATUS_COMMAND_NOT_EXIST);
            $responsePackage->setMsg("command action:{$action} not empty");
            return $responsePackage;
        }
        $this->{$action}($package, $responsePackage);
    }
}