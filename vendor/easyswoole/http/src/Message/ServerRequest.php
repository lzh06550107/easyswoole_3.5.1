<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午3:20
 */

namespace EasySwoole\Http\Message;

use Psr\Http\Message\ServerRequestInterface;

/**
 * 服务器端接收的请求类实现
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    private $attributes = []; // 请求体中属性值
    private $cookieParams = []; // cookie
    private $parsedBody = []; // 解析的请求体
    private $queryParams = []; // 查询参数
    private $serverParams; // 服务器端环境参数
    private $uploadedFiles = []; // 上传文件数组

    function __construct($method = 'GET', Uri $uri = null, array $headers = null, Stream $body = null, $protocolVersion = '1.1',$serverParams = array())
    {
        $this->serverParams = $serverParams;
        parent::__construct($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * 获取服务器端环境参数
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * 获取指定名称的cookie值
     * @param $name
     * @return array|mixed|null
     */
    public function getCookieParams($name = null)
    {
        if($name === null){
            return $this->cookieParams;
        }else{
            if(isset($this->cookieParams[$name])){
                return $this->cookieParams[$name];
            }else{
                return null;
            }
        }

    }

    /**
     * 设置cookie
     * @param array $cookies
     * @return $this|ServerRequest
     */
    public function withCookieParams(array $cookies)
    {
        $this->cookieParams = $cookies;
        return $this;
    }

    /**
     * 获取查询参数数组
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * 获取指定名称的查询参数
     * @param $name
     * @return mixed|null
     */
    public function getQueryParam($name){
        $data = $this->getQueryParams();
        if(isset($data[$name])){
            return $data[$name];
        }else{
            return null;
        }
    }

    /**
     * 设置查询参数数组
     * @param array $query
     * @return $this|ServerRequest
     */
    public function withQueryParams(array $query)
    {
        $this->queryParams = $query;
        return $this;
    }

    /**
     * 获取上传文件数组
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /*
     * 适配二维数组方式上传
     */
    public function getUploadedFile($name)
    {
        if(isset($this->uploadedFiles[$name])){
            return $this->uploadedFiles[$name];
        }else{
            return null;
        }
    }

    /**
     * @param array $uploadedFiles must be array of UploadFile Instance
     * @return ServerRequest
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->uploadedFiles = $uploadedFiles;
        return $this;
    }

    /**
     * 获取请求体中指定名称的值
     * @param $name
     * @return array|mixed|object|null
     */
    public function getParsedBody($name = null)
    {
        if($name !== null){
            if(isset($this->parsedBody[$name])){
                return $this->parsedBody[$name];
            }else{
                return null;
            }
        }else{
            return $this->parsedBody;
        }
    }

    public function withParsedBody($data)
    {
        $this->parsedBody = $data;
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $default;
        }
        return $this->attributes[$name];
    }

    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function withoutAttribute($name)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }
        unset($this->attributes[$name]);
        return $this;
    }
}