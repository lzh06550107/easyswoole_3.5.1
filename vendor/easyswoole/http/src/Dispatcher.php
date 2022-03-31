<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午2:56
 */

namespace EasySwoole\Http;

use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Exception\ControllerPoolEmpty;
use EasySwoole\Http\Exception\RouterError;
use EasySwoole\Http\Message\Status;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\Dispatcher as RouterDispatcher;
use Swoole\Coroutine\Channel;

/**
 * http调度器
 */
class Dispatcher
{
    private $router = null; // 路由器对象
    /**
     * @var AbstractRouter|null
     */
    private $routerRegister = null; // 路由管理器实例对象
    private $controllerPoolCreateNum = []; // 指定的控制器创建实例个数
    //以下为外部配置项目
    private $namespacePrefix; // 控制器命名空间前缀
    private $maxDepth; // 最大深度
    private $maxPoolNum; // 最大池数量
    private $httpExceptionHandler = null; // http异常处理器
    private $controllerPoolWaitTime = 5.0; // 从池中获取控制器等待时间
    /** @var callable */
    private $onRouterCreate; // 路由创建时的回调函数

    function __construct(string $namespacePrefix = null,int $maxDepth = 5,int $maxPoolNum = 200)
    {
        if($namespacePrefix !== null){
            $this->namespacePrefix = trim($namespacePrefix,'\\');
        }
        $this->maxPoolNum = $maxPoolNum;
        $this->maxDepth = $maxDepth;
    }

    /**
     * 设置控制器命名空间前缀
     * @param string $space
     * @return $this
     */
    function setNamespacePrefix(string $space):Dispatcher
    {
        $this->namespacePrefix = trim($space,'\\');
        return $this;
    }

    /**
     *
     * @param float $controllerPoolWaitTime
     * @return $this
     */
    public function setControllerPoolWaitTime(float $controllerPoolWaitTime):Dispatcher
    {
        $this->controllerPoolWaitTime = $controllerPoolWaitTime;
        return $this;
    }

    public function setControllerMaxPoolNum(int $num):Dispatcher
    {
        $this->maxPoolNum = $num;
        return $this;
    }

    function setOnRouterCreate(callable $call):Dispatcher
    {
        $this->onRouterCreate = $call;
        return $this;
    }

    function setHttpExceptionHandler(callable $handler):Dispatcher
    {
        $this->httpExceptionHandler = $handler;
        return $this;
    }

    function setMaxDepth($depth):Dispatcher
    {
        $this->maxDepth = $depth;
        return $this;
    }

