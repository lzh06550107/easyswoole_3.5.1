<?php


namespace EasySwoole\SyncInvoker;

use EasySwoole\SyncInvoker\Exception\Exception;

/**
 * 驱动工作类
 */
abstract class AbstractDriver
{
    private $allowMethods = [];
    private $request;
    private $response;

    function __construct()
    {
        //支持在子类控制器中以private，protected来修饰某个方法不可见
        $list = [];
        $ref = new \ReflectionClass(static::class);
        $public = $ref->getMethods(\ReflectionMethod::IS_PUBLIC); // 反射获取所有公共方法
        foreach ($public as $item) {
            array_push($list, $item->getName());
        }
        $this->allowMethods = array_diff($list,
            [
                '__hook', '__destruct',
                '__clone', '__construct', '__call',
                '__callStatic', '__get', '__set',
                '__isset', '__unset', '__sleep',
                '__wakeup', '__toString', '__invoke',
                '__set_state', '__clone', '__debugInfo','response'
            ]
        );
    }

    function __hook(Command $command)
    {
        $this->request = $command;
        try {
            if($this->onRequest($command) === false){ // 命令执行前执行的方法
                return $this->response;
            }
            if (in_array($command->getAction(), $this->allowMethods)) {
                $actionName = $command->getAction();
            } else {
                $actionName = 'actionNotFound';
            }
            call_user_func([$this,$actionName],...$command->getArg()); // 执行命令
            $this->afterRequest(); // 命令执行后执行的方法
            return $this->response;
        } catch (\Throwable $throwable) {
            $this->onException($throwable); // 异常处理方法
            return $this->response;
        }
    }

    /**
     * 命令执行前执行方法，子类重载该方法
     * @param Command $command
     * @return bool
     */
    protected function onRequest(Command $command):bool
    {
        return true;
    }

    /**
     * 命令执行后执行方法，子类重载该方法
     * @return void
     */
    protected function afterRequest()
    {

    }

    protected function getRequest():Command
    {
        return $this->request;
    }

    function response($response): void
    {
        $this->response = $response;
    }

    protected function actionNotFound()
    {
        throw new Exception("action {$this->getRequest()->getAction()} not exit");
    }

    /**
     * 命令执行异常处理方法，子类重载该方法
     * @param \Throwable $throwable
     * @return mixed
     * @throws \Throwable
     */
    protected function onException(\Throwable $throwable)
    {
        throw $throwable;
    }

    public function callback(?callable $callback)
    {
        if(is_callable($callback)){
            $ret = call_user_func($callback,$this);
            if($ret !== null && $this->response === null){
                $this->response = $ret;
            }
        }else{
            throw new Exception('callback method require a callable callback');
        }
    }
}