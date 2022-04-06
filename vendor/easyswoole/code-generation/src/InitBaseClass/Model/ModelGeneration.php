<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-21
 * Time: 10:13
 */

namespace EasySwoole\CodeGeneration\InitBaseClass\Model;

use EasySwoole\CodeGeneration\ClassGeneration\ClassGeneration;
use EasySwoole\CodeGeneration\InitBaseClass\Model\ModelConfig;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\DbManager;

/**
 * 模型层基类生成
 */
class ModelGeneration extends ClassGeneration
{
    /**
     * @var $config ModelConfig
     */
    protected $config;

    /**
     * 初始化模型配置
     * @param \EasySwoole\CodeGeneration\InitBaseClass\Model\ModelConfig|null $config
     * @throws \Exception
     */
    public function __construct(?ModelConfig $config=null)
    {
        if (empty($config)){
            $config = new ModelConfig("BaseModel","App\\Model");
            $config->setExtendClass(AbstractModel::class);
        }
        parent::__construct($config);
    }

    function addClassData()
    {
        $this->phpNamespace->addUse(DbManager::class); // 添加数据库连接管理器
        $method = $this->phpClass->addMethod('transaction'); // 添加开启事务方法
        $method->setStatic(); // 设置为静态方法
        $method->addParameter('callable')->setType('callable'); // 需要事务的闭包参数
        $method->setBody(<<<BODY
try {
    DbManager::getInstance()->startTransaction();
    \$result = \$callable();
    DbManager::getInstance()->commit();
    return \$result;
} catch (\Throwable \$throwable) {
    DbManager::getInstance()->rollback();
    throw \$throwable;
}
BODY
        );
        $method = $this->phpClass->addMethod('getPageList'); // 添加分页查询方法
        $method->addParameter('page')->setType('int')->setDefaultValue(1); // 默认第一页
        $method->addParameter('pageSize')->setType('int')->setDefaultValue(20); // 一页20条记录
        $method->addParameter('isCount')->setType('bool')->setDefaultValue(true); // 是否返回总记录数和分页数
        $method->setBody(<<<BODY
if (\$isCount){
    \$this->withTotalCount();
}
\$list = \$this
    ->page(\$page, \$pageSize)
    ->all();
\$data = [];
\$data['list'] = \$list;
\$data['page'] = \$page;
\$data['pageSize']  = \$pageSize;
if (\$isCount){
    \$total = \$this->lastQueryResult()->getTotalCount();
    \$data['total'] = \$total;
    \$data['pageCount'] = ceil(\$total / \$pageSize);
}

return \$data;
BODY
        );
    }
}
