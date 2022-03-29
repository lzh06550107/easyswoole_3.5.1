<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/22
 * Time: 下午2:53
 */

namespace EasySwoole\Spl;

use EasySwoole\Spl\Exception\Exception;

/*
 * 可序列化对象类
 * 仅能获取 protected 和 public 成员变量
 * 用于定义表结构，过滤掉无效字段数据
 */
class SplBean implements \JsonSerializable
{
    const FILTER_NOT_NULL = 1;
    const FILTER_NOT_EMPTY = 2;
    const FILTER_NULL = 3;
    const FILTER_EMPTY = 4;

    /**
     * 构造函数，初始化bean数据
     * @param array|null $data
     * @param $autoCreateProperty
     * @throws Exception
     */
    public function __construct(array $data = null, $autoCreateProperty = false)
    {
        if ($data) {
            $this->arrayToBean($data, $autoCreateProperty);
        }
        $this->initialize();
        $this->classMap();
    }

    /**
     * 收集类的public和protected属性
     * @return array
     */
    final public function allProperty(): array
    {
        $data = [];
        $class = new \ReflectionClass($this);
        $protectedAndPublic = $class->getProperties(
            \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED
        );
        foreach ($protectedAndPublic as $item) {
            if ($item->isStatic()) {
                continue;
            }
            array_push($data, $item->getName());
        }
        $data = array_flip($data);
        unset($data['_keyMap']);
        unset($data['_classMap']);
        return array_flip($data); // 删除重复元素
    }

    /**
     * 过滤并转换成数组数据
     * @param array|null $columns 键过滤
     * @param $filter 值过滤
     * @return array
     */
    function toArray(array $columns = null, $filter = null): array
    {
        $data = $this->jsonSerialize();
        if ($columns) {
            $data = array_intersect_key($data, array_flip($columns)); // 键过滤
        }
        if ($filter === self::FILTER_NOT_NULL) { // 过滤null值
            return array_filter($data, function ($val) {
                return !is_null($val);
            });
        } else if ($filter === self::FILTER_NOT_EMPTY) { // 过滤 null,'',0,false.[]等
            return array_filter($data, function ($val) {
                return !empty($val);
            });
        } else if ($filter === self::FILTER_NULL) { // 过滤非null值
            return array_filter($data, function ($val) {
                return is_null($val);
            });
        } else if ($filter === self::FILTER_EMPTY) { // 过滤
            return array_filter($data, function ($val) {
                return empty($val);
            });
        } else if (is_callable($filter)) { // 自定义过滤函数
            return array_filter($data, $filter);
        }
        return $data;
    }

    /*
     * 获取过滤后带有字段别名的数组数据
     * 返回转化后的array
     */
    function toArrayWithMapping(array $columns = null, $filter = null)
    {
        $array = $this->toArray();
        $array = $this->beanKeyMap($array);

        if ($columns) {
            $array = array_intersect_key($array, array_flip($columns));
        }
        if ($filter === self::FILTER_NOT_NULL) {
            return array_filter($array, function ($val) {
                return !is_null($val);
            });
        } else if ($filter === self::FILTER_NOT_EMPTY) {
            return array_filter($array, function ($val) {
                if ($val === 0 || $val === '0') { // 0不算空
                    return true;
                } else {
                    return !empty($val);
                }
            });
        } else if (is_callable($filter)) {
            return array_filter($array, $filter);
        }
        return $array;
    }

    /**
     * 使用数组数据来初始化bean属性
     * @param array $data 需要赋值的数组
     * @param $autoCreateProperty // 是否需要根据已有的属性来赋值
     * @return SplBean
     */
    private function arrayToBean(array $data, $autoCreateProperty = false): SplBean
    {

        $data = $this->dataKeyMap($data);

        if ($autoCreateProperty == false) { // 是否需要根据已有的属性来赋值
            $data = array_intersect_key($data, array_flip($this->allProperty())); // 获取双方都存在的key
        }
        foreach ($data as $key => $item) {
            $this->addProperty($key, $item); // 动态创建属性
        }
        return $this;
    }

    /**
     * 给对象添加属性
     * @param $name
     * @param $value
     */
    final public function addProperty($name, $value = null): void
    {
        $this->$name = $value;
    }

