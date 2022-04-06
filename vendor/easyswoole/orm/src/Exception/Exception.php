<?php


namespace EasySwoole\ORM\Exception;

use EasySwoole\ORM\Db\Result;

/**
 * 数据库操作异常类，多了两个额外方法
 */
class Exception extends \Exception
{
    /** @var Result */
    private $lastQueryResult;

    public function lastQueryResult(): ?Result
    {
        return $this->lastQueryResult;
    }

    public function setLastQueryResult(Result $result)
    {
        $this->lastQueryResult = $result;
    }
}