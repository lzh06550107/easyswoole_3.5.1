<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-06
 * Time: 22:58
 */

namespace EasySwoole\Component\Context;

use EasySwoole\Component\Context\Exception\ModifyError;
use EasySwoole\Component\Singleton;
use Swoole\Coroutine;

/**
 * 协程上下文管理器，在Swoole中，由于多个协程是并发执行的，因此不能使用类静态变量/全局变量保存协程上下文内容。使用局部变量是安全的，因为局部变量的值会自动保存在协程栈中，其它协程访问不到协程的局部变量
 */
class ContextManager
{
    use Singleton;

    private $contextHandler = [];

    private $context = [];

    private $deferList = [];

    // 注册某个项处理器
    public function registerItemHandler($key, ContextItemHandlerInterface $handler):ContextManager
    {
        $this->contextHandler[$key] = $handler;
        return $this;
    }

    public function set($key,$value,$cid = null):ContextManager
    {
        if(isset($this->contextHandler[$key])){
            throw new ModifyError('key is already been register for context item handler');
        }
        $cid = $this->getCid($cid);
        $this->context[$cid][$key] = $value; // 根据协程号和键来存在值
        return $this;
    }

    public function get($key,$cid = null)
    {
        $cid = $this->getCid($cid);
        if(isset($this->context[$cid][$key])){
            return $this->context[$cid][$key];
        }
        // 如果不存在该键值，则获取该键的处理器
        if(isset($this->contextHandler[$key])){
            /** @var ContextItemHandlerInterface $handler */
            $handler = $this->contextHandler[$key];
            $this->context[$cid][$key] = $handler->onContextCreate(); // 调用处理器来创建
            return $this->context[$cid][$key];
        }
        return null;
    }

    // 删除指定协程指定键的上下文信息
    public function unset($key,$cid = null)
    {
        $cid = $this->getCid($cid);
        if(isset($this->context[$cid][$key])){
            // 如果存在key处理器，则直接删除
            if(isset($this->contextHandler[$key])){
                /** @var ContextItemHandlerInterface $handler */
                $handler = $this->contextHandler[$key];
                $item = $this->context[$cid][$key];
                unset($this->context[$cid][$key]);
                return $handler->onDestroy($item); // 调用处理器来销毁
            }
            unset($this->context[$cid][$key]);
            return true;
        }else{
            return false;
        }
    }

    // 删除指定协程的所有上下文数据，需要单个删除，因为可能需要调用某些key的处理器销毁方法
    public function destroy($cid = null)
    {
        $cid = $this->getCid($cid); // 获取协程id
        if(isset($this->context[$cid])){
            $data = $this->context[$cid]; // 获取指定协程的上下文数据
            foreach ($data as $key => $val){
                $this->unset($key,$cid);
            }
        }
        unset($this->context[$cid]);
    }

    // 获取协程id
    public function getCid($cid = null):int
    {
        if($cid === null){
            $cid = Coroutine::getUid(); // 获取当前协程的唯一 ID, 它的别名为 getuid, 是一个进程内唯一的正整数
            if(!isset($this->deferList[$cid]) && $cid > 0){
                $this->deferList[$cid] = true;
                // 自动释放内存，当协程关闭时，会销毁该协程上下文
                Coroutine::defer(function ()use($cid){
                    unset($this->deferList[$cid]);
                    $this->destroy($cid);
                });
            }
            return $cid;
        }
        return $cid;
    }

    // 销毁整个上下文管理器对象
    public function destroyAll($force = false)
    {
        if($force){
            $this->context = [];
        }else{
            foreach ($this->context as $cid => $data){
                $this->destroy($cid);
            }
        }
    }

    // 获取指定协程的所有上下文信息
    public function getContextArray($cid = null):?array
    {
        $cid = $this->getCid($cid);
        if(isset($this->context[$cid])){
            return $this->context[$cid];
        }else{
            return null;
        }
    }
}