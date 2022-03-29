<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/22
 * Time: 下午2:54
 */

namespace EasySwoole\Spl;

/**
 * 用于定义枚举一个集合，规范化枚举数据
 */
class SplEnum
{
    private $val = null; // 常量值
    private $name = null; // 常量名称

    final public function __construct($val)
    {
        $list = self::getConstants(); // 获取类常量
        //禁止重复值
        if (count($list) != count(array_unique($list))) {
            $class = static::class;
            throw new \Exception("class : {$class} define duplicate value");
        }
        $this->val = $val;
        $this->name = self::isValidValue($val); // 根据值返回常量名称
        if($this->name === false){
            throw new \Exception("invalid value");
        }
    }

    final public function getName():string
    {
        return $this->name;
    }

    final public function getValue()
    {
        return $this->val;
    }

    final public static function isValidName(string $name):bool
    {
        $list = self::getConstants();
        if(isset($list[$name])){
            return true;
        }else{
            return false;
        }
    }

    final public static function isValidValue($val)
    {
        $list = self::getConstants();
        return array_search($val,$list); // 在数组中搜索键值，并返回它的键名
    }

    final public static function getEnumList():array
    {
        return self::getConstants();
    }

    /**
     * 获取类常量
     * @return array
     */
    private static function getConstants():array
    {
        try{
            return (new \ReflectionClass(static::class))->getConstants();
        }catch (\Throwable $throwable){
            return [];
        }
    }

    /**
     * 返回常量名称
     * @return string
     */
    function __toString()
    {
        // TODO: Implement __toString() method.
        return (string)$this->getName();
    }
}