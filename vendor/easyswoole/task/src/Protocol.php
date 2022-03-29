<?php

namespace EasySwoole\Task;

/**
 * 通讯协议
 */
class Protocol
{
    /**
     * 封包
     * @param string $data
     * @return string
     */
    public static function pack(string $data): string
    {
        return pack('N', strlen($data)) . $data;
    }

    /**
     * 解析包的长度字段
     * @param string $head
     * @return int
     */
    public static function packDataLength(string $head): int
    {
        return unpack('N', $head)[1];
    }

    /**
     * 解包
     * @param string $data
     * @return string
     */
    public static function unpack(string $data): string
    {
        return substr($data, 4);
    }
}