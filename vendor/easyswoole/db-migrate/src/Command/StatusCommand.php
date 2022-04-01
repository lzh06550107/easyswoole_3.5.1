<?php

namespace EasySwoole\DatabaseMigrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\DatabaseMigrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\DatabaseMigrate\MigrateManager;

/**
 * Class StatusCommand，展示成功迁移的数据记录，即为迁移表内的数据记录
 * @package EasySwoole\DatabaseMigrate\Command\Migrate
 * @author heelie.hj@gmail.com
 * @date 2020/9/4 22:14:56
 */
final class StatusCommand extends CommandAbstract
{
    private $strlen = 'strlen';

    public function commandName(): string
    {
        return 'migrate status';
    }

    public function desc(): string
    {
        return 'database migrate status';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    /**
     * 打印迁移记录表中所有记录
     * @return string|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \Throwable
     * @author heelie.hj@gmail.com
     * @date 2021-04-07 09:07:21
     */
    public function exec(): ?string
    {
        $allMigrateInfo = $this->getAllMigrateInfo();
        $this->checkLenFunc();
        if (count($allMigrateInfo) == 0) return null;
        $tmpData = [];
        
        foreach (array_keys(current($allMigrateInfo)) as $key) {
            $$key          = ($this->strlen)($key);
            $tmpData[$key] = $key;
        }
        
        array_unshift($allMigrateInfo, $tmpData);
        
        foreach ($allMigrateInfo as $item) {
            foreach ($item as $key => $value) {
                (($this->strlen)($value) > $$key) and ($$key = ($this->strlen)($value));
            }
        }
        
        $isolation = '';
        
        foreach ($tmpData as $key) {
            $isolation .= '+' . str_pad('', $$key + 2, '-');
        }
        $isolation .= '+' . PHP_EOL;

        $text = $isolation;
        foreach ($allMigrateInfo as $item) {
            foreach ($item as $key => $value) {
                $text .= '|' . str_pad(' ' . $value, $$key + 2, ' ');
            }
            $text .= '|' . PHP_EOL . $isolation;
        }
        return $text;
    }

    /**
     * 获取迁移表中所有的数据记录
     * @return array|bool|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \Throwable
     */
    private function getAllMigrateInfo()
    {
        $config = MigrateManager::getInstance()->getConfig();
        $sql    = 'SELECT * FROM ' . $config->getMigrateTable();
        return MigrateManager::getInstance()->query($sql);
    }

    /**
     * 是否存在多字节长度计算函数
     */
    public function checkLenFunc()
    {
        if (function_exists('mb_strlen')) {
            $this->strlen = 'mb_strlen';
        }
    }
}
