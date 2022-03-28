<?php


namespace EasySwoole\EasySwoole\Http;

use EasySwoole\Component\Singleton;
use EasySwoole\Http\Dispatcher as HttpDispatcher;

/**
 * 分发管理器
 */
class Dispatcher extends HttpDispatcher
{
    use Singleton;
}