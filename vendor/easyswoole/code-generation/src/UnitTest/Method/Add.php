<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-21
 * Time: 09:39
 */

namespace EasySwoole\CodeGeneration\UnitTest\Method;

use EasySwoole\CodeGeneration\Utility\Utility;

/**
 * 测试保存数据方法
 */
class Add extends UnitTestMethod
{
    protected $methodName = 'testAdd';
    protected $actionName = 'add';
    function addMethodBody()
    {
        $method = $this->method;
        $body = <<<BODY
\$data = [];

BODY;
        $body .= $this->getTableTestData('data'); // 获取测试随机生成的数据
        $modelName = Utility::getModelName($this->classGeneration->getConfig()->getModelClass());

        $body .= <<<BODY
\$response = \$this->request('{$this->actionName}',\$data);
\$model = new {$modelName}();
\$model->destroy(\$response->result->{$this->classGeneration->getConfig()->getTable()->getPkFiledName()}); // 删除添加的测试数据
//var_dump(json_encode(\$response,JSON_UNESCAPED_UNICODE));
BODY;
        $method->setBody($body);

    }
}
