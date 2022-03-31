<?php

namespace FastRoute;

/**
 * 动态路由对象封装
 */
class Route
{
    /** @var string */
    public $httpMethod; // 请求方法

    /** @var string */
    public $regex; // 正在表达式

    /** @var array */
    public $variables; // 路由参数

    /** @var mixed */
    public $handler; // 路由处理器

    /**
     * Constructs a route (value object). 创建一个路由对象
     *
     * @param string $httpMethod
     * @param mixed  $handler
     * @param string $regex
     * @param array  $variables
     */
    public function __construct($httpMethod, $handler, $regex, $variables)
    {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
    }

    /**
     * Tests whether this route matches the given string.测试当前路由是否匹配给定的字符串
     *
     * @param string $str
     *
     * @return bool
     */
    public function matches($str)
    {
        $regex = '~^' . $this->regex . '$~';
        return (bool) preg_match($regex, $str);
    }
}
