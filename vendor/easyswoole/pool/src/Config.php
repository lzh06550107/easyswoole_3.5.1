<?php


namespace EasySwoole\Pool;

use EasySwoole\Pool\Exception\Exception;
use EasySwoole\Spl\SplBean;

/**
 * 通用连接池配置类
 */
class Config extends SplBean
{
    protected $intervalCheckTime = 15*1000; // 设置 连接池定时器执行频率
    protected $maxIdleTime = 10; // 设置 连接池对象最大闲置未使用时间 (秒)
    protected $maxObjectNum = 20; // 设置 连接池最大对象数量
    protected $minObjectNum = 5; // 设置 连接池最小对象数量
    protected $getObjectTimeout = 3.0; // 设置 获取连接池中对象的超时时间
    protected $loadAverageTime = 0.001; // 设置 负载阈值，记录的是平均每个链接取出的时间

    protected $extraConf; // 额外配置

    /**
     * @return float|int
     */
    public function getIntervalCheckTime()
    {
        return $this->intervalCheckTime;
    }

    /**
     * @param $intervalCheckTime
     * @return Config
     */
    public function setIntervalCheckTime($intervalCheckTime): Config
    {
        $this->intervalCheckTime = $intervalCheckTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxIdleTime(): int
    {
        return $this->maxIdleTime;
    }

    /**
     * @param int $maxIdleTime
     * @return Config
     */
    public function setMaxIdleTime(int $maxIdleTime): Config
    {
        $this->maxIdleTime = $maxIdleTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxObjectNum(): int
    {
        return $this->maxObjectNum;
    }

    public function setMaxObjectNum(int $maxObjectNum): Config
    {
        if($this->minObjectNum >= $maxObjectNum){
            throw new Exception('min num is bigger than max');
        }
        $this->maxObjectNum = $maxObjectNum;
        return $this;
    }

    /**
     * @return float
     */
    public function getGetObjectTimeout(): float
    {
        return $this->getObjectTimeout;
    }

    /**
     * @param float $getObjectTimeout
     * @return Config
     */
    public function setGetObjectTimeout(float $getObjectTimeout): Config
    {
        $this->getObjectTimeout = $getObjectTimeout;
        return $this;
    }

    public function getExtraConf()
    {
        return $this->extraConf;
    }

    /**
     * @param $extraConf
     * @return Config
     */
    public function setExtraConf($extraConf): Config
    {
        $this->extraConf = $extraConf;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinObjectNum(): int
    {
        return $this->minObjectNum;
    }

    /**
     * @return float
     */
    public function getLoadAverageTime(): float
    {
        return $this->loadAverageTime;
    }

    /**
     * @param float $loadAverageTime
     * @return Config
     */
    public function setLoadAverageTime(float $loadAverageTime): Config
    {
        $this->loadAverageTime = $loadAverageTime;
        return $this;
    }

    public function setMinObjectNum(int $minObjectNum): Config
    {
        if($minObjectNum >= $this->maxObjectNum){
            throw new Exception('min num is bigger than max');
        }
        $this->minObjectNum = $minObjectNum;
        return $this;
    }
}