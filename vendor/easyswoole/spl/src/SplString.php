<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/22
 * Time: 下午2:55
 */

namespace EasySwoole\Spl;

/**
 * 用于处理字符串
 */
class SplString extends SplStream
{

    function __construct( string $str = null )
    {
        parent::__construct( $str );
    }

    /**
     * 向流写入字符串
     * @param string $string
     * @return $this
     */
    function setString( string $string ) : SplString
    {
        parent::truncate(); // 清空流
        parent::rewind(); // 重置流指针到流开头
        parent::write( $string ); // 向流写入字符串
        return $this;
    }

    /**
     * 按指定的长度切分字符串
     * @param int $length
     * @return SplArray
     */
    function split( int $length = 1 ) : SplArray
    {
        return new SplArray( str_split( $this->__toString(), $length ) );
    }

    /**
     * 把流中字符串按分隔符切分并保存到数组对象中
     * @param string $delimiter
     * @return SplArray
     */
    function explode( string $delimiter ) : SplArray
    {
        return new SplArray( explode( $delimiter, $this->__toString() ) );
    }

    /**
     * 截取从指定位置开始指定长度的字符串
     * @param int $start
     * @param int $length
     * @return $this
     */
    function subString( int $start, int $length ) : SplString
    {
        return $this->setString( substr( $this->__toString(), $start, $length ) );
    }

    /**
     * 对文本的编码类型进行转换
     * @param string $desEncoding
     * @param $detectList
     * @return $this
     */
    function encodingConvert( string $desEncoding, $detectList
    = [
        'UTF-8',
        'ASCII',
        'GBK',
        'GB2312',
        'LATIN1',
        'BIG5',
        "UCS-2",
    ] ) : SplString
    {
        $fileType = mb_detect_encoding( $this->__toString(), $detectList ); // 检测字符编码类型
        if( $fileType != $desEncoding ){
            $this->setString( mb_convert_encoding( $this->__toString(), $desEncoding, $fileType ) );
        }
        return $this;
    }

    /**
     * 字符转换的便捷方法，字符串编码转换为utf-8
     * @return $this
     */
    function utf8() : SplString
    {
        return $this->encodingConvert( "UTF-8" );
    }

    /*
     * 字符转换的便捷方法，unicode字符串编码转换为utf-8
     * special function for unicode
     */
    function unicodeToUtf8() : SplString
    {

        $string = preg_replace_callback( '/\\\\u([0-9a-f]{4})/i', function( $matches ){
            return mb_convert_encoding( pack( "H*", $matches[1] ), "UTF-8", "UCS-2BE" );
        }, $this->__toString() );
        return $this->setString( $string );
    }

    /**
     * 字符转换的便捷方法，字符串编码转换为unicode
     * @return $this
     */
    function toUnicode() : SplString
    {
        $raw = (string)$this->encodingConvert( "UCS-2" );
        $len = strlen( $raw );
        $str = '';
        for( $i = 0 ; $i < $len - 1 ; $i = $i + 2 ){
            $c  = $raw[$i];
            $c2 = $raw[$i + 1];
            if( ord( $c ) > 0 ){   //两个字节的文字
                $str .= '\u'.base_convert( ord( $c ), 10, 16 ).str_pad( base_convert( ord( $c2 ), 10, 16 ), 2, 0, STR_PAD_LEFT );
            } else{
                $str .= '\u'.str_pad( base_convert( ord( $c2 ), 10, 16 ), 4, 0, STR_PAD_LEFT );
            }
        }
        $string = strtoupper( $str );//转换为大写
        return $this->setString( $string );
    }

    /**
     * 字符串比较
     * @param string $str
     * @param int $ignoreCase
     * @return int
     */
    function compare( string $str, int $ignoreCase = 0 ) : int
    {
        if( $ignoreCase ){
            return strcasecmp( $this->__toString(), $str );
        } else{
            return strcmp( $this->__toString(), $str );
        }
    }

