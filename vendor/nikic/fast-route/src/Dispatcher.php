<?php

namespace FastRoute;

interface Dispatcher
{
    const NOT_FOUND = 0;
    const FOUND = 1;
    const METHOD_NOT_ALLOWED = 2;

    /**
     * Dispatches against the provided HTTP method verb and URI.根据请求URI和方法动作来调度
     *
     * Returns array with one of the following formats:
     *
     *     [self::NOT_FOUND]
     *     [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
     *     [self::FOUND, $handler, ['varName' => 'value', ...]] // 路由参数
     *
     * @param string $httpMethod
     * @param string $uri
     *
     * @return array
     */
    public function dispatch($httpMethod, $uri);
}
