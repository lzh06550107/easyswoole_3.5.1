<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/25
 * Time: 下午4:12
 */

namespace EasySwoole\Socket\AbstractInterface;

use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

/**
 * 解析器接口
 */
interface ParserInterface
{
    /**
     * 解码
     * @param $raw
     * @param $client
     * @return Caller|null
     */
    public function decode($raw,$client):?Caller;

    /**
     * 编码
     * @param Response $response
     * @param $client
     * @return string|null
     */
    public function encode(Response $response,$client):?string ;
}