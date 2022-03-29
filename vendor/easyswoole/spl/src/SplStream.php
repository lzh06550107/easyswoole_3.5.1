<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/22
 * Time: 下午2:54
 */

namespace EasySwoole\Spl;

/**
 * 资源流数据操作
 */
class SplStream
{
    private $stream;
    private $seekable;
    private $readable;
    private $writable;

    private $readList = [
        'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
        'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
        'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a+' => true
    ];

    private $writeList = [
        'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
        'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
        'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
    ];

    /**
     * 初始化资源和读写操作
     * @param $resource
     * @param $mode
     */
    function __construct($resource = '',$mode = 'r+')
    {
        switch (gettype($resource)) {
            case 'resource': {
                $this->stream = $resource;
                break;
            }
            case 'object':{
                if(method_exists($resource, '__toString')) {
                    $resource = $resource->__toString();
                    $this->stream = fopen('php://memory', $mode); // 打开内存
                    if ($resource !== '') {
                        fwrite($this->stream, $resource); // 对象字符串写入内存中
                    }
                    break;
                }else{
                    throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
                }
            }
            default:{
                $this->stream  = fopen('php://memory', $mode);
                try{
                    $resource = (string)$resource; // 可以强制转换为字符串
                    if ($resource !== '') {
                        fwrite($this->stream, $resource);
                    }
                }catch (\Exception $exception){
                    throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
                }
            }
        }

        $info = stream_get_meta_data($this->stream); // 获取流的元数据
        $this->seekable = $info['seekable']; // 当前流是否可查找
        $this->seek(0); // 当前流指针位于开头
        $this->readable = isset($this->readList[$info['mode']]); // 当前流是否可读
        $this->writable =  isset($this->writeList[$info['mode']]); // 当前流是否可写
    }

    /**
     * 读取当前流所有内容为字符串
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
        try {
            $this->seek(0);
            return (string) stream_get_contents($this->stream);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 关闭一个打开的文件指针
     */
    public function close()
    {
        // TODO: Implement close() method.
        $res = $this->detach();
        if(is_resource($res)){
            fclose($res);
        }
    }

    /**
     * 获取资源并重置资源对象
     * @return false|mixed|resource|string|null
     */
    public function detach()
    {
        // TODO: Implement detach() method.
        if (!isset($this->stream)) {
            return null;
        }
        $this->readable = $this->writable = $this->seekable = false;
        $result = $this->stream;
        unset($this->stream);
        return $result;
    }

    /**
     * 获取流大小
     * @return mixed|null
     */
    public function getSize()
    {
        // TODO: Implement getSize() method.
        $stats = fstat($this->stream); // 获取流大小
        if (isset($stats['size'])) {
            return $stats['size'];
        }else{
            return null;
        }
    }

    /**
     * 获取读写指针的位置
     * @return int
     */
    public function tell()
    {
        // TODO: Implement tell() method.
        $result = ftell($this->stream);
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    /**
     * 判断是否到达流的末尾
     * @return bool
     */
    public function eof()
    {
        // TODO: Implement eof() method.
        return !$this->stream || feof($this->stream);
    }

    /**
     * 获取是否可以在当前流中定位
     * @return mixed
     */
    public function isSeekable()
    {
        // TODO: Implement isSeekable() method.
        return $this->seekable;
    }

    /**
     * 定位文件流指针到指定的位置
     * @param $offset
     * @param $whence
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        // TODO: Implement seek() method.
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        } elseif (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position '
                . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    /**
     * 倒回文件指针的位置
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
        $this->seek(0);
    }

    /**
     * 是否可写
     * @return bool
     */
    public function isWritable()
    {
        // TODO: Implement isWritable() method.
        return $this->writable;
    }

    /**
     * 写入内容
     * @param $string
     * @return int
     */
    public function write($string)
    {
        // TODO: Implement write() method.
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    /**
     * 是否可读
     * @return bool
     */
    public function isReadable()
    {
        // TODO: Implement isReadable() method.
        return $this->readable;
    }

    /**
     * 读取内容
     * @param $length
     * @return string
     */
    public function read($length)
    {
        // TODO: Implement read() method.
        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new \RuntimeException('Length parameter cannot be negative');
        }
        if (0 === $length) {
            return '';
        }
        $string = fread($this->stream, $length);
        if (false === $string) {
            throw new \RuntimeException('Unable to read from stream');
        }
        return $string;
    }

    /**
     * 读取剩余的资源流到一个字符串
     * @return string
     */
    public function getContents()
    {
        // TODO: Implement getContents() method.
        //注意与__toString的区别
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    /**
     * 读取流的元数据
     * @param $key
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        // TODO: Implement getMetadata() method.
        if (!isset($this->stream)) {
            return $key ? null : [];
        } elseif (!$key) {
            return stream_get_meta_data($this->stream);
        } else {
            $meta = stream_get_meta_data($this->stream);
            return isset($meta[$key]) ? $meta[$key] : null;
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }

    function getStreamResource(){
        return $this->stream;
    }

    /**
     * 把打开文件截断到指定的长度
     * @param $size
     * @return bool
     */
    function truncate($size = 0){
        return ftruncate($this->stream,$size);
    }
}