    /**
     * 分发请求
     * @param Request $request
     * @param Response $response
     * @throws RouterError
     * @throws \EasySwoole\Component\Context\Exception\ModifyError
     */
    public function dispatch(Request $request,Response $response):void
    {
        // 进行一次初始化判定
        if($this->router === null){ // 如果存在路由器
            $r = $this->initRouter( $this->namespacePrefix.'\\Router'); // 加载路由管理器文件并收集用户定义的路由
            if($r instanceof AbstractRouter){
                if (is_callable($this->onRouterCreate)) {
                    call_user_func($this->onRouterCreate,$r); // 调用路由初始化回调函数
                }
                $this->routerRegister = $r;
                $data = $r->getRouteCollector()->getData(); // 获取路由表
                if(!empty($data)){
                    $this->router = new GroupCountBased($data); // 初始化路由调度器
                }else{
                    $this->router = false;
                }
            }else{
                $this->router = false;
            }
        }

        $path = UrlParser::pathInfo($request->getUri()->getPath());
        if($this->router instanceof GroupCountBased){
            if($this->routerRegister->isPathInfoMode()){ // 路径信息模式
                $routerPath = $path;
            }else{
                $routerPath = $request->getUri()->getPath();
            }
            $handler = null;
            $routeInfo = $this->router->dispatch($request->getMethod(),$routerPath); // 获取路由信息
            if($routeInfo !== false){
                switch ($routeInfo[0]) {
                    case RouterDispatcher::FOUND:{ // 如果找到路由
                        $handler = $routeInfo[1]; // 获取处理器
                        $inject = $this->routerRegister->parseParams(); // 获取路由参数存放位置
                        //合并解析出来的数据
                        switch ($inject){
                            case AbstractRouter::PARSE_PARAMS_IN_GET:{ // 路由参数放入GET中
                                $vars = $routeInfo[2];
                                $data = $request->getQueryParams();
                                $request->withQueryParams($vars+$data);
                                break;
                            }
                            case AbstractRouter::PARSE_PARAMS_IN_POST:{ // 路由参数放入POST中
                                $vars = $routeInfo[2];
                                $data = $request->getParsedBody();
                                $request->withParsedBody($vars + $data);
                                break;
                            }
                            case AbstractRouter::PARSE_PARAMS_IN_CONTEXT:{ // 路由参数放入协程上下文中
                                $vars = $routeInfo[2];
                                ContextManager::getInstance()->set(AbstractRouter::PARSE_PARAMS_CONTEXT_KEY,$vars);
                                break;
                            }
                            case AbstractRouter::PARSE_PARAMS_NONE:
                            default:{
                                break;
                            }
                        }
                        break;
                    }
                    case RouterDispatcher::METHOD_NOT_ALLOWED:{ // 如果未找到方法，则调用未找到方法回调
                        $handler = $this->routerRegister->getMethodNotAllowCallBack();
                        break;
                    }
                    case RouterDispatcher::NOT_FOUND: // 如果没有找到路由，则调用回调
                    default:{
                        $handler = $this->routerRegister->getRouterNotFoundCallBack();
                        break;
                    }
                }
                //如果handler不为null，那么说明，非为 \FastRoute\Dispatcher::FOUND ，因此执行
                if(is_callable($handler)){
                    try{
                        $ret = call_user_func($handler,$request,$response);
                        if(is_string($ret)){
                            $path = UrlParser::pathInfo($ret); // 回调函数可能是请求路径
                            $request->getUri()->withPath($path);
                            goto execController;
                        }else if($ret === false){ // 回调函数中断执行，请求结束
                            return;
                        }
                    }catch (\Throwable $throwable){
                        $this->onException($throwable,$request,$response);
                        //出现异常的时候，不在往下dispatch
                        return;
                    }
                }else if(is_string($handler)){
                    $path = UrlParser::pathInfo($handler);
                    $request->getUri()->withPath($path);
                    goto execController;
                }
            }
            //全局模式的时候，都拦截。非全局模式，否则继续往下
            if($this->routerRegister->isGlobalMode()){
                return;
            }
        }
        execController:{
            $this->controllerExecutor($request,$response,$path);
        }

    }

    /**
     * 执行控制器方法
     * @param Request $request
     * @param Response $response
     * @param string $path
     */
    private function controllerExecutor(Request $request, Response $response, string $path)
    {
        $pathInfo = ltrim($path,"/");
        $list = explode("/",$pathInfo);
        $actionName = null;
        $finalClass = null;
        $controlMaxDepth = $this->maxDepth;
        $currentDepth = count($list);
        $maxDepth = $currentDepth < $controlMaxDepth ? $currentDepth : $controlMaxDepth;
        while ($maxDepth >= 0){
            $className = '';
            for ($i=0 ;$i<$maxDepth;$i++){
                $className = $className."\\".ucfirst($list[$i] ?: 'Index');//为一级控制器Index服务
            }
            if(class_exists($this->namespacePrefix.$className)){
                //尝试获取该class后的actionName
                $actionName = empty($list[$i]) ? 'index' : $list[$i];
                $finalClass = $this->namespacePrefix.$className;
                break;
            }else{
                //尝试搜搜index控制器
                $temp = $className."\\Index";
                if(class_exists($this->namespacePrefix.$temp)){
                    $finalClass = $this->namespacePrefix.$temp;
                    //尝试获取该class后的actionName
                    $actionName = empty($list[$i]) ? 'index' : $list[$i];
                    break;
                }
            }
            $maxDepth--;
        }

        if(!empty($finalClass)){
            try{
                $controllerObject = $this->getController($finalClass);
            }catch (\Throwable $throwable){
                $this->onException($throwable,$request,$response);
                return;
            }
            if($controllerObject instanceof Controller){
                try{
                    // 调用控制器钩子
                    $forward = $controllerObject->__hook($actionName,$request,$response);
                    if(is_string($forward) && (strlen($forward) > 0) && ($forward != $path)){ // 如果相同，则会无限循环
                        $forward = UrlParser::pathInfo($forward);
                        $request->getUri()->withPath($forward);
                        $this->dispatch($request,$response); // 重新调度
                    }
                }catch (\Throwable $throwable){
                    $this->onException($throwable,$request,$response);
                }finally { // 该控制器对象需要重新入池中
                    $this->recycleController($finalClass,$controllerObject);
                }
            }else{
                $throwable = new ControllerPoolEmpty('controller pool empty for '.$finalClass);
                $this->onException($throwable,$request,$response);
            }
        }else{
            $response->withStatus(404);
            $response->write("not controller class match");
        }
    }

