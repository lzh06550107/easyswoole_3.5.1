<?php

namespace EasySwoole\DatabaseMigrate;

use EasySwoole\Component\Singleton;
use EasySwoole\DatabaseMigrate\Config\Config;
use EasySwoole\Mysqli\Client;

/**
 * 数据库迁移管理器
 */
class MigrateManager
{
    use Singleton;

    /** @var Config */
    protected $config; // 迁移配置和数据库配置

    /** @var Client */
    protected $client; // mysql客户端配置

    private function __construct(?Config $config = null)
    {
        if (!$config) {
            $config = new Config();
        }
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        if (!$this->client instanceof Client) {
            $this->client = new Client($this->config);
        }
        return $this->client;
    }

    /**
     * 查询
     * @param string $sql
     * @return array|bool|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \Throwable
     * @author heelie.hj@gmail.com
     * @date 2021-04-07 08:50:54
     */
    public function query(string $sql)
    {
        $client = $this->getClient();
        $client->queryBuilder()->raw($sql);
        return $client->execBuilder();
    }

    /**
     * 插入
     * @param string $tableName
     * @param array $insertData
     * @return array|bool|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \Throwable
     * @author heelie.hj@gmail.com
     * @date 2021-04-18 21:05:12
     */
    public function insert(string $tableName, array $insertData)
    {
        $client = $this->getClient();
        $client->queryBuilder()->insert($tableName, $insertData);
        return $client->execBuilder();
    }

    /**
     * 删除
     * @param string $tableName
     * @param array $whereData
     * @param int|null $limit
     * @return array|bool|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \Throwable
     * @author heelie.hj@gmail.com
     * @date 2021-04-18 21:05:12
     */
    public function delete(string $tableName, array $whereData = [], ?int $limit = null)
    {
        $client = $this->getClient();
        $builder = $client->queryBuilder();
        foreach ($whereData as $key => $value) {
            $builder->where($key, $value);
        }
        $builder->delete($tableName, $limit);
        return $client->execBuilder();
    }
}
