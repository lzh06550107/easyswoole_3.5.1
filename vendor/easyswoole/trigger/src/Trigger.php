<?php


namespace EasySwoole\Trigger;

use EasySwoole\Log\LoggerInterface;

/**
 * 触发器实现类
 */
class Trigger implements TriggerInterface
{

    protected $logger; // 日志记录器

    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 触发器触发错误，该函数会打印错误到控制台
     * @param $msg
     * @param int $errorCode
     * @param Location|null $location
     * @return mixed|void
     */
    public function error($msg, int $errorCode = E_USER_ERROR, Location $location = null)
    {
        if($location == null){
            $location = new Location();
            $debugTrace = debug_backtrace();
            $caller = array_shift($debugTrace);
            $location->setLine($caller['line']);
            $location->setFile($caller['file']);
        }
        $this->logger->console("{$msg} at file:{$location->getFile()} line:{$location->getLine()}",$this->errorMapLogLevel($errorCode));
    }

    /**
     * 触发器触发异常，该函数会打印错误到控制台
     * @param \Throwable $throwable
     * @return mixed|void
     */
    public function throwable(\Throwable $throwable)
    {
        $msg = "{$throwable->getMessage()} at file:{$throwable->getFile()} line:{$throwable->getLine()}";
        $this->logger->console($msg,LoggerInterface::LOG_LEVEL_ERROR);
    }

    /**
     * 错误码转换为日志层级
     * @param int $errorCode
     * @return int
     */
    private function errorMapLogLevel(int $errorCode)
    {
        switch ($errorCode){
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return LoggerInterface::LOG_LEVEL_ERROR;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                return LoggerInterface::LOG_LEVEL_WARNING;
            case E_NOTICE:
            case E_USER_NOTICE:
                return LoggerInterface::LOG_LEVEL_NOTICE;
            case E_STRICT:
                return LoggerInterface::LOG_LEVEL_NOTICE;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return LoggerInterface::LOG_LEVEL_NOTICE;
            default :
                return LoggerInterface::LOG_LEVEL_INFO;
        }
    }
}