<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/14
 * Time: 下午6:15
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Event;
use EasySwoole\Component\Singleton;
use EasySwoole\Log\LoggerInterface;

/**
 * 日志管理器
 */
class Logger
{
    private $logger; // 日志记录器实例对象

    private $callback; // 日志监听器

    private $logConsole = true; // 设置记录日志到日志文件时是否在控制台打印日志

    private $displayConsole = true; // 设置是否开启在控制台打印日志

    private $ignoreCategory = []; // 忽略分类

    private $logLevel = LoggerInterface::LOG_LEVEL_INFO; // 日志输出层级

    use Singleton;

    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->callback = new Event();
    }

    public function onLog(): Event
    {
        return $this->callback;
    }

    public function logLevel(?int $level = null)
    {
        if ($level !== null) {
            $this->logLevel = $level;
            return $this;
        }
        return $this->logLevel;
    }

    public function displayConsole(?bool $is = null)
    {
        if($is === null){
            return $this->displayConsole;
        }else{
            $this->displayConsole = $is;
            return $this;
        }
    }

    public function logConsole(?bool $is = null)
    {
        if ($is === null) {
            return $this->logConsole;
        } else {
            $this->logConsole = $is;
            return $this;
        }
    }

    public function ignoreCategory(?array $arr = null)
    {
        if ($arr === null) {
            return $this->ignoreCategory;
        } else {
            $this->ignoreCategory = $arr;
            return $this;
        }
    }

    /**
     * 输出日志通用方法
     * @param string|null $msg 日志消息
     * @param int $logLevel 日志层级
     * @param string $category 日志分类
     * @return void
     */
    public function log(?string $msg, int $logLevel = LoggerInterface::LOG_LEVEL_DEBUG, string $category = 'debug')
    {
        if ($logLevel < $this->logLevel) {
            return;
        }

        if (in_array($category, $this->ignoreCategory)) {
            return;
        }

        if ($this->logConsole) { // 输出日志到控制台
            $this->console($msg, $logLevel, $category);
        }

        $this->logger->log($msg, $logLevel, $category); // 输出日志到文件
        $calls = $this->callback->all();
        foreach ($calls as $call) { // 如果存在日志监听器，则触发监听器
            call_user_func($call, $msg, $logLevel, $category);
        }
    }

    public function console(?string $msg, int $logLevel = LoggerInterface::LOG_LEVEL_DEBUG, string $category = 'debug')
    {
        if($this->displayConsole){ // 日志输出到控制台
            $this->logger->console($msg, $logLevel, $category);
        }
    }

    public function debug(?string $msg, string $category = 'debug')
    {
        $this->log($msg, LoggerInterface::LOG_LEVEL_DEBUG, $category);
    }

    public function info(?string $msg, string $category = 'info')
    {
        $this->log($msg, LoggerInterface::LOG_LEVEL_INFO, $category);
    }

    public function notice(?string $msg, string $category = 'notice')
    {
        $this->log($msg, LoggerInterface::LOG_LEVEL_NOTICE, $category);
    }

    public function waring(?string $msg, string $category = 'waring')
    {
        $this->log($msg, LoggerInterface::LOG_LEVEL_WARNING, $category);
    }

    public function error(?string $msg, string $category = 'error')
    {
        $this->log($msg, LoggerInterface::LOG_LEVEL_ERROR, $category);
    }


}
