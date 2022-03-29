<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午3:19
 */

namespace EasySwoole\Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * 客户端请求类实现
 */
class Request extends Message implements RequestInterface
{
    private $uri; // Uri类对象
    private $method; // 请求方法
    private $target; // 请求相对路径

    function __construct($method = 'GET', Uri $uri = null, array $headers = null, Stream $body = null, $protocolVersion = '1.1')
    {
        $this->method = $method;
        if ($uri != null) {
            $this->uri = $uri;
        }else{
            $this->uri = new Uri();
        }
        parent::__construct($headers, $body, $protocolVersion);
    }

    /**
     * 根据uri来拼装目标路径
     * @return mixed|string
     */
    public function getRequestTarget()
    {
        if (!empty($this->target)) {
            return $this->target;
        }
        if ($this->uri instanceof Uri) {
            $target = $this->uri->getPath();
            if ($target == '') {
                $target = '/';
            }
            if ($this->uri->getQuery() != '') {
                $target .= '?' . $this->uri->getQuery();
            }
        } else {
            $target = "/";
        }
        return $target;
    }

    /**
     * 设置目标路径
     * @param $requestTarget
     * @return $this|Request
     */
    public function withRequestTarget($requestTarget)
    {
        $this->target = $requestTarget;
        return $this;
    }

    /**
     * 获取请求方法
     * @return mixed|string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 设置请求方法
     * @param $method
     * @return $this|Request
     */
    public function withMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * 获取uri
     * @return Uri|UriInterface
     */
    public function getUri()
    {
        if($this->uri == null){
            $this->uri = new Uri();
        }
        return $this->uri;
    }

    /**
     * 
     * @param UriInterface $uri
     * @param $preserveHost
     * @return $this|Request
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }
        $this->uri = $uri;
        if (!$preserveHost) { // 不保留主机
            $host = $this->uri->getHost();
            if (!empty($host)) { // 
                if (($port = $this->uri->getPort()) !== null) {
                    $host .= ':' . $port;
                }
                if ($this->getHeader('host')) {
                    $header = $this->getHeader('host');
                } else {
                    $header = 'Host';
                }
                $this->withHeader($header, $host);
            }
        }
        return $this;
    }
}