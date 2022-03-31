<?php

namespace EasySwoole\Http\GlobalParam;

use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Session\Session;
use EasySwoole\Spl\SplContextArray;
use EasySwoole\Utility\Random;
use Swoole\Coroutine;

/**
 * 全局拦截器
 */
class Hook
{
    const SESSION_CONTEXT = '_SESSION_CONTEXT_';
    /** @var Session */
    protected $session;
    /** @var SessionConfig */
    protected $sessionConfig;

    /**
     * 开启会话
     * @param Session $session
     * @return SessionConfig
     */
    public function enableSession(Session $session):SessionConfig
    {
        $this->session = $session;
        $this->sessionConfig = new SessionConfig();
        return $this->sessionConfig;
    }

    /**
     * 全局变量都保存到协程上下文中
     */
    public function register()
    {
        global $_GET;
        if(!$_GET instanceof SplContextArray){
            $_GET = new SplContextArray();
        }
        global $_COOKIE;
        if(!$_COOKIE instanceof SplContextArray){
            $_COOKIE = new SplContextArray();
        }
        global $_POST;
        if(!$_POST instanceof SplContextArray){
            $_POST = new SplContextArray();
        }
        global $_FILES;
        if(!$_FILES instanceof SplContextArray){
            $_FILES = new SplContextArray();
        }
        global $_SERVER;
        if(!$_SERVER instanceof SplContextArray){
            $_SERVER = new SplContextArray();
        }
        if ($this->session){
            global $_SESSION;
            if(!$_SESSION instanceof SplContextArray){
                $_SESSION = new SplContextArray();
            }
        }
    }

    /**
     * 请求处理前拦截回调
     * @param Request $request
     * @param Response $response
     * @throws \EasySwoole\Component\Context\Exception\ModifyError
     * @throws \EasySwoole\Session\Exception
     * @throws \Throwable
     */
    public function onRequest(Request $request,Response $response)
    {
        global $_GET;
        /** @var $_GET SplContextArray */
        $_GET->loadArray($request->getQueryParams()); // 全局GET变量被替换为支持协程上下文件的全局GET变量
        global $_COOKIE;
        /** @var $_COOKIE SplContextArray */
        $_COOKIE->loadArray($request->getCookieParams()); // 全局COOKIE变量被替换为支持协程上下文件的全局COOKIE变量
        global $_POST;
        /** @var $_POST SplContextArray */
        $_POST->loadArray($request->getParsedBody()); // 全局POST变量被替换为支持协程上下文件的全局POST变量
        global $_FILES;
        $files = [];
        if(!empty($request->getSwooleRequest()->files)){
            $files = $request->getSwooleRequest()->files;
        }
        /** @var $_FILES SplContextArray */
        $_FILES->loadArray($files); // 全局FILES变量被替换为支持协程上下文件的全局FILES变量
        global $_SERVER;
        $server = [];
        foreach ($request->getSwooleRequest()->header as $key => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }
        foreach ($request->getSwooleRequest()->server as $key => $value) {
            $server[strtoupper(str_replace('-', '_', $key))] = $value;
        }
        /** @var $_SERVER SplContextArray */
        $_SERVER->loadArray($server); // 全局SERVER变量被替换为支持协程上下文件的全局SERVER变量

        if ($this->session){ // 如果没有会话，则需要初始化一个会话对象
            /** @var $_SESSION SplContextArray */
            global $_SESSION;
            $sid = $request->getCookieParams($this->sessionConfig->getSessionName()); // 从cookie中获取会话id
            if(empty($sid)){ // 随机生成一个会话id
                $sid = Random::makeUUIDV4();
                $response->setCookie($this->sessionConfig->getSessionName(),$sid);
            }
            $context = $this->session->create($sid); // 创建会话上下文对象
            ContextManager::getInstance()->set(self::SESSION_CONTEXT,$context); // 协程上下文管理器保存该会话上下文对象
            $_SESSION->loadArray($context->allContext()); // 全局SESSION变量被替换为支持协程上下文件的全局SESSION变量
            Coroutine::defer(function ()use($context,$sid){
                /** @var $_SESSION SplContextArray */
                global $_SESSION;
                try{
                    $context->setData($_SESSION->toArray()); // 保存前需要更新会话上下文数据
                }catch (\Throwable $throwable){
                    throw $throwable;
                } finally {
                    $this->session->close($sid); // 关闭会话上下文对象
                }
            });
        }
    }
}