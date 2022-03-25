<?php


namespace EasySwoole\Config;

/**
 * 配置存储处理器抽象类
 */
abstract class AbstractConfig
{
    /**
     * 获取指定key的配置
     * @param $key
     * @return mixed
     */
    abstract function getConf($key = null);

    /**
     * 设置指定key的配置
     * @param $key
     * @param $val
     * @return bool
     */
    abstract function setConf($key,$val):bool ;

    /**
     * 批量加载整个配置，会覆盖
     * @param array $array
     * @return bool
     */
    abstract function load(array $array):bool ;

    /**
     * 批量加载整个配置，会合并
     * @param array $array
     * @return bool
     */
    abstract function merge(array $array):bool ;

    /**
     * 清理所有配置
     * @return bool
     */
    abstract function clear():bool ;

    /**
     * 返回底层配置存储器
     * @return mixed
     */
    abstract function storage();
}