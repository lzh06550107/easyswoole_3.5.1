<?php


namespace EasySwoole\Bridge;


class Container
{
    private $container = [];

    /**
     * 注册命令
     * @param CommandInterface $command 命令对象
     * @param $cover 是否覆盖
     */
    public function set(CommandInterface $command,$cover = false)
    {
        if(!isset($this->container[strtolower($command->commandName())]) || $cover){
            $this->container[strtolower($command->commandName())] = $command;
        }
    }

    /**
     * 获取指定名称的命令对象
     * @param $key
     * @return CommandInterface|null
     */
    function get($key): ?CommandInterface
    {
        $key = strtolower($key);
        if (isset($this->container[$key])) {
            return $this->container[$key];
        } else {
            return null;
        }
    }

    function all():array
    {
        return $this->container;
    }
}