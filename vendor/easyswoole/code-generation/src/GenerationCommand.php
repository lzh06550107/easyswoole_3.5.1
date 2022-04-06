<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 2020-05-20
 * Time: 16:51
 */

namespace EasySwoole\CodeGeneration;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\Component\Di;
use EasySwoole\Component\Timer;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

/**
 * 生成代码命令
 */
class GenerationCommand implements CommandInterface
{
    public function commandName(): string
    {
        return "generation";
    }

    public function desc(): string
    {
        return 'Code auto generation tool';
    }


    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('init', 'initialization'); // 主要是初始化基类
        $commandHelp->addAction('all', 'specify build'); // 生成所有类
        $commandHelp->addActionOpt('--tableName', 'specify table name');
        $commandHelp->addActionOpt('--modelPath', 'specify model path');
        $commandHelp->addActionOpt('--controllerPath', 'specify controller path');
        $commandHelp->addActionOpt('--unitTestPath', 'specify unit-test path');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        $action = CommandManager::getInstance()->getArg(0);
        $result = new Result();
        $run = new Scheduler(); // 实例化协程调度器对象
        $run->add(function () use (&$result, $action) { // 添加协程
            switch ($action) {
                case 'init':
                    $result = $this->init();
                    break;
                case 'all':
                    $result = $this->all();
                    break;
                default:
                    $result = CommandManager::getInstance()->displayCommandHelp($this->commandName());
                    break;
            }
            Timer::getInstance()->clearAll();
        });
        $run->start(); // 启动协程调度器
        return $result . PHP_EOL;
    }

    /**
     * 初始化各层基类
     * @return ArrayToTextTable
     */
    protected function init()
    {
        $table = [];
        $table[0] = ['className' => 'Model', 'filePath' => $this->generationBaseModel()];
        $table[1] = ['className' => 'Controller', 'filePath' => $this->generationBaseController()];
        $table[2] = ['className' => 'UnitTest', 'filePath' => $this->generationBaseUnitTest()];

        return new ArrayToTextTable($table); // 用表格打印
    }

    /**
     * 生成所有类
     * @return ArrayToTextTable|string
     */
    protected function all()
    {
        $tableName = CommandManager::getInstance()->getOpt('tableName'); // 生成指定表的模型
        if (empty($tableName)) {
            return Color::error('table not empty');
        }

        $modelPath = CommandManager::getInstance()->getOpt('modelPath'); // 生成的模型所在目录
        $controllerPath = CommandManager::getInstance()->getOpt('controllerPath'); // 生成的控制器所在目录
        $unitTestPath = CommandManager::getInstance()->getOpt('unitTestPath'); // 生成的单元测试用例所在目录
        $connection = $this->getConnection();
        $codeGeneration = new CodeGeneration($tableName, $connection); // 初始化代码生成器
        $this->trySetDiGenerationPath($codeGeneration);

        $table = [];
        if ($modelPath) {
            $filePath = $codeGeneration->generationModel($modelPath);
            $table[] = ['className' => 'Model', "filePath" => $filePath];
        } else {
            return Color::error('Model path must be specified');
        }

        if ($controllerPath) {
            $filePath = $codeGeneration->generationController($controllerPath);
            $table[] = ['className' => 'Controller', "filePath" => $filePath];
        }
        if ($unitTestPath) {
            $filePath = $codeGeneration->generationUnitTest($unitTestPath);
            $table[] = ['className' => 'UnitTest', "filePath" => $filePath];
        }

        return new ArrayToTextTable($table);
    }

    /**
     * 根据代码生成配置获取数据库连接
     * @return Connection
     * @throws \EasySwoole\Spl\Exception\Exception
     * @throws \Throwable
     */
    protected function getConnection(): Connection
    {
        $connection = Di::getInstance()->get('CodeGeneration.connection');
        if ($connection instanceof Connection) {
            return $connection;
        } elseif (is_array($connection)) {
            $mysqlConfig = new \EasySwoole\ORM\Db\Config($connection);
            $connection = new Connection($mysqlConfig);
            return $connection;
        } elseif ($connection instanceof \EasySwoole\ORM\Db\Config) {
            $connection = new Connection($connection);
            return $connection;
        }
    }

    /**
     * 尝试注入代码生成配置
     * @param CodeGeneration $codeGeneration
     * @throws \Throwable
     */
    protected function trySetDiGenerationPath(CodeGeneration $codeGeneration)
    {
        $modelBaseNameSpace = Di::getInstance()->get('CodeGeneration.modelBaseNameSpace'); // 模型基类命名空间
        $controllerBaseNameSpace = Di::getInstance()->get('CodeGeneration.controllerBaseNameSpace'); // 控制器基类命名空间
        $unitTestBaseNameSpace = Di::getInstance()->get('CodeGeneration.unitTestBaseNameSpace'); // 单元测试基类命名空间
        $rootPath = Di::getInstance()->get('CodeGeneration.rootPath'); // 项目根目录
        if ($modelBaseNameSpace) {
            $codeGeneration->setModelBaseNameSpace($modelBaseNameSpace);
        }
        if ($controllerBaseNameSpace) {
            $codeGeneration->setControllerBaseNameSpace($controllerBaseNameSpace);
        }
        if ($unitTestBaseNameSpace) {
            $codeGeneration->setUnitTestBaseNameSpace($unitTestBaseNameSpace);
        }
        if ($unitTestBaseNameSpace) {
            $codeGeneration->setRootPath($rootPath);
        }
    }

    protected function generationBaseController()
    {
        $generation = new \EasySwoole\CodeGeneration\InitBaseClass\Controller\ControllerGeneration();
        return $generation->generate();
    }

    protected function generationBaseUnitTest()
    {
        $generation = new \EasySwoole\CodeGeneration\InitBaseClass\UnitTest\UnitTestGeneration();
        return $generation->generate();
    }

    /**
     * 基础模型代码生成
     * @return bool|int|string
     */
    protected function generationBaseModel()
    {
        $generation = new \EasySwoole\CodeGeneration\InitBaseClass\Model\ModelGeneration();
        return $generation->generate();
    }

}
