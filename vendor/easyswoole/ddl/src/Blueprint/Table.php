<?php

namespace EasySwoole\DDL\Blueprint;

use EasySwoole\DDL\Blueprint\Create\Table as CreateTable;

/**
 * 创建表结构描述
 * 暂只支持创建表 CREATE 结构
 * Class Table
 * @package EasySwoole\DDL\Blueprint
 */
class Table extends CreateTable
{
    /**
     * 创建大整数值列，4字节
     * @param string $name 列名称
     * @param int|null $limit 列宽度限制
     * @return Create\Column|mixed
     */
    public function colInt(string $name, int $limit = null)
    {
        return $this->int($name, $limit);
    }

    /**
     * 创建极大整数值列，8字节
     * @param string $name 列名称
     * @param int|null $limit 列宽度限制
     * @return Create\Column
     */
    public function colBigInt(string $name, int $limit = null)
    {
        return $this->bigint($name, $limit);
    }

    /**
     * 创建超小整数值列，1字节
     * @param string $name 列名称
     * @param int|null $limit 列宽度限制
     * @return Create\Column
     */
    public function colTinyInt(string $name, int $limit = null)
    {
        return $this->tinyint($name, $limit);
    }

    /**
     * 创建超小整数值列，2字节
     * @param string $name 列名称
     * @param int|null $limit 列宽度限制
     * @return Create\Column
     */
    public function colSmallInt(string $name, int $limit = null)
    {
        return $this->smallint($name, $limit);
    }

    /**
     * 创建中等大小整数，3字节
     * @param string $name 列名称
     * @param int|null $limit 列宽度限制
     * @return Create\Column
     */
    public function colMediumInt(string $name, int $limit = null)
    {
        return $this->mediumInt($name, $limit);
    }

    /**
     * 创建单精度浮点数组列，4字节
     * @param string $name 列名称
     * @param int|null $precision 字段总精度(允许储存多少位数|含小数点)
     * @param int|null $digits 小数点部分的精度(可空)
     * @return Create\Column
     */
    public function colFloat(string $name, int $precision = null, int $digits = null)
    {
        return $this->float($name, $precision, $digits);
    }

    /**
     * 创建双精度浮点数组列，8字节
     * @param string $name 列名称
     * @param int|null $precision 字段总精度(允许储存多少位数|含小数点)
     * @param int|null $digits 小数点部分的精度(可空)
     * @return Create\Column
     */
    public function colDouble(string $name, int $precision = null, int $digits = null)
    {
        return $this->double($name, $precision, $digits);
    }

    /**
     * 创建小数值列，对 DECIMAL(M,D) ，如果M>D，为M+2否则为D+2
     * @param string $name 列名称
     * @param int $precision 字段总精度(允许储存多少位数|含小数点)
     * @param int $digits 小数点部分的精度
     * @return Create\Column
     */
    public function colDecimal(string $name, int $precision = 10, int $digits = 0)
    {
        return $this->decimal($name, $precision, $digits);
    }

    /**
     * 创建日期值列，YYYY-MM-DD
     * @param string $name 列名称
     * @return Create\Column
     */
    public function colDate(string $name)
    {
        return $this->date($name);
    }

    /**
     * 创建年分值列，YYYY
     * @param string $name 列名称
     * @return Create\Column
     */
    public function colYear(string $name)
    {
        return $this->year($name);
    }

    /**
     * 创建时间值列，HH:MM:SS
     * @param string $name 列名称
     * @param int|null $fsp
     * @return Create\Column
     */
    public function colTime(string $name, ?int $fsp = null)
    {
        return $this->time($name, $fsp);
    }

    /**
     * 创建日期时间列，YYYY-MM-DD HH:MM:SS
     * @param string $name 列名称
     * @param int|null $fsp
     * @return Create\Column
     */
    public function colDateTime(string $name, ?int $fsp = null)
    {
        return $this->datetime($name, $fsp);
    }

    /**
     * 创建时间戳列，YYYYMMDD HHMMSS
     * @param string $name 列名称
     * @param int|null $fsp
     * @return Create\Column
     */
    public function colTimestamp(string $name, ?int $fsp = null)
    {
        return $this->timestamp($name, $fsp);
    }

    /**
     * 创建定长字符串列，定长字符串，0-255字节
     * @param string $name 列名称
     * @param int|null $limit 字符的个数，编码决定了每个字符占用的字节个数
     * @return Create\Column
     */
    public function colChar(string $name, ?int $limit = null)
    {
        return $this->char($name, $limit);
    }

    /**
     * 创建变长字符串列，变长字符串，0-65535字节
     * @param string $name 列名称
     * @param int|null $limit 字符的个数，编码决定了每个字符占用的字节个数
     * @return Create\Column
     */
    public function colVarChar(string $name, ?int $limit = null)
    {
        return $this->varchar($name, $limit);
    }

    /**
     * 创建长文本数据列，0-65535字节
     * @param string $name 列名称
     * @return Create\Column
     */
    public function colText(string $name)
    {
        return $this->text($name);
    }

    /**
     * 创建短文本数据列，0-255字节
     * @param string $name 列名称
     * @return Create\Column
     */
    public function colTinyText(string $name)
    {
        return $this->tinytext($name);
    }

    /**
     * 创建极大文本数据列，0-4294967295字节
     * @param string $name 列名称
     * @return Create\Column
     */
    public function colLongText(string $name)
    {
        return $this->longtext($name);
    }

    /**
     * 创建中等长度文本数据列，0-16777215字节
     * @param string $name 列名称
     * @return Create\Column
     */
    public function colMediumText(string $name)
    {
        return $this->mediumtext($name);
    }

    /**
     * 创建二进制形式的长文本数据列，0-65535字节
     * @param string $name 列名称
     * @return Create\Column
     */
    public function colBlob(string $name)
    {
        return $this->blob($name);
    }

    /**
     * 创建二进制形式的极大文本数据列，0-4294967295字节
     * @param string $name 列名称
     * @return Create\Column
     */
    public function colLongBlob(string $name)
    {
        return $this->longblob($name);
    }

    /**
     * 创建不超过 255 个字符的二进制字符串列，0-255字节
     * @param string $name 列名称
     * @return Create\Column|mixed
     */
    public function colTinyBlob(string $name)
    {
        return $this->tinyblob($name);
    }

    /**
     * 创建二进制形式的中等长度文本数据列，0-16777215字节
     * @param string $name 列名称
     * @return Create\Column|mixed
     */
    public function colMediumBlob(string $name)
    {
        return $this->mediumblob($name);
    }

    /**
     * 为指定的列创建普通索引
     * @param string $name 索引名称
     * @param $columns 列名称，字符串或数组
     * @return Create\Index
     */
    public function indexNormal(string $name, $columns)
    {
        return $this->normal($name, $columns);
    }

    /**
     * 为指定的列创建唯一索引
     * @param string $name 索引名称
     * @param $columns 列名称，字符串或数组
     * @return Create\Index
     */
    public function indexUnique(string $name, $columns)
    {
        return $this->unique($name, $columns);
    }

    /**
     * 为指定的列创建主键索引
     * @param string $name 索引名称
     * @param $columns 列名称，字符串或数组
     * @return Create\Index
     */
    public function indexPrimary(string $name, $columns)
    {
        return $this->primary($name, $columns);
    }

    /**
     * 为指定的列创建全文索引
     * @param string $name 索引名称
     * @param $columns 列名称，字符串或数组
     * @return Create\Index
     */
    public function indexFullText(string $name, $columns)
    {
        return $this->fulltext($name, $columns);
    }
}