    /**
     * 清空字符串左边的指定字符串
     * @param string $charList
     * @return $this
     */
    function lTrim( string $charList = " \t\n\r\0\x0B" ) : SplString
    {
        return $this->setString( ltrim( $this->__toString(), $charList ) );
    }

    /**
     * 清空字符串右边的指定字符串
     * @param string $charList
     * @return $this
     */
    function rTrim( string $charList = " \t\n\r\0\x0B" ) : SplString
    {
        return $this->setString( rtrim( $this->__toString(), $charList ) );
    }

    /**
     * 清空掉字符串左右两边的指定字符串
     * @param string $charList
     * @return $this
     */
    function trim( string $charList = " \t\n\r\0\x0B" ) : SplString
    {
        return $this->setString( trim( $this->__toString(), $charList ) );
    }

    /**
     * 使用指定的字符串长度来填充流中字符串
     * @param int $length
     * @param string|null $padString
     * @param int $pad_type
     * @return $this
     */
    function pad( int $length, string $padString = null, int $pad_type = STR_PAD_RIGHT ) : SplString
    {
        return $this->setString( str_pad( $this->__toString(), $length, $padString, $pad_type ) );
    }

    /**
     * 重复一个字符串
     * @param int $times
     * @return $this
     */
    function repeat( int $times ) : SplString
    {
        return $this->setString( str_repeat( $this->__toString(), $times ) );
    }

    /**
     * 获取字符串长度
     * @return int
     */
    function length() : int
    {
        return strlen( $this->__toString() );
    }

    /**
     * 将字符串转化为大写
     * @return $this
     */
    function upper() : SplString
    {
        return $this->setString( strtoupper( $this->__toString() ) );
    }

    /**
     * 将字符串转化为小写
     * @return $this
     */
    function lower() : SplString
    {
        return $this->setString( strtolower( $this->__toString() ) );
    }

    /**
     * 从字符串中去除 HTML 和 PHP 标记1
     * @param string|null $allowable_tags
     * @return $this
     */
    function stripTags( string $allowable_tags = null ) : SplString
    {
        return $this->setString( strip_tags( $this->__toString(), $allowable_tags ) );
    }

    /**
     * 子字符串替换
     * @param string $find
     * @param string $replaceTo
     * @return $this
     */
    function replace( string $find, string $replaceTo ) : SplString
    {
        return $this->setString( str_replace( $find, $replaceTo, $this->__toString() ) );
    }

    /**
     * 获取指定目标的中间字符串
     * @param string $startStr
     * @param string $endStr
     * @return $this
     */
    function between( string $startStr, string $endStr ) : SplString
    {
        $explode_arr = explode( $startStr, $this->__toString() );
        if( isset( $explode_arr[1] ) ){
            $explode_arr = explode( $endStr, $explode_arr[1] );
            return $this->setString( $explode_arr[0] );
        } else{
            return $this->setString( '' );
        }
    }

    /**
     * 按照正则规则查找字符串
     * @param $regex
     * @param bool $rawReturn
     * @return mixed|null
     */
    public function regex( $regex, bool $rawReturn = false )
    {
        preg_match( $regex, $this->__toString(), $result );
        if( !empty( $result ) ){
            if( $rawReturn ){
                return $result;
            } else{
                return $result[0];
            }
        } else{
            return null;
        }
    }

    /**
     * 是否存在指定字符串
     * @param string $find
     * @param bool $ignoreCase
     * @return bool
     */
    public function exist( string $find, bool $ignoreCase = true ) : bool
    {
        if( $ignoreCase ){
            $label = stripos( $this->__toString(), $find );
        } else{
            $label = strpos( $this->__toString(), $find );
        }
        return $label === false ? false : true;
    }

    /**
     * 转换为烤串
     */
    function kebab() : SplString
    {
        return $this->snake( '-' );
    }

