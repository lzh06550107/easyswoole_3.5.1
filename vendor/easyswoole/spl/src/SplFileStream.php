<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/22
 * Time: 下午2:55
 */

namespace EasySwoole\Spl;

/**
 * 文件资源流数据操作
 */
class SplFileStream extends SplStream
{
    function __construct($file,$mode = 'c+')
    {
        $fp = fopen($file,$mode); // 打开指定的文件流
        parent::__construct($fp);
    }

    // 给文件加排它锁
    function lock($mode = LOCK_EX){
        return flock($this->getStreamResource(),$mode);
    }

    // 给文件解锁
    function unlock($mode = LOCK_UN){
        return flock($this->getStreamResource(),$mode);
    }
}