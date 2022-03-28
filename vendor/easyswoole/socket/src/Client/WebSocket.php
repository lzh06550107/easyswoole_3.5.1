<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/25
 * Time: 下午3:23
 */

namespace EasySwoole\Socket\Client;

/**
 * 封装WebSocket客户端连接
 */
class WebSocket extends Tcp
{
    private $data; // 帧数据
    private $opCode; // 帧操作码
    private $isFinish; //

    function __construct(\Swoole\Websocket\Frame $frame = null)
    {
        if($frame){
            parent::__construct($frame->fd); // 客户端连接的唯一标识socket id，使用$server->push推送数据时需要用到
            $this->data = $frame->data; // 数据内容，可以是文本内容也可以是二进制数据，可以通过opcode的值来判断
            $this->opCode = $frame->opcode; // WebSocket的OpCode类型，可以参考WebSocket协议标准文档
            $this->isFinish = $frame->finish; // 表示数据帧是否完整，一个WebSocket请求可能会分成多个数据帧进行发送（底层已经实现了自动合并数据帧，现在不用担心接收到的数据帧不完整）
        }
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getOpCode()
    {
        return $this->opCode;
    }

    /**
     * @param mixed $opCode
     */
    public function setOpCode($opCode)
    {
        $this->opCode = $opCode;
    }

    /**
     * @return mixed
     */
    public function getisFinish()
    {
        return $this->isFinish;
    }

    /**
     * @param mixed $isFinish
     */
    public function setIsFinish($isFinish)
    {
        $this->isFinish = $isFinish;
    }
}