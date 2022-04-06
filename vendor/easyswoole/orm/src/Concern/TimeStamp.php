<?php

namespace EasySwoole\ORM\Concern;

/**
 * 时间戳trait操作类，主要是数据创建和更新字段
 */
trait TimeStamp
{

    /** @var bool|string 是否开启时间戳 */
    protected  $autoTimeStamp = false;
    /** @var bool|string 创建时间字段名 false不设置 */
    protected  $createTime = 'create_time';
    /** @var bool|string 更新时间字段名 false不设置 */
    protected  $updateTime = 'update_time';

    /**
     * 获取自动更新时间戳设置
     * @return bool|string
     */
    public function getAutoTimeStamp()
    {
        return $this->autoTimeStamp;
    }

    /**
     * 获取创建时间的是否开启、字段名
     * @return bool|string
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     *  获取更新时间的是否开启、字段名
     * @return bool|string
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

}
