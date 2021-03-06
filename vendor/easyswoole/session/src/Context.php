<?php

namespace EasySwoole\Session;

/**
 * 会话上下文，用来保存从文件读取的会话内容
 */
class Context
{
    protected $data = [];

    function __construct(?array $data = null)
    {
        if($data == null){
            $data = [];
        }
        $this->data = $data;
    }

    function set(string $key,$data)
    {
        $this->data[$key] = $data;
    }

    function get(string $key)
    {
        if(isset($this->data[$key])){
            return $this->data[$key];
        }
        return null;
    }

    function del(string $key)
    {
        unset($this->data[$key]);
    }

    function flush()
    {
        $this->data = [];
    }

    function allContext():array
    {
        return $this->data;
    }

    function setData(array $data):Context
    {
        $this->data = $data;
        return $this;
    }
}