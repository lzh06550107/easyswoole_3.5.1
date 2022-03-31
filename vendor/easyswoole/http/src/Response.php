<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午3:43
 */

namespace EasySwoole\Http;

use EasySwoole\Http\Message\Response as MessageResponse;
use EasySwoole\Http\Message\Status;

/**
 * 适配器模式，swoole响应适配标准响应类
 */
class Response extends MessageResponse
{
    private $response; // swoole响应对象
    const STATUS_NOT_END = 0;
    const STATUS_LOGICAL_END = 1;
    const STATUS_REAL_END = 2;
    const STATUS_RESPONSE_DETACH = 3;

    private $sendFile = null;
    private $isEndResponse = self::STATUS_NOT_END;//1 逻辑end  2真实end 3分离响应
    private $isChunk = false;

    final public function __construct(\Swoole\Http\Response $response = null)
    {
        $this->response = $response;
        parent::__construct();
        $this->withAddedHeader('Server','EasySwoole');
    }

    /**
     * 逻辑结束
     */
    function end(){
        $this->isEndResponse = self::STATUS_LOGICAL_END;
    }

    function __response():bool
    {
        $ret = false;
        if($this->isEndResponse <= self::STATUS_REAL_END){
            $this->isEndResponse = self::STATUS_REAL_END; // 表示已经发送，不能再次写入
            //结束处理
            $status = $this->getStatusCode();
            $this->response->status($status);
            $headers = $this->getHeaders();
            foreach ($headers as $header => $val){
                foreach ($val as $sub){
                    $this->response->header($header,$sub);
                }
            }
            $cookies = $this->getCookies();
            foreach ($cookies as $cookie){
                $this->response->cookie(...$cookie);
            }
            $write = $this->getBody()->__toString();
            if($write !== '' && $this->isChunk){
                $this->response->write($write);
                $write = null;
            }

            if($this->sendFile != null){
                // 发送文件到浏览器
                $this->response->sendfile($this->sendFile); // 调用 sendfile 前不得使用 write 方法发送 Http-Chunk;调用 sendfile 后底层会自动执行 end
            }else{
                $this->response->end($write);
            }
            return true;
        }

        $this->response = null;
        return $ret;
    }

    /**
     * 判断是否结束
     * @return int
     */
    function isEndResponse()
    {
        return $this->isEndResponse;
    }

    function write(string $str){
        if(!$this->isEndResponse()){
            if ($this->isChunk){ // 启用 Http Chunk 分段向浏览器发送相应内容
                $this->getSwooleResponse()->write($str);
            }else{
                $this->getBody()->write($str);
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * 重定向
     * @param $url
     * @param $status
     * @return bool
     */
    function redirect($url,$status = Status::CODE_MOVED_TEMPORARILY):bool
    {
        if(!$this->isEndResponse()){
            //仅支持header重定向  不做meta定向
            $this->withStatus($status);
            $this->withHeader('Location',$url);
            return true;
        }else{
            return false;
        }
    }

    /*
     * 目前swoole不支持同键名的header   因此只能通过别的方式设置多个cookie
     */
    public function setCookie(string $name, $value = null, $expire = null,string $path = '/',string $domain = '',bool $secure = false,bool $httponly = false,string $samesite = ''){
        if(!$this->isEndResponse()){
            $this->withAddedCookie([
                $name,$value,$expire,$path,$domain,$secure,$httponly,$samesite
            ]);
            return true;
        }else{
            return false;
        }

    }

    function getSwooleResponse()
    {
        return $this->response;
    }

    /**
     * 响应文件
     * @param string $sendFilePath
     */
    function sendFile(string $sendFilePath)
    {
        $this->sendFile = $sendFilePath;
    }

    /**
     * 分离响应对象
     * @return int|null
     */
    public function detach():?int
    {
        $fd = $this->response->fd;
        $this->isEndResponse = self::STATUS_RESPONSE_DETACH;
        $this->response->detach(); // 分离响应对象。使用此方法后，$response 对象销毁时不会自动 end，与 Http\Response::create 和 Server->send 配合使用
        return $fd;
    }

    /**
     * 是否分块响应
     * @param bool $isChunk
     */
    public function setIsChunk(bool $isChunk): void
    {
        $this->isChunk = $isChunk;
    }

    /**
     * 分离后，可根据id重新创建Response对象
     * @param int $fd
     * @return Response
     */
    static function createFromFd(int $fd):Response
    {
        $resp = \Swoole\Http\Response::create($fd);
        return new Response($resp);
    }

    /**
     * 以字符串形式输出响应对象
     * @return string
     */
    final public function __toString():string
    {
        // TODO: Implement __toString() method.
        return Utility::toString($this);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->getBody()->close();
    }
}
