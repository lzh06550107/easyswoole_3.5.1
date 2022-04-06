<?php


namespace EasySwoole\ORM\Db;

use EasySwoole\Mysqli\QueryBuilder;

/**
 * 客户端接口类
 */
interface ClientInterface
{
    /**
     * 使用查询构建器查询
     * @param QueryBuilder $builder 查询构建器对象
     * @param bool $rawQuery 是否执行原生sql
     * @return Result
     */
    public function query(QueryBuilder $builder,bool $rawQuery = false): Result;

    /**
     * 获取数据库连接的名称
     * @param string|null $name 连接名称
     * @return string|null
     */
    public function connectionName(?string $name = null):?string;
}