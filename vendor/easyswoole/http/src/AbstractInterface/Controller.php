<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午11:16
 */

namespace EasySwoole\Http\AbstractInterface;

use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

/**
 * 控制器抽象类
 */
abstract class Controller
{
    private $request;
    private $response;
    private $actionName;
    private $defaultProperties = [];
    private $allowMethodReflections = []; // 允许调用的控制器方法
    private $propertyReflections = [];

    function __construct()
    {
        $forbidList = [
            '__hook', '__destruct',
            '__clone', '__construct', '__call',
            '__callStatic', '__get', '__set',
            '__isset', '__unset', '__sleep',
            '__wakeup', '__toString', '__invoke',
            '__set_state', '__clone', '__debugInfo',
            'onRequest'
        ];

        //支持在子类控制器中以private，protected来修饰某个方法不可见
        $ref = new \ReflectionClass(static::class);
        $public = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($public as $item) {
            if((!in_array($item->getName(),$forbidList)) && (!$item->isStatic())){
                $this->allowMethodReflections[$item->getName()] = $item;
            }
        }

        //获取，生成属性默认值
        $ref = new \ReflectionClass(static::class);
        $properties = $ref->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            $this->propertyReflections[$name] = $property;
            //不重置静态变量与私有变量，但重置public和protected变量
            if (($property->isPublic() || $property->isProtected()) && !$property->isStatic()) {
                $this->defaultProperties[$name] = $this->{$name};
            }
        }
    }

    protected function getAllowMethodReflections()
    {
        return $this->allowMethodReflections;
    }

    protected function getPropertyReflections():array
    {
        return $this->propertyReflections;
    }

    /**
     * 恢复控制器对象的默认值
     */
    protected function gc()
    {
        //恢复默认值
        foreach ($this->defaultProperties as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * 控制器默认方法
     */
    function index()
    {
        $class = static::class;
        $this->writeJson(Status::CODE_OK,null,"this is {$class} index action");
    }

    /**
     * 控制器动作没有发现
     * @param string|null $action
     */
    protected function actionNotFound(?string $action)
    {
        $class = static::class;
        $this->writeJson(Status::CODE_NOT_FOUND,null,"{$class} has not action for {$action}");
    }

    /**
     * 控制器范围请求后拦截回调
     * @param string|null $actionName
     */
    protected function afterAction(?string $actionName): void
    {
    }

    /**
     * 控制器范围异常处理
     * @param \Throwable $throwable
     * @throws \Throwable
     */
    protected function onException(\Throwable $throwable): void
    {
        throw $throwable;
    }

    /**
     * 控制器范围内的请求前拦截器
     * @param string|null $action
     * @return bool|null
     */
    protected function onRequest(?string $action): ?bool
    {
        return true;
    }

    protected function getActionName(): ?string
    {
        return $this->actionName;
    }

    protected function request(): Request
    {
        return $this->request;
    }

    protected function response(): Response
    {
        return $this->response;
    }

    /**
     * 写入响应数据
     * @param $statusCode
     * @param $result
     * @param $msg
     * @return bool
     */
    protected function writeJson($statusCode = 200, $result = null, $msg = null)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                "code" => $statusCode, // 返回码
                "result" => $result, // 操作结果
                "msg" => $msg // 一般为错误消息
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取请求体中json格式的数据
     * @return array|null
     */
    protected function json(): ?array
    {
        return json_decode($this->request()->getBody()->__toString(), true);
    }

    /**
     * 获取请求体中xml格式的数据
     * @param $options
     * @param string $className
     * @return false|\SimpleXMLElement|string
     */
    protected function xml($options = LIBXML_NOERROR | LIBXML_NOCDATA, string $className = 'SimpleXMLElement')
    {
        if (\PHP_VERSION_ID < 80000 || \LIBXML_VERSION < 20900){
            libxml_disable_entity_loader(true);
        }
        return simplexml_load_string($this->request()->getBody()->__toString(), $className, $options);
    }

    //该方法用于保留对外调用
    public function __hook(?string $actionName, Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->actionName = $actionName;
        return $this->__exec();
    }
    
    //允许用户修改整个请求执行流程
    protected function __exec()
    {
        $actionName = $this->actionName;
        $forwardPath = null;
        try {
            if ($this->onRequest($actionName) !== false) { // 如果不为false，则继续执行控制器动作
                if (isset($this->allowMethodReflections[$actionName])) {
                    $forwardPath = $this->$actionName(); // 可以返回其它路由路径
                } else {
                    $forwardPath = $this->actionNotFound($actionName);
                }
            }
        } catch (\Throwable $throwable) {
            //若没有重构onException，直接抛出给上层
            $this->onException($throwable);
        } finally {
            try {
                $this->afterAction($actionName);
            } catch (\Throwable $throwable) {
                $this->onException($throwable);
            } finally {
                try {
                    $this->gc(); // 清理控制器实例对象
                } catch (\Throwable $throwable) {
                    $this->onException($throwable);
                } finally {
                    unset($this->request);
                    unset($this->response);
                }
            }
        }
        return $forwardPath;
    }
}
