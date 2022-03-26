<?php


namespace EasySwoole\Bridge;


use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\Socket\Tools\Protocol;
use Swoole\Coroutine\Socket;

/**
 * 桥接进程
 */
class BridgeProcess extends AbstractUnixProcess
{
    private $container;
    private $onException;

    function run($arg)
    {
        $this->container = $arg['container'];
        $this->onException = $arg['onException'];
        $onStart = $arg['onStart'];
        if($onStart){
            call_user_func($onStart,$this);
        }
        parent::run($arg);
    }

    function onAccept(Socket $socket)
    {
        $data = Protocol::socketReader($socket, 3);
        if ($data === null) {
            $package = new  Package();
            $package->setStatus(Package::STATUS_PACKAGE_ERROR); // 协议包错误
            Protocol::socketWriter($socket, serialize($package));
            $socket->close();
            return null;
        }
        /**
         * @var $package Package
         */
        $package = unserialize($data); // 反系列化包
        /**
         * @var $command CommandInterface
         */
        $command = $this->container->get($package->getCommand()); // 通过命令名称获取命令对象
        if (!$command instanceof CommandInterface) {
            $package = new Package();
            $package->setStatus(Package::STATUS_COMMAND_NOT_EXIST); // 指定的命令不存在
            $package->setMsg("command:{$package->getCommand()} is not exist");
            Protocol::socketWriter($socket, serialize($package));
            $socket->close();
            return null;
        }
        $responsePackage = new Package(); // 初始化一个响应包
        try{
            //结果在闭包中更改
            $responsePackage->setStatus(Package::STATUS_SUCCESS);
            $command->exec($package,$responsePackage,$socket);
        }catch (\Throwable $throwable){
            $responsePackage->setStatus(Package::STATUS_COMMAND_ERROR);
            $responsePackage->setMsg($throwable->getMessage());
            $this->onException($throwable);
        } finally {
            Protocol::socketWriter($socket,serialize($responsePackage));
            $socket->close();
        }
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        if($this->onException){
            call_user_func($this->onException,$throwable);
        }else{
            throw $throwable;
        }
    }
}
