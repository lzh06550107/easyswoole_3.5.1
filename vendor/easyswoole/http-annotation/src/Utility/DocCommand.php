<?php

namespace EasySwoole\HttpAnnotation\Utility;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;

/**
 * 命令行生成离线文档，允许通过注解控制器的注解标签，生成文档
 */
class DocCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'doc';
    }

    public function desc(): string
    {
        return 'Build api doc by annotations';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('--dir', 'scanned directory or file'); // 指定控制器所在目录
        $commandHelp->addActionOpt('--extra', 'import ext MD file'); // 导入额外的md文件
        return $commandHelp;
    }

    public function exec(): ?string
    {
        $dir = CommandManager::getInstance()->getOpt('dir');
        if (!$dir) {
            return Color::error('Scan directory must be specified. Please use - h for help') . PHP_EOL;
        }

        if (!is_dir($dir) && !is_file($dir)) {
            return Color::error("This [$dir] is not a directory or a file") . PHP_EOL;
        }

        $extra = CommandManager::getInstance()->getOpt('extra');
        if ($extra && !is_file($extra)) {
            return Color::error("This [$extra] is not a file") . PHP_EOL;
        }

        $extra = $extra ? file_get_contents($extra) : null;

        $doc = new AnnotationDoc();
        $string = $doc->scan2Html($dir, $extra);

        if (!empty($string)) {
            file_put_contents('easyDoc.html', $string); // 写入文件中
            $ret = Color::success('The generated file is easyDoc.html') . PHP_EOL;
        } else {
            $ret = Color::error('There is no API to generate') . PHP_EOL;
        }

        return $ret;
    }
}
