<?php


namespace EasySwoole\ORM\Db;

use EasySwoole\Mysqli\Client;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Pool\ObjectInterface;

/**
 * orm使用mysqli客户端来连接数据库
 */
class MysqliClient extends Client implements ClientInterface, ObjectInterface
{

    private $name; // 连接名称

    /**
     * 设置连接名称
     * @param string|null $name
     * @return string|null
     */
    public function connectionName(?string $name = null): ?string
    {
        if($name !== null){
            $this->name = $name;
        }
        return $this->name;
    }

    /**
     * 执行查询
     * @param QueryBuilder $builder 查询构建器对象
     * @param bool $rawQuery 是否执行原生sql
     * @return Result 执行结果
     * @throws Exception
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \Throwable
     */
    public function query(QueryBuilder $builder, bool $rawQuery = false): Result
    {
        $result = new Result();
        $ret = null;
        $errno = 0;
        $error = '';
        $stmt = null;
        
        try {
            if ($rawQuery) {
                // $builder->getLastQuery() 获取构建的SQL(已替换占位符)
                $ret = $this->rawQuery($builder->getLastQuery(), $this->config->getTimeout());
            } else {
                $stmt = $this->mysqlClient()->prepare($builder->getLastPrepareQuery(), $this->config->getTimeout());
                if ($stmt) {
                    $ret = $stmt->execute($builder->getLastBindParams(), $this->config->getTimeout());
                } else {
                    $ret = false;
                }
            }

            $errno = $this->mysqlClient()->errno; // 错误号
            $error = $this->mysqlClient()->error; // 错误消息
            $insert_id = $this->mysqlClient()->insert_id; // 插入数据id
            $affected_rows = $this->mysqlClient()->affected_rows; // 影响的行数
            /*
             * 重置mysqli客户端成员属性，避免下次使用
             */
            $this->mysqlClient()->errno = 0;
            $this->mysqlClient()->error = '';
            $this->mysqlClient()->insert_id = 0;
            $this->mysqlClient()->affected_rows = 0;
            //结果设置
            if (!$rawQuery && $ret && $this->config->isFetchMode()) {
                $result->setResult(new Cursor($stmt));
            } else {
                $result->setResult($ret);
            }
            $result->setLastError($error);
            $result->setLastErrorNo($errno);
            $result->setLastInsertId($insert_id);
            $result->setAffectedRows($affected_rows);
        } catch (\Throwable $throwable) {
            throw $throwable; // 向上层抛出异常
        } finally {
            if ($errno) { // 如果存在错误

                /**
                 * 断线收回链接
                 */
                if (in_array($errno, [2006, 2013])) {
                    $this->close(); // 断线，则关闭连接
                }

                // 封装错误，并抛出
                $exception = new Exception($error . " [{$builder->getLastQuery()}]");
                $exception->setLastQueryResult($result);
                throw $exception;
            }
        }
        return $result;
    }

    /**
     * 关闭连接
     */
    function gc()
    {
        $this->close();
    }

    /**
     * 
     */
    function objectRestore()
    {
        $this->reset(); // 重置连接的查询构建器，让连接可重用
    }

    function beforeUse(): ?bool
    {
        return $this->connect(); // 建立数据库连接
    }
}