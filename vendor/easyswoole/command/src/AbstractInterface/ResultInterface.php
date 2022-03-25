<?php


namespace EasySwoole\Command\AbstractInterface;

/**
 * 命令执行结果封装类接口
 */
interface ResultInterface
{
    function getResult();
    function setResult($result);
    function setMsg(?string $msg);
    function getMsg():?string ;
}