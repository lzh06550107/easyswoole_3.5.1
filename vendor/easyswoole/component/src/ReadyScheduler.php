<?php


namespace EasySwoole\Component;

use Swoole\Coroutine;
use Swoole\Table;

/**
 * 基于Swoole Table实现的就绪计划程序，用于解决主服务启动时，部分子服务未就绪问题
 */
class ReadyScheduler
{
    use Singleton;

    const STATUS_UNREADY = 0; // 服务未就绪
    const STATUS_READY = 1; // 服务就绪

    private $table;

    function __construct()
    {
        $this->table = new Table(2048);
        $this->table->column('status',Table::TYPE_INT,1);
        $this->table->create();
    }

    /**
     * 添加等待的服务项
     * @param string $key
     * @param int $status
     * @return $this
     */
    function addItem(string $key,int $status = self::STATUS_UNREADY):ReadyScheduler
    {
        $this->table->set($key,[
            'status'=>$status
        ]);
        return $this;
    }

    /**
     * 获取指定的服务状态
     * @param string $key
     * @return int|null
     */
    function status(string $key):?int
    {
        $ret = $this->table->get($key);
        if($ret){
            return $ret['status'];
        }else{
            return null;
        }
    }

    /**
     * 服务设置为就绪状态
     * @param string $key
     * @param bool $force
     * @return $this
     */
    function ready(string $key,bool $force = false):ReadyScheduler
    {
        if($force){
            $this->table->set($key,[
                'status'=>self::STATUS_READY
            ]);
        }else{
            $this->table->incr($key,'status',1);
        }
        return $this;
    }

    /**
     * 服务设置为未就绪状态
     * @param string $key
     * @param bool $force
     * @return $this
     */
    function unready(string $key,bool $force = false):ReadyScheduler
    {
        if($force){
            $this->table->set($key,[
                'status'=>self::STATUS_UNREADY
            ]);
        }else{
            $this->table->decr($key,'status',1);
        }
        return $this;
    }

    
    function restore(string $key,?int $status):ReadyScheduler
    {
        $this->table->set($key,[
            'status'=>$status
        ]);
        return $this;
    }

    /**
     * 等待所有指定的服务就绪
     * @param $keys
     * @param float $time 
     * @return bool
     */
    function waitReady($keys,float $time = 3.0):bool
    {
        if(!is_array($keys)){
            $keys = [$keys];
        }else if(empty($keys)){
            return true;
        }
        while (1){
            foreach ($keys as $key => $item){
                if($this->status($item) >= self::STATUS_READY){
                    unset($keys[$key]); // 如果已经就绪，则从等待列表中移除
                }
                if(count($keys) == 0){ // 当所有的等待服务都就绪，则返回
                    return true;
                }
                if($time > 0){
                    $time = $time - 0.01;
                    Coroutine::sleep(0.01); // 切换协程
                }else{ // 超时，返回false
                    return false;
                }
            }
        }
    }

    /**
     * 等待任何一个服务就绪
     * @param array $keys
     * @param float $timeout
     * @return bool
     */
    function waitAnyReady(array $keys,float $timeout = 3.0):bool
    {
        if(empty($keys)){
            return true;
        }
        while (1){
            foreach ($keys as $key){
                if($this->status($key) >= self::STATUS_READY){
                    return true; // 任何一个服务就绪，则立即返回
                }
            }
            if($timeout > 0){
                $timeout = $timeout - 0.01;
                Coroutine::sleep(0.01); // 切换协程
            }else{
                return false;
            }
        }
    }

}