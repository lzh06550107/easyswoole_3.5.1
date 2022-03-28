<?php


namespace EasySwoole\Socket\Tools;

use Swoole\Coroutine\Socket;

/**
 * 协议类
 */
class Protocol
{
    const SETTING = [
        'open_length_check' => true,
        'package_length_type'   => 'N',
        'package_length_offset' => 0,
        'package_body_offset'   => 4,
    ];

    public static function pack(string $data): string
    {
        return pack('N', strlen($data)).$data;
    }

    public static function packDataLength(string $head): int
    {
        return unpack('N', $head)[1];
    }

    public static function unpack(string $data):string
    {
        return substr($data,4);
    }

    /**
     * 通过socket客户端来读取数据
     * @param Socket $socket
     * @param float $timeout
     * @return string|null
     */
    public static function socketReader(Socket $socket,float $timeout = 3.0):?string
    {
        $ret = null;
        $header = $socket->recvAll(4,$timeout); // 挂起当前协程，等读取到数据，则唤醒该协程
        if(strlen($header) == 4){
            $allLength = self::packDataLength($header); // 获取数据包长度
            $data = $socket->recvAll($allLength,$timeout); // 读取指定长度的数据
            if(strlen($data) == $allLength){
                $ret = $data;
            }
        }
        return $ret;
    }

    public static function socketWriter(Socket $socket,string $rawData,float $timeout = 3.0)
    {
        return $socket->sendAll(self::pack($rawData),$timeout); // 直到数据发送完成或遇到错误，唤醒对应协程
    }
}