    /**
     * 如果定义了异常处理器，则直接调用，如果没有，则直接写入到客户端
     * @param \Throwable $throwable
     * @param Request $request
     * @param Response $response
     */
    protected function onException(\Throwable $throwable, Request $request, Response $response)
    {
        if(is_callable($this->httpExceptionHandler)){
            call_user_func($this->httpExceptionHandler,$throwable,$request,$response);
        }else{
            $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
            $response->write(nl2br($throwable->getMessage()."\n".$throwable->getTraceAsString()));
        }
    }

    /**
     * 重新创建或者从池中获取一个控制器实例对象
     * @param string $class
     * @return mixed
     * @throws \Throwable
     */
    protected function getController(string $class)
    {
        $classKey = $this->generateClassKey($class);
        if(!isset($this->$classKey)){ // 分发器是否存在该控制器实例
            $this->$classKey = new Channel($this->maxPoolNum+1); // 最多可以保存多少个该控制器实例对象
            $this->controllerPoolCreateNum[$classKey] = 0;
        }
        $channel = $this->$classKey;
        //懒惰创建模式
        /** @var Channel $channel */
        if($channel->isEmpty()){
            $createNum = $this->controllerPoolCreateNum[$classKey];
            if($createNum < $this->maxPoolNum){ // 小于池中数量，则可以继续创建
                $this->controllerPoolCreateNum[$classKey] = $createNum+1;
                try{
                    //防止用户在控制器结构函数做了什么东西导致异常
                    return new $class();
                }catch (\Throwable $exception){
                    $this->controllerPoolCreateNum[$classKey] = $createNum;
                    //直接抛给上层
                    throw $exception;
                }
            } // 超过池中限制数量，则从池中获取
            return $channel->pop($this->controllerPoolWaitTime);
        } // 池中存在控制器对象，则从池中获取
        return $channel->pop($this->controllerPoolWaitTime);
    }

    /**
     * 生成控制器缓存key
     * @param string $class
     * @return string
     */
    private function generateClassKey(string $class):string
    {
        return substr(md5($class), 8, 16);
    }

    /**
     * 该控制器对象需要重新入池中
     * @param string $class
     * @param Controller $obj
     */
    private function recycleController(string $class,Controller $obj)
    {
        $classKey = $this->generateClassKey($class);
        /** @var Channel $channel */
        $channel = $this->$classKey;
        $channel->push($obj);
    }

    /**
     * 初始化路由器对象
     * @param string|null $class 路由器类
     * @return AbstractRouter|null
     * @throws RouterError
     * @throws \ReflectionException
     */
    protected function initRouter(?string $class = null):?AbstractRouter
    {
        if(class_exists($class)){
            $ref = new \ReflectionClass($class);
            if($ref->isSubclassOf(AbstractRouter::class)){
                return $ref->newInstance();
            }else{
                throw new RouterError("class : {$class} not AbstractRouter class");
            }
        }else{
            return null;
        }
    }
}