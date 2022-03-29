<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午2:58
 */

namespace EasySwoole\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * 消息包实现类
 */
class Message implements MessageInterface
{
    private $protocolVersion = '1.1'; // 协议版本
    private $headers = []; // 请求头
    private $body; // 请求主体

    function __construct(array $headers = null,Stream $body = null,$protocolVersion = '1.1')
    {
        if($headers != null){
            $this->headers = $headers;
        }
        if($body != null){
            $this->body = $body;
        }
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * 获取协议版本
     * @return mixed|string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * 设置协议版本
     * @param $version
     * @return $this|Message
     */
    public function withProtocolVersion($version)
    {
        if($this->protocolVersion === $version){
            return $this;
        }
        $this->protocolVersion = $version;
        return $this;
    }

    /**
     * 获取请求头
     * @return array|\string[][]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 判断指定的请求头是否存在
     * @param $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return array_key_exists($name,$this->headers);
    }

    /**
     * 获取指定名称的请求头
     * @param $name
     * @return array|mixed|string[]
     */
    public function getHeader($name)
    {
        if(array_key_exists($name,$this->headers)){
            return $this->headers[$name];
        }else{
            return array();
        }
    }

    /**
     * 获取指定名称的请求头，多个值以分号连接
     * @param $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        if(array_key_exists($name,$this->headers)){
            return implode("; ",$this->headers[$name]);
        }else{
            return '';
        }
    }

    /**
     * 设置请求头
     * @param $name
     * @param $value
     * @return $this|Message
     */
    public function withHeader($name, $value)
    {
        if(!is_array($value)){
            $value = [$value];
        }
        if(isset($this->headers[$name]) &&  $this->headers[$name] === $value){
            return $this;
        }
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * 追加方式设置请求头
     * @param $name
     * @param $value
     * @return $this|Message
     */
    public function withAddedHeader($name, $value)
    {
        if(!is_array($value)){
            $value = [$value];
        }
        if(isset($this->headers[$name])){
            $this->headers[$name] =  array_merge($this->headers[$name], $value);
        }else{
            $this->headers[$name] = $value;
        }
        return $this;
    }

    /**
     * 删除指定名称的请求头
     * @param $name
     * @return $this|Message
     */
    public function withoutHeader($name)
    {
        if(isset($this->headers[$name])){
            unset($this->headers[$name]);
            return $this;
        }else{
            return $this;
        }
    }

    /**
     * 获取请求体的流
     * @return Stream|StreamInterface
     */
    public function getBody()
    {
        if($this->body == null){
            $this->body = new Stream(''); // 实例流
        }
        return $this->body;
    }

    /**
     * 设置请求体的流
     * @param StreamInterface $body
     * @return $this|Message
     */
    public function withBody(StreamInterface $body)
    {
        $this->body = $body;
        return $this;
    }
}