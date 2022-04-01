<?php

namespace EasySwoole\DatabaseMigrate\Utility;

use DateTime;
use DateTimeZone;
use EasySwoole\DatabaseMigrate\MigrateManager;
use EasySwoole\DatabaseMigrate\Validate\Validator;
use Throwable;

/**
 * Class Util，工具类
 * @package EasySwoole\DatabaseMigrate\Utility
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:28:02
 */
class Util
{
    /**
     * 下划线转换为驼峰
     * @param string $str
     * @return string
     */
    public static function lineConvertHump(string $str): string
    {
        return ucfirst(preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str));
    }

    /**
     * 驼峰转换为下划线
     * @param string $str
     * @return string
     */
    public static function humpConvertLine(string $str): string
    {
        return ltrim(preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, str_replace('_', '', $str)), '_');
    }

    /**
     * 二维数组的某一列值作为键，该列值相同的行封装在数组中
     * @param array $array
     * @param string $indexKey
     * @return array
     */
    public static function arrayBindKey(array $array, string $indexKey)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (isset($value[$indexKey])) {
                $result[$value[$indexKey]][] = $value;
            }
        }
        return $result;
    }

    /**
     * 获取迁移文件最终名称
     * @param $migrateName
     * @return string
     * @throws Throwable
     */
    public static function genMigrateFileName($migrateName)
    {
        if (Validator::isHumpName($migrateName)) { // 驼峰命名转换为下划线
            $migrateName = self::humpConvertLine($migrateName);
        }
        return self::getCurrentMigrateDate() . '_' . $migrateName . '.php';
    }

    /**
     * 迁移文件日期
     * @return string
     * @throws Throwable
     */
    public static function getCurrentMigrateDate()
    {
        return (new DateTime('now', new DateTimeZone('UTC')))->format('Y_m_d_His');
    }

    /**
     * 迁移文件名称转换为类名称
     * @param $fileName
     * @return string
     */
    public static function migrateFileNameToClassName($fileName)
    {
        $withoutDateFileName = implode('_', array_slice(explode('_', $fileName), 4));
        return self::lineConvertHump(pathinfo($withoutDateFileName, PATHINFO_FILENAME));
    }

    /**
     * 获取所有的迁移文件
     * @return array
     */
    public static function getAllMigrateFiles(): array
    {
        $config = MigrateManager::getInstance()->getConfig();
        return glob($config->getMigratePath() . '*.php');
    }

    /**
     * 获取所有数据种子文件
     * @return array
     */
    public static function getAllSeederFiles(): array
    {
        $config = MigrateManager::getInstance()->getConfig();
        return glob($config->getSeederPath() . '*.php');
    }

    /**
     * 批量加载文件
     * @param $files
     */
    public static function requireOnce($files)
    {
        foreach ((array)$files as $file) {
            require_once $file;
        }
    }
}
