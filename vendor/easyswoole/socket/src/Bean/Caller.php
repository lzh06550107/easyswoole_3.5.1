<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/10
 * Time: 上午10:57
 */

namespace EasySwoole\Socket\Bean;

/**
 * 封装请求类
 */
class Caller
{
    private $args = []; // 请求参数
    private $controllerClass = null; // 控制器类
    private $action = null; // 动作名称
    private $client; // 客户端连接实例

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }


    public function getControllerClass():?string
    {
        return $this->controllerClass;
    }

    public function setControllerClass(?string $controllerClass): void
    {
        $this->controllerClass = $controllerClass;
    }

    public function getAction():?string
    {
        return $this->action;
    }

    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client): void
    {
        $this->client = $client;
    }

}