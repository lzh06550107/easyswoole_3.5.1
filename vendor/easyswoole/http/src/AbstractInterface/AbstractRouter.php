<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/29
 * Time: 下午4:00
 */

namespace EasySwoole\Http\AbstractInterface;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

/**
 * 抽象路由管理器
 */
abstract class AbstractRouter
{
    const PARSE_PARAMS_NONE = 0;
    const PARSE_PARAMS_IN_GET = 1;
    const PARSE_PARAMS_IN_POST = 2;
    const PARSE_PARAMS_IN_CONTEXT = 3;

    const PARSE_PARAMS_CONTEXT_KEY = 'PARSE_PARAMS_CONTEXT_KEY';

    private $routeCollector;
    private $methodNotAllowCallBack = null; // 未找到方法的回调
    private $routerNotFoundCallBack = null; // 路由匹配错误
    private $globalMode = false; // 全局模式拦截下，路由将只匹配 Router.php 中的控制器方法进行响应，将不会执行框架的默认解析
    private $pathInfoMode = true; // 是否是路径信息模式
    private $injectParams = AbstractRouter::PARSE_PARAMS_IN_CONTEXT; // 绑定的参数将由框架内部进行组装到框架的 Context(上下文) 数据之中

    final function __construct()
    {
        // 初始化路由收集器对象
        $this->routeCollector = new RouteCollector(new Std(),new GroupCountBased());
        $this->initialize($this->routeCollector); // 实例化的时候自动收集用户注册的路由
    }

    /**
     * 子类重载该方法来添加路由
     * @param RouteCollector $routeCollector
     * @return mixed
     */
    abstract function initialize(RouteCollector $routeCollector);

    /**
     * @return bool
     */
    public function isPathInfoMode(): bool
    {
        return $this->pathInfoMode;
    }

    /**
     * @param bool $pathInfoMode
     */
    public function setPathInfoMode(bool $pathInfoMode): void
    {
        $this->pathInfoMode = $pathInfoMode;
    }

    function getRouteCollector():RouteCollector
    {
        return $this->routeCollector;
    }


    function setMethodNotAllowCallBack(callable $call)
    {
        $this->methodNotAllowCallBack = $call;
    }

    function getMethodNotAllowCallBack()
    {
        return $this->methodNotAllowCallBack;
    }

    /**
     * @return null
     */
    public function getRouterNotFoundCallBack()
    {
        return $this->routerNotFoundCallBack;
    }

    /**
     * @param null $routerNotFoundCallBack
     */
    public function setRouterNotFoundCallBack($routerNotFoundCallBack): void
    {
        $this->routerNotFoundCallBack = $routerNotFoundCallBack;
    }

    /**
     * 全局模式
     * @return bool
     */
    public function isGlobalMode(): bool
    {
        return $this->globalMode;
    }

    public function setGlobalMode(bool $globalMode): AbstractRouter
    {
        $this->globalMode = $globalMode;
        return $this;
    }

    /**
     * 设置解析的路由参数存放在哪里
     * @param int|null $injectWay
     * @return $this|int
     */
    public function parseParams(?int $injectWay = null)
    {
        if($injectWay === null){
            return $this->injectParams;
        }else{
            $this->injectParams = $injectWay;
            return $this;
        }
    }
}