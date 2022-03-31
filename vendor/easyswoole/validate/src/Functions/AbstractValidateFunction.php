<?php

namespace EasySwoole\Validate\Functions;

use EasySwoole\Validate\Validate;

/**
 * 验证规则抽象类
 */
abstract class AbstractValidateFunction
{
    /**
     * 校验规则的名字
     * @return string
     */
    abstract public function name(): string;

    /**
     * 验证失败返回 false，或者用户可以抛出异常，验证成功返回 true
     * @param $itemData
     * @param $arg
     * @param $column
     * @param Validate $validate
     * @return bool
     */
    abstract public function validate($itemData, $arg, $column, Validate $validate): bool;
}
