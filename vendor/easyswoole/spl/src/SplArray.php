<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/22
 * Time: 下午2:52
 */

namespace EasySwoole\Spl;

/**
 * 用于处理数组封装的基础工具
 */
class SplArray extends \ArrayObject
{
    /**
     * 以数组的形式访问键值
     * @param $name
     * @return false|mixed|null
     */
    function __get($name)
    {
        if (isset($this[$name])) {
            return $this[$name];
        } else {
            return null;
        }
    }

    /**
     * 以数组的形式设置键值
     * @param $name
     * @param $value
     */
    function __set($name, $value): void
    {
        $this[$name] = $value;
    }

    /**
     * 在需要字符串格式的地方，所有的数据以json字符串形式输出
     * @return string
     */
    function __toString(): string
    {
        return json_encode($this, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 返回SplArray对象的底层数组
     * @return array
     */
    function getArrayCopy(): array
    {
        return (array)$this;
    }

    /**
     * 设置指定路径的值，路径以点号分隔
     * @param $path 路径以点号分隔
     * @param $value
     */
    function set($path, $value): void
    {
        $path = explode(".", $path);
        $temp = $this;
        while ($key = array_shift($path)) {
            $temp = &$temp[$key];
        }
        $temp = $value;
    }

    /**
     * 删除指定路径的值
     * @param $path
     */
    function unset($path)
    {
        $finalKey = null;
        $path = explode(".", $path);
        $temp = $this;
        while (count($path) > 1 && $key = array_shift($path)) {
            $temp = &$temp[$key];
        }
        $finalKey = array_shift($path);
        if (isset($temp[$finalKey])) {
            unset($temp[$finalKey]);
        }
    }

    /**
     * 获取指定路径的值
     * @param $key 路径以点号分隔
     * @param $default 默认值
     * @param $target 可以指定从其它spl数组获取值
     * @return array|mixed|null
     */
    function get($key = null, $default = null,$target = null)
    {
        if($target == null){ // 如果没有指定存储目标，就使用当前类底层数组
            $target = $this->getArrayCopy();
        }
        if (is_null($key)) { // 如果没有传入key，则直接返回底层数组
            return $target;
        }
        // 如果传入的就是数组形式的路径，则不处理；如果传入的是点号连接的字符串，则需要按点号切分字符串
        $key = is_array($key) ? $key : explode('.', is_int($key) ? (string)$key : $key);
        while (!is_null($segment = array_shift($key))) { // 获取第一个路径
            // 如果获取的是数组，则通过索引访问
            if ((is_array($target) || $target instanceof \Traversable )&& isset($target[$segment])) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) { // 如果获取的是对象，则通过属性访问
                $target = $target->{$segment};
            } else { // 如果没有找到该路径，则可能是泛型语法
                if ($segment === '*') { // 支持中间路径泛型语法，这样会查询该级的所有数据
                    $data = [];
                    foreach ($target as $item) {
                        $data[] = self::get($key, $default,$item);
                    }
                    return $data;
                } else { // 最后还是没有找到，则返回默认值
                    return $default;
                }
            }
        }
        return $target; // 第一个路径都没有找到，则直接返回底层数组
    }

    /**
     * 删除指定的键值
     * @param $key
     */
    public function delete($key): void
    {
        $this->unset($key);
    }

    /**
     * 数组去重取唯一的值
     * @return SplArray
     */
    public function unique(): SplArray
    {
        return new SplArray(array_unique($this->getArrayCopy(), SORT_REGULAR));
    }

    /**
     * 获取数组中重复的值
     * @return SplArray
     */
    public function multiple(): SplArray
    {
        $unique_arr = array_unique($this->getArrayCopy(), SORT_REGULAR);
        return new SplArray(array_udiff_uassoc($this->getArrayCopy(), $unique_arr, function ($key1, $key2) {
            if ($key1 === $key2) {
                return 0;
            }
            return 1;
        }, function ($value1, $value2) {
            if ($value1 === $value2) {
                return 0;
            }
            return 1;
        }));
    }

    /**
     * 通过值排序
     * @param $flags
     * @return bool
     */
    public function asort($flags = SORT_REGULAR): bool
    {
        return parent::asort($flags);
    }

    /**
     * 通过键排序
     * @param $flags
     * @return bool
     */
    public function ksort($flags = SORT_REGULAR): bool
    {
        return parent::ksort($flags);
    }

    /**
     * 自定义排序，不会影响底层数组的顺序
     * @param int $sort_flags
     * @return SplArray
     */
    public function sort($sort_flags = SORT_REGULAR): SplArray
    {
        $temp = $this->getArrayCopy();
        sort($temp, $sort_flags);
        return new SplArray($temp);
    }

    /**
     * 取得某一列
     * @param string      $column
     * @param null|string $index_key
     * @return SplArray
     */
    public function column($column, $index_key = null): SplArray
    {
        return new SplArray(array_column($this->getArrayCopy(), $column, $index_key));
    }

    /**
     * 交换数组中的键和值
     * @return SplArray
     */
    public function flip(): SplArray
    {
        return new SplArray(array_flip($this->getArrayCopy()));
    }

    /**
     * 过滤本数组
     * @param string|array $keys 需要取得/排除的键
     * @param bool         $exclude true则排除设置的键名 false则仅获取设置的键名
     * @return SplArray
     */
    public function filter($keys, $exclude = false): SplArray
    {
        if (is_string($keys)) {
            $keys = explode(',', $keys);
        }
        $new = array();
        foreach ($this->getArrayCopy() as $name => $value) {
            if (!$exclude) {
                in_array($name, $keys) ? $new[$name] = $value : null;
            } else {
                in_array($name, $keys) ? null : $new[$name] = $value;
            }
        }
        return new SplArray($new);
    }

    /**
     * 获取指定路径数组对象的所有键
     * @param $path
     * @return array
     */
    public function keys($path = null): array
    {
        if (!empty($path)) {
            $temp = $this->get($path);
            if (is_array($temp)) {
                return array_keys($temp);
            } else {
                return [];
            }
        }
        return array_keys((array)$this);
    }

    /**
     * 提取数组中的值
     * @return SplArray
     */
    public function values(): SplArray
    {
        return new SplArray(array_values($this->getArrayCopy()));
    }

    /**
     * 清空底层所有内容
     * @return $this
     */
    public function flush(): SplArray
    {
        foreach ($this->getArrayCopy() as $key => $item) {
            unset($this[$key]);
        }
        return $this;
    }

    /**
     * 重新加载数据
     * @param array $data
     * @return $this
     */
    public function loadArray(array $data)
    {
        parent::__construct($data);
        return $this;
    }

    /**
     * 批量追加数据
     * @param array $data
     * @return $this
     */
    function merge(array $data)
    {
        return $this->loadArray($data + $this->getArrayCopy());
    }

    /*
     * 转换为xml数据格式
     $test = new \EasySwoole\Spl\SplArray([
        'title'=>'title',
        'items'=>[
            ['title'=>'Some string', 'number' => 1],
            ['title'=>'Some string', 'number' => 2],
            ['title'=>'Some string', 'number' => 3]
        ]
    ]);
     */
    public function toXML($CD_DATA = false, $rootName = 'xml', $encoding = 'UTF-8', $item = 'item')
    {
        $data = $this->getArrayCopy();
        if ($CD_DATA) {
            /*
             * 默认制定
             */
            $xml = new class('<?xml version="1.0" encoding="' . $encoding . '" ?>' . "<{$rootName}></{$rootName}>") extends \SimpleXMLElement
            {
                public function addCData($cdata_text)
                {
                    $dom = dom_import_simplexml($this);
                    $cdata = $dom->ownerDocument->createCDATASection($cdata_text);
                    $dom->appendChild($cdata);
                }
            };
        } else {
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="' . $encoding . '" ?>' . "<{$rootName} ></{$rootName}>");
        }
        $parser = function ($xml, $data) use (&$parser, $CD_DATA, $item) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    if (!is_numeric($k)) {
                        $ch = $xml->addChild($k);
                    } else {
                        $ch = $xml->addChild($item);
                    }
                    $parser($ch, $v);
                } else {
                    if (is_numeric($k)) {
                        $xml->addChild($k, $v);
                    } else {
                        if ($CD_DATA) {
                            $n = $xml->addChild($k);
                            $n->addCData($v);
                        } else {
                            $xml->addChild($k, $v);
                        }
                    }
                }
            }
        };
        $parser($xml, $data);
        unset($parser);
        $str = $xml->asXML();
        return substr($str, strpos($str, "\n") + 1);
    }

}
