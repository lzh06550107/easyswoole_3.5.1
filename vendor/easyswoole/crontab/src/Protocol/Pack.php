<?php


namespace EasySwoole\Crontab\Protocol;

/**
 * 封包和解包帮助类
 */
class Pack
{
    public static function pack(string $data): string
    {
        return pack('N', strlen($data)) . $data;
    }

    public static function packDataLength(string $head): int
    {
        return unpack('N', $head)[1];
    }

    public static function unpack(string $data): string
    {
        return substr($data, 4);
    }
}