    /**
     * 转为蛇的样子
     * @param  string $delimiter
     */
    function snake( string $delimiter = '_' ) : SplString
    {
        $string = $this->__toString();
        if( !ctype_lower( $string ) ){
            $string = preg_replace( '/\s+/u', '', ucwords( $this->__toString() ) );
            $string = $this->setString( preg_replace( '/(.)(?=[A-Z])/u', '$1'.$delimiter, $string ) );
            $this->setString( $string );
            $this->lower();
        }
        return $this;
    }


    /**
     * 转为大写的大写
     */
    function studly() : SplString
    {
        $value = ucwords( str_replace( ['-', '_'], ' ', $this->__toString() ) );
        return $this->setString( str_replace( ' ', '', $value ) );
    }

    /**
     * 驼峰
     *
     */
    function camel() : SplString
    {
        $this->studly();
        return $this->setString( lcfirst( $this->__toString() ) );
    }


    /**
     * 用数组依次替换字符串
     * @param  string $search
     * @param  array  $replace
     */
    public function replaceArray( string $search, array $replace ) : SplString
    {
        foreach( $replace as $value ){
            $this->setString( $this->replaceFirst( $search, $value ) );
        }
        return $this;
    }

    /**
     * 替换字符串中给定值的第一次出现。
     * @param  string $search
     * @param  string $replace
     */
    public function replaceFirst( string $search, string $replace ) : SplString
    {
        if( $search == '' ){
            return $this;
        }

        $position = strpos( $this->__toString(), $search );

        if( $position !== false ){
            return $this->setString( substr_replace( $this->__toString(), $replace, $position, strlen( $search ) ) );
        }

        return $this;
    }

    /**
     * 替换字符串中给定值的最后一次出现。
     * @param  string $search
     * @param  string $replace
     */
    public function replaceLast( string $search, string $replace ) : SplString
    {
        $position = strrpos( $this->__toString(), $search );

        if( $position !== false ){
            return $this->setString( substr_replace( $this->__toString(), $replace, $position, strlen( $search ) ) );
        }

        return $this;
    }

    /**
     * 以一个给定值的单一实例开始一个字符串
     *
     * @param  string $prefix
     */
    public function start( string $prefix ) : SplString
    {
        // 向其中 每个正则表达式语法中的字符前增加一个反斜线。 这通常用于你有一些运行时字符串 需要作为正则表达式进行匹配的时候
        $quoted = preg_quote( $prefix, '/' );
        // 替换字符串流中的所有该字符串，然后再前面追加
        return $this->setString( $prefix.preg_replace( '/^(?:'.$quoted.')+/u', '', $this->__toString() ) );
    }

    /**
     * 在给定的值之后返回字符串的其余部分。
     *
     * @param  string $search
     */
    function after( string $search ) : SplString
    {
        if( $search === '' ){
            return $this;
        } else{
            return $this->setString( array_reverse( explode( $search, $this->__toString(), 2 ) )[0] );
        }
    }

    /**
     * 在给定的值之前获取字符串的一部分
     *
     * @param  string $search
     */
    function before( string $search ) : SplString
    {
        if( $search === '' ){
            return $this;
        } else{
            return $this->setString( explode( $search, $this->__toString() )[0] );
        }
    }

    /**
     * 确定给定的字符串是否以给定的子字符串结束
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    public function endsWith( $needles ) : bool
    {
        foreach( (array)$needles as $needle ){
            if( substr( $this->__toString(), - strlen( $needle ) ) === (string)$needle ){
                return true;
            }
        }
        return false;
    }

    /**
     * 确定给定的字符串是否从给定的子字符串开始
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    public function startsWith( $needles ) : bool
    {
        foreach( (array)$needles as $needle ){
            if( $needle !== '' && substr( $this->__toString(), 0, strlen( $needle ) ) === (string)$needle ){
                return true;
            }
        }
        return false;
    }
}