<?php

namespace EasySwoole\Mysqli;

use EasySwoole\Spl\SplBean;

/**
 * 数据库连接配置类
 */
class Config extends SplBean
{
    protected $host;
    protected $user;
    protected $password;
    protected $database;//数据库
    protected $port = 3306;
    protected $timeout = 30;
    protected $charset = 'utf8';
    protected $strict_type =  false; //开启严格模式，返回的字段将自动转为数字类型
    protected $fetch_mode = false;//开启fetch模式, 可与pdo一样使用fetch/fetchAll逐行
    protected $maxReconnectTimes = 3;

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * 设置连接主机地址
     * @param mixed $host
     */
    public function setHost($host): void
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * 设置连接登录用户
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * 设置连接登录密码
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * 设置连接的数据库
     * @param mixed $database
     */
    public function setDatabase($database): void
    {
        $this->database = $database;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * 设置连接的端口
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * 设置连接超时时间
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * 设置连接的编码
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @return bool
     */
    public function isStrictType(): bool
    {
        return $this->strict_type;
    }

    /**
     * 设置操作是否开启严格模式
     * @param bool $strict_type
     */
    public function setStrictType(bool $strict_type): void
    {
        $this->strict_type = $strict_type;
    }

    /**
     * @return bool
     */
    public function isFetchMode(): bool
    {
        return $this->fetch_mode;
    }

    /**
     * 设置获取模式
     * @param bool $fetch_mode
     */
    public function setFetchMode(bool $fetch_mode): void
    {
        $this->fetch_mode = $fetch_mode;
    }

    /**
     * @return int
     */
    public function getMaxReconnectTimes(): int
    {
        return $this->maxReconnectTimes;
    }

    /**
     * 设置最大重连时间
     * @param int $maxReconnectTimes
     */
    public function setMaxReconnectTimes(int $maxReconnectTimes): void
    {
        $this->maxReconnectTimes = $maxReconnectTimes;
    }
}