<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 20-4-25
 * Time: 上午10:51
 */

namespace EasySwoole\CodeGeneration\ControllerGeneration\Method;

use EasySwoole\CodeGeneration\ControllerGeneration\ControllerConfig;
use EasySwoole\CodeGeneration\ControllerGeneration\ControllerGeneration;
use EasySwoole\DDL\Blueprint\Create\Index;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\ORM\Utility\Schema\Column;

/**
 * 控制器方法抽象类
 */
abstract class MethodAbstract extends \EasySwoole\CodeGeneration\ClassGeneration\MethodAbstract
{
    /**
     * @var \Nette\PhpGenerator\Method $method
     */
    protected $method;

    /**
     * @var ControllerConfig
     */
    protected $controllerConfig;

    protected $methodName = 'methodName';
    protected $methodDescription = '这是生成的测试方法介绍';
    protected $responseParam = [
        'code'   => '状态码',
        'result' => 'api请求结果',
        'msg'    => 'api提示信息',
    ];
    protected $authParam = null;
    protected $methodAllow = "GET,POST";
    protected $responseSuccessText = '{"code":200,"result":[],"msg":"操作成功"}';
    protected $responseFailText = '{"code":400,"result":[],"msg":"操作失败"}"}';

    /**
     * @param ControllerGeneration $classGeneration
     */
    function __construct(ControllerGeneration $classGeneration)
    {
        $this->classGeneration = $classGeneration;
        $method = $classGeneration->getPhpClass()->addMethod($this->getMethodName());
        $this->method = $method;
        $this->controllerConfig = $classGeneration->getConfig();
        $this->authParam = $classGeneration->getConfig()->getAuthSessionName();
    }


    function run(){
        $this->addRequestComment();
        $this->addResponseComment();
        $this->addMethodBody();
    }


    /**
     * 新增方法请求头注释
     * addComment
     * @author tioncico
     * Time: 上午11:05
     */
    protected function addRequestComment()
    {
        $realTableName = $this->controllerConfig->getRealTableName();
        $apiUrl = $this->getApiUrl();
        $method = $this->method;
        $methodName = $this->methodName;

        //配置基础注释
        $method->addComment("@Api(name=\"{$methodName}\",path=\"{$apiUrl}/{$realTableName}/{$methodName}\")");
        $method->addComment("@ApiDescription(\"{$this->methodDescription}\")");
        $method->addComment("@Method(allow={{$this->methodAllow}})");
        if ($this->authParam) {
            $method->addComment("@Param(name=\"{$this->authParam}\", from={COOKIE,GET,POST}, alias=\"权限验证token\" required=\"\")");
        }
        $method->addComment("@InjectParamsContext(key=\"param\")");
    }

    /**
     * 新增方法响应参数
     * addResponseComment
     * @author tioncico
     * Time: 上午11:05
     */
    protected function addResponseComment()
    {
        $method = $this->method;
        foreach ($this->responseParam as $name => $description) {
            $method->addComment("@ApiSuccessParam(name=\"{$name}\",description=\"{$description}\")");
        }
        $method->addComment("@ApiSuccess({$this->responseSuccessText})");
        $method->addComment("@ApiFail({$this->responseFailText})");
    }

    /**
     * 获取控制器的api url
     * @return array|string|string[]
     */
    protected function getApiUrl()
    {
        $baseNamespace = $this->controllerConfig->getNamespace();
        $apiUrl = str_replace(['App\\HttpController', '\\'], ['', '/'], $baseNamespace);
        return $apiUrl;
    }

    /**
     * 获取模型名称
     * @return false|mixed|string
     */
    protected function getModelName()
    {
        $modelNameArr = (explode('\\', $this->controllerConfig->getModelClass()));
        $modelName = end($modelNameArr);
        return $modelName;
    }

    /**
     * 给方法参数添加注释，列约束可以转换为方法参数约束
     * addColumnComment
     * @param \EasySwoole\HttpAnnotation\AnnotationTag\Param $param
     * @author Tioncico
     * Time: 9:49
     */
    protected function addColumnComment(Param $param)
    {
        $method = $this->method;
        $commentStr = "@Param(name=\"{$param->name}\"";
        $arr = ['alias', 'description', 'lengthMax', 'required', 'optional', 'defaultValue'];
        foreach ($arr as $value) {
            if ($param->$value !== null) {
                $commentStr .= ",$value=\"{$param->$value}\"";
            }
        }
        $commentStr .= ")";
        $method->addComment($commentStr);
    }

    /**
     * 初始化一个列参数
     * @param Column $column
     * @return Param
     */
    protected function newColumnParam(Column $column)
    {
        $columnName = $column->getColumnName(); // 获取列名称
        $columnComment = $column->getColumnComment(); // 获取列注释
        $paramValue = new Param();
        $paramValue->name = $columnName; // 参数名称
        $paramValue->alias = $columnComment; // 参数注释
        $paramValue->description = $columnComment; // 参数注释作为参数描述
        $paramValue->lengthMax = $column->getColumnLimit(); // 设置参数值长度限制
        $paramValue->defaultValue = $column->getDefaultValue(); // 设置参数默认值
        return $paramValue;
    }

    /**
     * 对表中的列迭代调用回调函数
     * @param callable $callback
     */
    protected function chunkTableColumn(callable $callback)
    {
        $table = $this->controllerConfig->getTable();
        foreach ($table->getColumns() as $column) {
            $columnName = $column->getColumnName();
            $result = $callback($column, $columnName);
            if ($result ===true){
                break;
            }
        }
    }

    /**
     * 对表中的索引迭代调用回调函数，索引列转换为条件查询
     * @param callable $callback
     */
    protected function chunkTableIndex(callable $callback)
    {
        $table = $this->controllerConfig->getTable();
        /**
         * @var $index Index
         */
        foreach ($table->getIndexes() as $index) {
            //程序的逻辑只会存在一个索引
            $columnName = $index->getIndexColumns();
            $result = $callback($table->getColumns()[$columnName], $columnName);
            if ($result ===true){
                break;
            }
        }
    }

    /**
     * 获取方法名称
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

}
