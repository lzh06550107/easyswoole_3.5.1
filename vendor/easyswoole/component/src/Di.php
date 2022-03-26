<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午4:17
 */

namespace EasySwoole\Component;

/**
 * 依赖注入管理器
 */
class Di
{
    use Singleton;

    private $container = []; // 容器对象
    private $onKeyMiss = null; // 不存在的键使用的回调函数
    private $alias = []; // 键别名

    /**
     * 给键添加别名
     * @param $alias 别名
     * @param $key 键
     * @return $this
     */
    public function alias($alias,$key): Di
    {
        // 如果别名在容器中不存在，则添加别名和键之间的映射关系
        if(!array_key_exists($alias,$this->container)){
            $this->alias[$alias] = $key;
            return $this;
        }else{
            throw new  \InvalidArgumentException("can not alias a real key: {$alias}");
        }
    }

    /**
     * 设置键丢失后的回调函数
     * @param callable $call 回调函数
     * @return $this
     */
    public function setOnKeyMiss(callable $call):Di
    {
        $this->onKeyMiss = $call;
        return $this;
    }

    /**
     * 删除别名
     * @param $alias 别名
     * @return $this
     */
    public function deleteAlias($alias): Di
    {
        unset($this->alias[$alias]);
        return $this;
    }

    /**
     * 设置指定键值以及参数到容器
     * @param $key 键
     * @param $obj 值，可能是闭包，或者类
     * @param ...$arg 参数
     */
    public function set($key, $obj,...$arg):void
    {
        /*
         * 注入的时候不做任何的类型检测与转换
         * 由于编程人员为问题，该注入资源并不一定会被用到
         */
        $this->container[$key] = [
            "obj"=>$obj,
            "params"=>$arg
        ];
    }

    /**
     * 删除容器中指定的键
     * @param $key 键名称
     * @return $this
     */
    function delete($key):Di
    {
        unset($this->container[$key]);
        return $this;
    }

    /**
     * 清空容器
     * @return $this
     */
    function clear():Di
    {
        $this->container = [];
        return $this;
    }

    /**
     * 获取指定键的值。如果值是对象，则立即返回；如果值是
     * @param $key
     * @return null
     * @throws \Throwable
     */
    function get($key)
    {
        if(isset($this->alias[$key])){
            $key = $this->alias[$key];
        }
        if(isset($this->container[$key])){
            $obj = $this->container[$key]['obj'];
            $params = $this->container[$key]['params'];
            if(is_object($obj) || is_callable($obj)){
                return $obj; // 返回对象
            }else if(is_string($obj) && class_exists($obj)){ // 如果值是类，则初始化该类对象，如果构造函数中还有其它的类，则依赖注入
                try{
                    $ref = new \ReflectionClass($obj);
                    if(empty($params)){
                        $constructor = $ref->getConstructor();
                        if($constructor){
                            $list = $constructor->getParameters();
                            foreach ($list as $p){
                                $class = $p->getClass();
                                if($class){
                                    $temp = $this->get($class->getName());
                                }else{
                                    $temp = $this->get($p->getName()) ?? $p->getDefaultValue();
                                }
                                $params[] = $temp;
                            }
                        }
                    }
                    $this->container[$key]['obj'] = $ref->newInstanceArgs($params); // 把字符串类替换为初始化的实例对象
                    return $this->container[$key]['obj'];
                }catch (\Throwable $throwable){
                    throw $throwable;
                }
            }else{
                return $obj;
            }
        }else{
            if(is_callable($this->onKeyMiss)){ // 如果键不存在，则调用回调函数来处理
                return call_user_func($this->onKeyMiss,$key);
            }
            return null;
        }
    }
}
