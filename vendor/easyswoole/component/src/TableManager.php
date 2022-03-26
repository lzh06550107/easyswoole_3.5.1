<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 15:54
 */

namespace EasySwoole\Component;

use Swoole\Table;

/**
 * 内存共享表管理器
 */
class TableManager
{
    use Singleton;

    private $list = [];


    /**
     * 添加指定名称的内存共享表
     * @param $name
     * @param array $columns    ['col'=>['type'=>Table::TYPE_STRING,'size'=>1]]
     * @param int $size
     */
    public function add($name,array $columns,$size = 1024):void
    {
        if(!isset($this->list[$name])){
            $table = new Table($size);
            foreach ($columns as $column => $item){
                $table->column($column,$item['type'],$item['size']);
            }
            $table->create();
            $this->list[$name] = $table;
        }
    }

    /**
     * 获取指定名称的内存共享表
     * @param $name
     * @return Table|null
     */
    public function get($name):?Table
    {
        if(isset($this->list[$name])){
            return $this->list[$name];
        }else{
            return null;
        }
    }

    /**
     * 删除并销毁指定名称的内存共享表
     * @param $name
     */
    public function del($name)
    {
        if(isset($this->list[$name])){
            $this->list[$name]->destroy();
            unset($this->list[$name]);
        }
    }
}
