<?php

namespace EasySwoole\DatabaseMigrate\Validate;

use EasySwoole\DatabaseMigrate\Utility\Util;

/**
 * Class Validator 验证器
 * @package EasySwoole\DatabaseMigrate\Validate
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:29:54
 */
class Validator
{
    /**
     * 验证是否是有效名称
     * @param string $str
     * @return bool
     */
    public static function isValidName(string $str): bool
    {
        return boolval(preg_match('/^(?!_|\d)\w+$/', $str));
    }

    /**
     * 验证是否是驼峰名称
     * @param string $str
     * @return bool
     */
    public static function isHumpName(string $str): bool
    {
        return boolval(preg_match('/^([A-Z][a-z0-9]+)+$/', $str));
    }

    /**
     * 验证是否是有效的类名称
     * @param $className
     * @param $type
     * @return bool
     */
    public static function validClass($className, $type)
    {
        $files = [];
        switch ($type) {
            case 'migrate':
                $files = Util::getAllMigrateFiles();
                break;
            case 'seeder':
                $files = Util::getAllSeederFiles();
                break;
        }

        Util::requireOnce($files);

        return class_exists($className);
    }
}