    /**
     * 获取对象属性
     * @param $name
     * @return null
     */
    final public function getProperty($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return null;
        }
    }

    /**
     * 收集json系列化字段
     * @return array
     */
    final public function jsonSerialize(): array
    {
        $data = [];
        foreach ($this as $key => $item) {
            $data[$key] = $item;
        }
        unset($data['_keyMap']);
        unset($data['_classMap']);
        return $data;
    }

    /**
     * 以json字符串输出
     * @return false|string
     */
    public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /*
     * 在子类中重写该方法，可以在类初始化的时候进行一些操作
     */
    protected function initialize(): void
    {

    }

    /*
     * 如果需要用到keyMap  请在子类重构并返回对应的map数据，就是改变数组key到对象属性的名称，设置classMapping关系，也就是关联类
     * return ['beanKey'=>'dataKey']
     * return ['实际的键名'=>'传人的键名']
     */
    protected function setKeyMapping(): array
    {
        return [];
    }

    /*
     * 设置keyMapping关系，也就是字段别名
     * return ['property'=>class string]
     */
    protected function setClassMapping(): array
    {
        return [];
    }

    /*
     * 恢复到属性定义的默认值，即重新初始化bean数据
     */
    public function restore(array $data = [], $autoCreateProperty = false)
    {
        $this->clear();
        // 如果合并的数组中有相同的字符串键名，则取最先出现的值而把后面拥有相同键名的那些值“抛弃”
        $this->arrayToBean($data + get_class_vars(static::class), $autoCreateProperty);
        $this->initialize();
        $this->classMap(); // 对象属性是对象的可能需要重新实例化
        return $this;
    }

    /**
     * 给对象批量追加属性
     * @param array $data
     * @return $this
     */
    function merge(array $data)
    {
        $this->arrayToBean($data);
        return $this;
    }

    /**
     * 清理类中不是原生定义的属性
     */
    private function clear()
    {
        $keys = $this->allProperty();
        $ref = new \ReflectionClass(static::class); // 反射子类
        $fields = array_keys($ref->getDefaultProperties()); // 默认属性的数组，其键是属性的名称，其值是属性的默认值或者在属性没有默认值时是 NULL。 这个函数不区分静态和非静态属性，也不考虑可见性修饰符
        $fields = array_merge($fields, array_values($this->setKeyMapping()));
        // 多余的key
        $extra = array_diff($keys, $fields);

        foreach ($extra as $key => $value) {
            unset($this->$value);
        }
    }

    private function classMap()
    {
        $propertyList = $this->allProperty();
        foreach ($this->setClassMapping() as $property => $class) {
            if (in_array($property, $propertyList)) {
                $val = $this->$property;
                $force = true;
                if (strpos($class, '@') !== false) {
                    $force = false;
                    $class = substr($class, 1);
                }
                if (is_object($val)) {
                    if (!$val instanceof $class) {
                        throw new Exception("value for property:{$property} dot not match in " . (static::class));
                    }
                } else if ($val === null) {
                    if ($force) {
                        $this->$property = $this->createClass($class); // 重新初始化类对象
                    }
                } else {
                    $this->$property = $this->createClass($class, $val);
                }
            } else {
                throw new Exception("property:{$property} not exist in " . (static::class));
            }
        }
    }

    /**
     * 实例化类对象
     * @param string $class
     * @param null   $arg
     * @return object
     * @throws \ReflectionException
     */
    private function createClass(string $class, $arg = null)
    {
        $ref = new \ReflectionClass($class);
        return $ref->newInstance($arg);
    }

    /**
     * beanKeyMap
     * 将Bean的属性名转化为data数据键名
     *
     * @param array $array
     * @return array
     */
    private function beanKeyMap(array $array): array
    {
        foreach ($this->setKeyMapping() as $dataKey => $beanKey) {
            if (array_key_exists($beanKey, $array)) {
                $array[$dataKey] = $array[$beanKey];
                unset($array[$beanKey]);
            }
        }
        return $array;
    }

    /**
     * dataKeyMap
     * 将data中的键名 转化为Bean的属性名
     *
     * @param array $array
     * @return array
     */
    private function dataKeyMap(array $array): array
    {
        foreach ($this->setKeyMapping() as $dataKey => $beanKey) {
            if (array_key_exists($dataKey, $array)) {
                $array[$beanKey] = $array[$dataKey]; // 根据键映射修改键
                unset($array[$dataKey]);
            }
        }
        return $array;
    }
}
