<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 20-4-25
 * Time: 上午11:15
 */

namespace EasySwoole\CodeGeneration\ControllerGeneration\Method;

use EasySwoole\ORM\Utility\Schema\Column;

/**
 * 添加数据
 */
class Add extends MethodAbstract
{
    protected $methodName = 'add';
    protected $methodDescription = '新增数据';
    protected $responseParam = [
        'code'   => '状态码',
        'result' => 'api请求结果',
        'msg'    => 'api提示信息',
    ];
    protected $authParam = 'userSession';
    protected $methodAllow = "GET,POST";
    protected $responseSuccessText = '{"code":200,"result":[],"msg":"新增成功"}';
    protected $responseFailText = '{"code":400,"result":[],"msg":"新增失败"}';

    /**
     * 添加数据方法主体
     * @return mixed|void
     */
    function addMethodBody()
    {
        $method = $this->method;
        $modelName = $this->getModelName();
        // 从协程上下文中获取参数
        $methodBody = <<<Body
\$param = ContextManager::getInstance()->get('param');
\$data = [

Body;
        $this->chunkTableColumn(function (Column $column, string $columnName) use (&$methodBody) {
            $paramValue = $this->newColumnParam($column);
            if ($column->getIsAutoIncrement()){ // 表主键自增列忽略
                return false;
            }
            if ($column->isNotNull()) { // 如果非空列，则该参数必须
                $paramValue->required = '';
                $methodBody .= "    '{$columnName}'=>\$param['{$columnName}'],\n";
            } else { // 否则，该参数可选
                $paramValue->optional = '';
                $methodBody .= "    '{$columnName}'=>\$param['{$columnName}'] ?? '',\n";
            }
            $this->addColumnComment($paramValue);
        });

        $methodBody .= <<<Body
];
\$model = new {$modelName}(\$data);
\$model->save();
\$this->writeJson(Status::CODE_OK, \$model->toArray(), "新增成功");

Body;

        //配置方法内容
        $method->setBody($methodBody);
    }


}
