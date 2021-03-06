<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 16:08
 */

namespace EasySwoole\CodeGeneration\ControllerGeneration;

use EasySwoole\CodeGeneration\ClassGeneration\ClassGeneration;
use EasySwoole\CodeGeneration\ClassGeneration\MethodAbstract;
use EasySwoole\CodeGeneration\ControllerGeneration\Method\Add;
use EasySwoole\CodeGeneration\ControllerGeneration\Method\Delete;
use EasySwoole\CodeGeneration\ControllerGeneration\Method\GetList;
use EasySwoole\CodeGeneration\ControllerGeneration\Method\GetOne;
use EasySwoole\CodeGeneration\ControllerGeneration\Method\Update;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiResponseParam;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccessParam;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\Validate\Validate;
use Nette\PhpGenerator\PhpNamespace;

/**
 * 控制器生成器
 */
class ControllerGeneration extends ClassGeneration
{
    /**
     * @var $config ControllerConfig
     */
    protected $config;


    function addClassData()
    {
        $this->addUse($this->phpNamespace); // 指定生成控制器的命名空间
        $this->addGenerationMethod(new Add($this)); //
        $this->addGenerationMethod(new Update($this));
        $this->addGenerationMethod(new GetOne($this));
        $this->addGenerationMethod(new GetList($this));
        $this->addGenerationMethod(new Delete($this));
    }

    function getClassName()
    {
        return $this->config->getRealTableName() . $this->config->getFileSuffix();
    }

    protected function getApiGroup(){
        $className=  $this->getClassName();
        $namespace = $this->getConfig()->getNamespace();
        $namespace = str_replace('App\HttpController\\','',$namespace);
        $namespace = str_replace('\\','.',$namespace);
        return "{$namespace}.$className";
    }

    /**
     * 添加命名空间
     * @param PhpNamespace $phpNamespace
     */
    protected function addUse(PhpNamespace $phpNamespace)
    {
        $phpNamespace->addUse($this->config->getModelClass()); // 模型类命名空间
        $phpNamespace->addUse(Status::class); // 响应状态类命名空间
        $phpNamespace->addUse(Validate::class);  // 验证器类命名空间
        $phpNamespace->addUse(Validate::class); // TODO 重复
        $phpNamespace->addUse($this->config->getExtendClass()); // 扩展类命名空间
        //引入新版注解,以及文档生成
        $phpNamespace->addUse(ApiGroup::class); // 接口组注释类命名空间
        $phpNamespace->addUse(ApiGroupAuth::class); // 接口组认证注释类命名空间
        $phpNamespace->addUse(ApiGroupDescription::class); // 接口组描述注释类命名空间
        $phpNamespace->addUse(ApiFail::class); // 响应失败注释类命名空间
        $phpNamespace->addUse(ApiRequestExample::class); // 接口请求例子注释类命令空间
        $phpNamespace->addUse(ApiSuccess::class); // 响应成功注释类命名空间
        $phpNamespace->addUse(Method::class); // 方法注释类命名空间
        $phpNamespace->addUse(Param::class); // 方法参数注释类命名空间
        $phpNamespace->addUse(Api::class); // 接口注释类命名空间
        $phpNamespace->addUse(ApiSuccessParam::class); // 响应成功参数注释类命名空间
        $phpNamespace->addUse(ApiDescription::class); // 接口描述注释类命名空间
        $phpNamespace->addUse(ContextManager::class); // 上下文管理器命名空间
        $phpNamespace->addUse(InjectParamsContext::class); // 注入
    }

    function addGenerationMethod(MethodAbstract $abstract)
    {
        $this->methodGenerationList[$abstract->getMethodName()] = $abstract;
    }

    function addComment()
    {
        parent::addComment(); // TODO: Change the autogenerated stub
        $this->phpClass->addComment("@ApiGroup(groupName=\"{$this->getApiUrl()}.{$this->config->getRealTableName()}\")");
        $this->phpClass->addComment("@ApiGroupAuth(name=\"{$this->config->getAuthSessionName()}\")");
        $this->phpClass->addComment("@ApiGroupDescription(\"{$this->config->getTable()->getComment()}\")");
    }

    protected function getApiUrl()
    {
        $baseNamespace = $this->getConfig()->getNamespace();
        $apiUrl = str_replace(['App\\HttpController', '\\'], ['', '/'], $baseNamespace);
        return $apiUrl;
    }

}
