<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\File;

/**
 * 安装应用框架命令
 */
class Install implements CommandInterface
{
    public function commandName(): string
    {
        return 'install';
    }

    public function desc(): string
    {
        return 'Easyswoole framework installation';
    }

    public function exec(): ?string
    {
        if (is_file(EASYSWOOLE_ROOT . '/easyswoole')) {
            unlink(EASYSWOOLE_ROOT . '/easyswoole');
        }
        // 创建应用的easyswoole文件
        file_put_contents(EASYSWOOLE_ROOT . '/easyswoole', file_get_contents(__DIR__ . '/../../Resource/easyswoole'));
        // 创建Index.php文件
        Utility::releaseResource(__DIR__ . '/../../Resource/Http/Index._php', EASYSWOOLE_ROOT . '/App/HttpController/Index.php',true);
        // 创建Router文件
        Utility::releaseResource(__DIR__ . '/../../Resource/Http/Router._php', EASYSWOOLE_ROOT . '/App/HttpController/Router.php',true);
        // 创建dev文件
        Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/dev.php');
        // 创建produce文件
        Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/produce.php');
        // 创建bootstrap文件
        Utility::releaseResource(__DIR__ . '/../../Resource/bootstrap._php', EASYSWOOLE_ROOT . '/bootstrap.php');
        // 创建EasySwooleEvent文件
        Utility::releaseResource(__DIR__ . '/../../Resource/EasySwooleEvent._php', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
        $this->updateComposerJson(); // 在composer.json文件中添加 App 映射关系
        $this->execComposerDumpAutoload(); // 重新生成类加载映射文件
        $this->checkFunctionsOpen(); // 检测指定的函数是否开放
        return Color::success("install success,enjoy!!!\ndont forget run composer dump-autoload !!!");
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    protected function checkFunctionsOpen()
    {
        if (!function_exists('symlink') || !function_exists('readlink')) {
            echo Color::warning('Please at php.ini Open the symlink and readlink functions') . PHP_EOL;
        }
    }

    /**
     * 在composer.json文件中添加 App 映射关系
     */
    protected function updateComposerJson()
    {
        $arr = json_decode(file_get_contents(EASYSWOOLE_ROOT . '/composer.json'), true);
        $arr['autoload']['psr-4']['App\\'] = "App/";
        File::createFile(EASYSWOOLE_ROOT . '/composer.json', json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * 重新生成类加载映射文件
     */
    protected function execComposerDumpAutoload()
    {
        @exec('composer dump-autoload');
    }
}