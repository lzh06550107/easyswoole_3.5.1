<?php

namespace EasySwoole\Validate;

use EasySwoole\Spl\SplArray;
use EasySwoole\Validate\Exception\Runtime;
use EasySwoole\Validate\Functions\AbstractValidateFunction;
use EasySwoole\Validate\Functions\ActiveUrl;
use EasySwoole\Validate\Functions\AllDigital;
use EasySwoole\Validate\Functions\AllowFile;
use EasySwoole\Validate\Functions\AllowFileType;
use EasySwoole\Validate\Functions\Alpha;
use EasySwoole\Validate\Functions\AlphaDash;
use EasySwoole\Validate\Functions\AlphaNum;
use EasySwoole\Validate\Functions\Between;
use EasySwoole\Validate\Functions\BetweenLen;
use EasySwoole\Validate\Functions\BetweenMbLen;
use EasySwoole\Validate\Functions\DateAfter;
use EasySwoole\Validate\Functions\DateBefore;
use EasySwoole\Validate\Functions\Decimal;
use EasySwoole\Validate\Functions\Different;
use EasySwoole\Validate\Functions\DifferentWithColumn;
use EasySwoole\Validate\Functions\Equal;
use EasySwoole\Validate\Functions\EqualWithColumn;
use EasySwoole\Validate\Functions\Func;
use EasySwoole\Validate\Functions\GreaterThanWithColumn;
use EasySwoole\Validate\Functions\InArray;
use EasySwoole\Validate\Functions\Integer;
use EasySwoole\Validate\Functions\IsArray;
use EasySwoole\Validate\Functions\IsBool;
use EasySwoole\Validate\Functions\IsFloat;
use EasySwoole\Validate\Functions\IsIp;
use EasySwoole\Validate\Functions\Length;
use EasySwoole\Validate\Functions\LengthMax;
use EasySwoole\Validate\Functions\LengthMin;
use EasySwoole\Validate\Functions\LessThanWithColumn;
use EasySwoole\Validate\Functions\Max;
use EasySwoole\Validate\Functions\Min;
use EasySwoole\Validate\Functions\Money;
use EasySwoole\Validate\Functions\MbLength;
use EasySwoole\Validate\Functions\MbLengthMax;
use EasySwoole\Validate\Functions\MbLengthMin;
use EasySwoole\Validate\Functions\NotEmpty;
use EasySwoole\Validate\Functions\NotInArray;
use EasySwoole\Validate\Functions\Numeric;
use EasySwoole\Validate\Functions\Optional;
use EasySwoole\Validate\Functions\Regex;
use EasySwoole\Validate\Functions\Required;
use EasySwoole\Validate\Functions\Timestamp;
use EasySwoole\Validate\Functions\TimestampAfter;
use EasySwoole\Validate\Functions\TimestampAfterDate;
use EasySwoole\Validate\Functions\TimestampBefore;
use EasySwoole\Validate\Functions\TimestampBeforeDate;
use EasySwoole\Validate\Functions\Url;

/**
 * 数据验证器
 * Class Validate
 */
class Validate
{
    protected $columns = []; // 要验证的列项

    /** @var null|Error */
    protected $error; // 验证失败的错误对象

    protected $verifiedData = []; // 要被验证的数据，SplArray封装

    /** @var null|SplArray */
    protected $verifyData;

    protected $functions = [];

    /**
     * Validate constructor.
     * @throws Runtime
     */
    public function __construct()
    {
        $this->addFunction(new ActiveUrl());
        $this->addFunction(new AllDigital());
        $this->addFunction(new AllowFile());
        $this->addFunction(new AllowFileType());
        $this->addFunction(new Alpha());
        $this->addFunction(new AlphaNum());
        $this->addFunction(new AlphaDash());
        $this->addFunction(new Between());
        $this->addFunction(new BetweenLen());
        $this->addFunction(new BetweenMbLen());
        $this->addFunction(new DateAfter());
        $this->addFunction(new DateBefore());
        $this->addFunction(new Decimal());
        $this->addFunction(new Different());
        $this->addFunction(new DifferentWithColumn());
        $this->addFunction(new Equal());
        $this->addFunction(new EqualWithColumn());
        $this->addFunction(new Func());
        $this->addFunction(new GreaterThanWithColumn());
        $this->addFunction(new InArray());
        $this->addFunction(new Integer());
        $this->addFunction(new IsArray());
        $this->addFunction(new IsBool());
        $this->addFunction(new IsFloat());
        $this->addFunction(new IsIp());
        $this->addFunction(new Length());
        $this->addFunction(new LengthMax());
        $this->addFunction(new LengthMin());
        $this->addFunction(new LessThanWithColumn());
        $this->addFunction(new Max());
        $this->addFunction(new Min());
        $this->addFunction(new Money());
        $this->addFunction(new MbLength());
        $this->addFunction(new MbLengthMax());
        $this->addFunction(new MbLengthMin());
        $this->addFunction(new NotEmpty());
        $this->addFunction(new NotInArray());
        $this->addFunction(new Numeric());
        $this->addFunction(new Optional());
        $this->addFunction(new Regex());
        $this->addFunction(new Required());
        $this->addFunction(new Timestamp());
        $this->addFunction(new TimestampAfter());
        $this->addFunction(new TimestampAfterDate());
        $this->addFunction(new TimestampBefore());
        $this->addFunction(new TimestampBeforeDate());
        $this->addFunction(new Url());
    }

    /**
     * 获取验证错误
     * @return Error|null
     */
    public function getError(): ?Error
    {
        return $this->error;
    }

    /**
     * 获取要验证的数据
     * @return SplArray|null
     */
    public function getVerifyData(): ?SplArray
    {
        return $this->verifyData;
    }

