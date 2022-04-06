<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-21
 * Time: 10:13
 */

namespace EasySwoole\CodeGeneration\InitBaseClass\UnitTest;

use Curl\Curl;
use EasySwoole\CodeGeneration\ClassGeneration\ClassGeneration;
use PHPUnit\Framework\TestCase;

/**
 * 单元测试基类生成器
 */
class UnitTestGeneration extends ClassGeneration
{
    protected $apiBase = 'http://127.0.0.1:9501';
    /**
     * @var $config UnitTestConfig
     */
    protected $config;

    public function __construct(?UnitTestConfig $config = null)
    {
        if (empty($config)) {
            $config = new UnitTestConfig("BaseTest", "UnitTest");
            $config->setExtendClass(TestCase::class);
        }
        parent::__construct($config);
    }

    /**
     * 生成单元测试基类
     */
    function addClassData()
    {
        $this->phpNamespace->addUse(\EasySwoole\EasySwoole\Core::class);
        $this->phpNamespace->addUse(TestCase::class);
        $this->phpNamespace->addUse(Curl::class);
        $this->addProperty();
        $this->addRequest();
        $this->addSetUp();
    }

    protected function addProperty()
    {
        $class = $this->phpClass;
        $class->addProperty('isInit', 0)->setStatic(); // 应用是否已经初始化
        $class->addProperty('curl')->setComment("@var Curl"); // curl实例对象
        $class->addProperty('apiBase', $this->apiBase); // 请求url基础
        $class->addProperty('modelName');
    }

    /**
     * 调用测试方法前的初始化
     */
    protected function addSetUp()
    {
        $this->phpClass->addMethod('setUp')->setReturnType('void')->setProtected()->setBody(<<<BODY
\$this->curl = new Curl();
if (self::\$isInit == 1) {
    return;
}
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', dirname(__FILE__, 2));
require_once dirname(__FILE__, 2) . '/EasySwooleEvent.php';
Core::getInstance()->initialize();
self::\$isInit = 1;
BODY
        );
    }

    /**
     * 请求通用方法
     */
    protected function addRequest()
    {
        $method = $this->phpClass->addMethod('request');
        $method->addParameter('action');
        $method->addParameter('data')->setDefaultValue([]); // 请求参数
        $method->addParameter('modelName')->setDefaultValue(null);
        $method->setBody(<<<BODY
\$modelName = \$modelName ?? \$this->modelName;
\$url = \$this->apiBase . '/' . \$modelName . '/' . \$action;
\$curl = \$this->curl;
\$curl->post(\$url, \$data);
if (\$curl->response) {
//            var_dump(\$curl->response);
} else {
    echo 'Error: ' . \$curl->errorCode . ': ' . \$curl->errorMessage . "\n";
}
\$this->assertTrue(!!\$curl->response);
\$this->assertEquals(200, \$curl->response->code, \$curl->response->msg);
return \$curl->response;
BODY
        );
    }
}
