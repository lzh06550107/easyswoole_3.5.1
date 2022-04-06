<?php


namespace EasySwoole\ORM\Db;

use EasySwoole\Pool\AbstractPool;

/**
 * 数据库连接接口
 */
interface ConnectionInterface
{
    /**
     * 延迟回收获取的客户端连接
     * @param float|null $timeout 获取客户端连接超时时间
     * @return ClientInterface|null
     */
    function defer(float $timeout = null):?ClientInterface;

    /**
     * 获取连接池
     * @return AbstractPool
     */
    function __getClientPool():AbstractPool;

    /**
     * 获取数据库配置
     * @return Config|null
     */
    function getConfig():?Config;
}