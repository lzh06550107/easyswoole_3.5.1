<?php

namespace EasySwoole\Mysqli;

use EasySwoole\Mysqli\Exception\Exception;
use Swoole\Coroutine\MySQL;

/**
 * mysqli客户端
 */
class Client
{
    protected $config; // 数据库连接配置

    protected $mysqlClient; // 客户端

    protected $queryBuilder; // 查询构建器

    protected $lastQueryBuilder; // 最近一次查询构建器

    protected $onQuery; // 查询结束后的回调函数

    function __construct(Config $config)
    {
        $this->config = $config;
        $this->queryBuilder = new QueryBuilder();
    }

    function onQuery(callable $call):Client
    {
        $this->onQuery = $call;
        return $this;
    }

    function queryBuilder():QueryBuilder
    {
        return $this->queryBuilder;
    }

    function lastQueryBuilder():?QueryBuilder
    {
        return $this->lastQueryBuilder;
    }

    /**
     * 重置查询器
     */
    function reset()
    {
        $this->queryBuilder()->reset();
    }

    /**
     * 使用查询构建器查询
     * @param float|null $timeout 连接超时时间
     * @return false
     * @throws Exception
     * @throws \Throwable
     */
    function execBuilder(float $timeout = null)
    {
        $this->lastQueryBuilder = $this->queryBuilder; // 保存最近一次查询构建器
        $start = microtime(true);

        if($timeout === null){
            $timeout = $this->config->getTimeout();
        }

        try{
            $this->connect(); // 连接数据库
            $stmt = $this->mysqlClient()->prepare($this->queryBuilder()->getLastPrepareQuery(),$timeout); // 预编译，设置超时时间
            $ret = null;
            if($stmt){
                $ret = $stmt->execute($this->queryBuilder()->getLastBindParams(),$timeout); // 绑定参数执行，设置超时时间
            }else{
                $ret = false;
            }
            if($this->onQuery){
                call_user_func($this->onQuery,$ret,$this,$start); // 传入查询结果、当前对象、查询开始时间
            }
            if($ret === false && $this->mysqlClient()->errno){
                throw new Exception($this->mysqlClient()->error); // 查询错误封装为异常抛出
            }
            return $ret;
        }catch (\Throwable $exception){
            throw $exception;
        }finally{
            $this->reset(); // 重置查询器
        }
    }

    /**
     * 原始sql语句查询
     * @param string $query
     * @param float|null $timeout
     * @return mixed
     * @throws Exception
     */
    function rawQuery(string $query,float $timeout = null)
    {
        $builder = new QueryBuilder();
        $builder->raw($query);
        $this->lastQueryBuilder = $builder; // 记录最近一次查询构建器
        $start = microtime(true);
        if($timeout === null){
            $timeout = $this->config->getTimeout();
        }
        $this->connect();
        $ret = $this->mysqlClient()->query($query,$timeout);
        if($this->onQuery){
            call_user_func($this->onQuery,$ret,$this,$start);
        }
        if($ret === false && $this->mysqlClient()->errno){
            throw new Exception($this->mysqlClient()->error);
        }
        return $ret;
    }

    function mysqlClient():?MySQL
    {
        return $this->mysqlClient;
    }

    /**
     * 连接数据库
     * @return bool
     */
    function connect():bool
    {
        if(!$this->mysqlClient instanceof MySQL){
            $this->mysqlClient = new MySQL(); // swoole协程客户端
        }
        if(!$this->mysqlClient->connected){
           return (bool)$this->mysqlClient->connect($this->config->toArray());
        }
        return true;
    }

    /**
     * 关闭数据库连接
     * @return bool
     */
    function close():bool
    {
        if($this->mysqlClient instanceof MySQL && $this->mysqlClient->connected){
            $this->mysqlClient->close();
            $this->mysqlClient = null;
        }
        return true;
    }

    /**
     * 对象销毁的时候自动关闭数据库连接
     */
    function __destruct()
    {
        $this->close();
    }
}