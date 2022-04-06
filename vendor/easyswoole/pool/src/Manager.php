<?php


namespace EasySwoole\Pool;

use EasySwoole\Component\Singleton;

/**
 * 连接池管理器
 */
class Manager
{
    use Singleton;

    protected $container = []; // 池名称和池对象映射关系

    function register(AbstractPool $pool,string $name = null):Manager
    {
        if($name === null){
            $name = get_class($pool);
        }
        $this->container[$name] = $pool;
        return $this;
    }

    function get(string $name):?AbstractPool
    {
        if(isset($this->container[$name])){
            return $this->container[$name];
        }
        return null;
    }

    /**
     * 销毁所有连接池
     */
    function resetAll()
    {
        /** @var AbstractPool $item */
        foreach ($this->container as $item){
            $item->destroy();
        }
    }
}