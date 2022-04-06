<?php


namespace EasySwoole\CodeGeneration\ModelGeneration\Method;

/**
 * 
 * Created on 2022/4/6 11:59
 * Create by LZH
 */
abstract class MethodAbstract  extends \EasySwoole\CodeGeneration\ClassGeneration\MethodAbstract
{

    protected function chunkTableColumn(callable $callback)
    {
        $table = $this->classGeneration->getConfig()->getTable();
        foreach ($table->getColumns() as $column) {
            $columnName = $column->getColumnName();
            $result = $callback($column, $columnName);
            if ($result ===true){ // 回调方法返回false，则中断列循环
                break;
            }
        }
    }


}