    /**
     * 添加一个待验证字段
     * @param string $name 验证字段名称
     * @param string|null $alias 字段别名，显示错误信息时使用
     * @param bool $reset 是否重置验证字段
     * @return Rule 验证规则
     */
    public function addColumn(string $name, ?string $alias = null, bool $reset = false): Rule
    {
        if (!isset($this->columns[$name]) || $reset) {
            $rule = new Rule(); // 规则注册表
            $this->columns[$name] = [
                'alias' => $alias,
                'rule' => $rule,
            ];
        }

        return $this->columns[$name]['rule'];
    }

    /**
     * 删除一个待验证字段
     */
    public function delColumn(string $name)
    {
        if (isset($this->columns[$name])) {
            unset($this->columns[$name]);
        }
    }

    /**
     * 获取一个待验证字段
     */
    public function getColumn(string $name): array
    {
        return $this->columns[$name] ?? [];
    }

    /**
     * 静态方法快速创建一个验证器对象
     * @param array $rules 验证规则
     * @param array $message 验证错误消息提示
     * @param array $alias 验证字段的别名
     * @return static
     * @throws Runtime
     */
    public static function make(array $rules = [], array $message = [], array $alias = []): self
    {
        $errMsgMap = [];
        // eg: msgMap[field][rule] => msg

        foreach ($message as $field => $msg) {
            // eg: field.required

            $pos = strrpos($field, '.');
            if ($pos === false) {
                // No validation rules will reset all error messages
                $errMsgMap[$field] = $msg;
                continue;
            }

            $fieldName = substr($field, 0, $pos); // 字段名称
            $fieldRule = substr($field, $pos + 1); // 字段规则

            if (!$fieldName) {
                throw new Runtime(sprintf('Error message[%s] does not specify a field', $msg));
            }

            if ($fieldRule) {
                $errMsgMap[$fieldName][$fieldRule] = $msg;
                continue;
            }

            // No validation rules will reset all error messages
            $errMsgMap[$fieldName] = $msg;
        }

        $instance = new static();
        foreach ($rules as $key => $rule) {
            if (!$key) {
                throw new Runtime('The verified field is empty');
            }

            /** @var Rule $validateRule */
            $validateRule = $instance->addColumn($key, $alias[$key] ?? null);
            // eg: rule 'required|max:25|between:1,100'
            $rule = explode('|', $rule); // 切分多个验证规则
            foreach ($rule as $action) {
                $actionArgs = [];

                if (strpos($action, ':')) { // 获取验证规则参数字符串
                    // eg max:25
                    list($action, $arg) = explode(':', $action, 2);

                    if (!strpos($arg, ',')) {
                        $actionArgs[] = $arg;
                    } else {
                        // eg between:1,100
                        $arg = explode(',', $arg); // 获取验证规则各个参数
                        $actionArgs = array_merge($actionArgs, $arg);
//                        $actionArgs[] = $arg;
                    }
                }

                $errMsg = $errMsgMap[$key] ?? null;
                if (is_array($errMsg)) {
                    $errMsg = $errMsg[$action] ?? null;
                }

                $actionArgs[] = $errMsg;
                $validateRule->{$action}(...$actionArgs); // 调用验证规则
            }
        }

        return $instance;
    }

    /**
     * 验证字段是否合法
     * @param array $data
     * @return bool
     * @throws Runtime
     */
    public function validate(array $data): bool
    {
        $this->verifiedData = [];
        $spl = new SplArray($data);
        $this->verifyData = $spl;

        foreach ($this->columns as $column => $item) {
            $columnData = $spl->get($column); // 获取该列验证项
            $ruleMap = $item['rule']->getRuleMap(); // 获取注册的所有验证规则
            //多维数组
            if (strpos($column, '*') !== false && is_array($columnData)) {
                foreach ($columnData as $datum) {
                    if ($this->runRule($datum, $ruleMap, $column)) {
                        return false;
                    }
                }
            } else {
                if ($this->runRule($columnData, $ruleMap, $column)) {
                    return false;
                }
            }
            $this->verifiedData[$column] = $columnData;
        }

        return true;
    }

    /**
     * 运行规则
     * @param $itemData
     * @param $rules
     * @param $column
     * @return null|Error
     * @throws Runtime
     */
    private function runRule($itemData, $rules, $column): ?Error
    {
        if (isset($rules['optional']) && ($itemData === null || $itemData === '')) {
            return null;
        }
        
        foreach ($rules as $rule => $ruleConf) {
            
            $check = strtolower($rule);

            if (!isset($this->functions[$check])) { // 验证是否存在该规则
                throw new Runtime("unsupport rule {$rule}");
            }

            /** @var AbstractValidateFunction $func */
            $func = $this->functions[$check];
            if ($func->validate($itemData, $ruleConf['arg'], $column, $this) === false) {
                $this->error = new Error( // 错误对象
                    $column,
                    $itemData,
                    $this->columns[$column]['alias'],
                    $rule,
                    $ruleConf['msg'],
                    $ruleConf['arg'],
                    $this
                );

                return $this->error;
            }
        }

        return null;
    }

    /**
     * 获取验证成功后的数据
     */
    public function getVerifiedData(): array
    {
        return $this->verifiedData;
    }

    /**
     * 直接添加验证函数
     * @param AbstractValidateFunction $function
     * @param bool $overlay 是否允许覆盖
     * @return $this
     * @throws Runtime
     */
    public function addFunction(AbstractValidateFunction $function, bool $overlay = false): Validate
    {
        if (isset($this->functions[strtolower($function->name())]) && $overlay === false) {
            throw new Runtime(sprintf('This validate function [%s] already exists', $function->name()));
        }

        $this->functions[strtolower($function->name())] = $function;

        return $this;
    }
}
