<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:24
 */

namespace EasySwoole\EasySwoole\Command;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\Utility\File;

/**
 * 命令工具类
 */
class Utility
{
    /**
     * 显示logo
     * @return string
     */
    public static function easySwooleLog()
    {
        return <<<LOGO
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/

LOGO;
    }

    /**
     * 格式化显示名称和值
     * @param $name
     * @param $value
     * @return string
     */
    static function displayItem($name, $value)
    {
        if ($value === true) {
            $value = 'true';
        } else if ($value === false) {
            $value = 'false';
        } else if ($value === null) {
            $value = 'null';
        } else if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
        return "\e[32m" . str_pad($name, 30, ' ', STR_PAD_RIGHT) . "\e[34m" . $value . "\e[0m";
    }

    /**
     * 从源中复制文件到指定的位置，用户需要确认是否替换
     * @param $source
     * @param $destination
     * @param $confirm
     * @return void
     */
    public static function releaseResource($source, $destination,$confirm = false)
    {
        $filename = basename($destination);
        clearstatcache(); // 清除文件状态缓存
        $replace = true; // 是否替换已经存在的文件

        // 如果文件存在，则询问用户是否替换
        if (is_file($destination)) {
            echo Color::danger("{$filename} has already existed, do you want to replace it? [ Y / N (default) ] : ");
            $answer = strtolower(trim(strtoupper(fgets(STDIN))));
            if (!in_array($answer, ['y', 'yes'])) {
                $replace = false;
            }
        }

        if ($replace) {
            if($confirm){ // 如果需要用户确认是否替换，则需要获取用户的确认
                echo Color::danger("do you want to release {$filename}? [ Y / N (default) ] : ");
                $answer = strtolower(trim(strtoupper(fgets(STDIN))));
                if (!in_array($answer, ['y', 'yes'])) {
                    return;
                }
            }
            File::copyFile($source, $destination);
        }
    }

    /**
     * 清空控制台中缓存
     * @return void
     */
    public static function opCacheClear()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache(); // 清除用户/系统缓存
        }
        if (function_exists('opcache_reset')) {
            opcache_reset(); // 该函数将重置整个字节码缓存。 在调用 opcache_reset() 之后，所有的脚本将会重新载入并且在下次被点击的时候重新解析。
        }
    }

    /**
     * 和服务中的桥接进程进行通讯
     * @param string $commandName 命令名称
     * @param callable $function 回调函数
     * @param $action 桥接进程中的动作名称
     * @param $params 桥接进程中的动作参数
     * @param $timeout 超时时间
     * @return false|mixed|string
     */
    public static function bridgeCall(string $commandName, callable $function, $action, $params = [], $timeout = 3)
    {
        $arg = ['action' => $action] + $params;
        $package = Bridge::getInstance()->call($commandName, $arg, $timeout);
        if ($package->getStatus() == Package::STATUS_SUCCESS) { // 调用成功，则调用回调函数来处理返回结果
            $result = call_user_func($function, $package);
        } else {
            $result = Color::error($package->getMsg());
        }
        return $result;
    }

}
