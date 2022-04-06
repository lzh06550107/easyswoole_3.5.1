<?php
/**
 * Created by PhpStorm.
 * User: haoxu
 * Date: 2020-01-15
 * Time: 16:44
 */

namespace EasySwoole\ORM\Db;

use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Exception\Exception;
use phpDocumentor\Reflection\Types\This;
use Swoole\Coroutine\MySQL\Statement;

class Cursor implements CursorInterface
{
    protected $statement; // 
    protected $modelName; // 模型类名称
    protected $returnAsArray = false; // 是否以数组形式返回结果

    public function __construct(Statement $statement)
    {
        $this->statement = $statement;
    }


    public function setModelName(string $modelName)
    {
        $this->modelName = $modelName;
    }

    public function setReturnAsArray(bool $returnAsArray): void
    {
        $this->returnAsArray = $returnAsArray;
    }


    /**
     * @return mixed
     * @throws \Throwable
     */
    public function fetch()
    {
        try{
            $data = $this->statement->fetch(); // 从结果集中获取下一行数据
            if (!$this->returnAsArray && $data) {
                if (is_null($this->modelName)) {
                    throw new Exception('ModelName can not be null');
                }
                $model = new $this->modelName($data); // 初始化模型对象
                return $model;
            }
            return $data;
        }catch (\Throwable $throw){
            throw $throw;
        }
    }
}