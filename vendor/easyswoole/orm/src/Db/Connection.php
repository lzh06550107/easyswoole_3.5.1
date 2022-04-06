<?php


namespace EasySwoole\ORM\Db;

use EasySwoole\Pool\AbstractPool;

/**
 * 数据库连接管理类
 */
class Connection implements ConnectionInterface
{
    /** @var Config */
    protected $config;
    
    /** @var AbstractPool */
    protected $pool;

    function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * 延迟回收获取的客户端连接
     * @param float|null $timeout
     * @return ClientInterface|null
     * @throws \EasySwoole\Pool\Exception\PoolEmpty
     * @throws \Throwable
     */
    function defer(float $timeout = null): ?ClientInterface
    {
        if($timeout === null){
            $timeout = $this->config->getGetObjectTimeout();
        }
        return $this->getPool()->defer($timeout);
    }

    /**
     * 获取连接池
     * @return AbstractPool
     */
    function __getClientPool(): AbstractPool
    {
        return $this->getPool();
    }


    protected function getPool():MysqlPool
    {
        if(!$this->pool){
            $this->pool = new MysqlPool($this->config);
        }
        return $this->pool;
    }

    /**
     * @return Config|null
     */
    public function getConfig():?Config
    {
        return $this->config;
    }
}