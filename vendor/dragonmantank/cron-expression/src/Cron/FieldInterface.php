<?php

namespace Cron;

use DateTimeInterface;

/**
 * CRON field interface，CRON字段接口
 */
interface FieldInterface
{
    /**
     * Check if the respective value of a DateTime field satisfies a CRON exp
     * 检查 DateTime 字段的相应值是否满足 CRON exp
     *
     * @param DateTimeInterface $date  DateTime object to check
     * @param string            $value CRON expression to test against
     *
     * @return bool Returns TRUE if satisfied, FALSE otherwise
     */
    public function isSatisfiedBy(DateTimeInterface $date, $value);

    /**
     * When a CRON expression is not satisfied, this method is used to increment
     * or decrement a DateTime object by the unit of the cron field
     * 当一个 CRON 表达式不满足时，该方法用于以 cron 字段为单位递增或递减一个 DateTime 对象
     *
     * @param DateTimeInterface &$date  DateTime object to change
     * @param bool              $invert (optional) Set to TRUE to decrement
     *
     * @return FieldInterface
     */
    public function increment(DateTimeInterface &$date, $invert = false);

    /**
     * Validates a CRON expression for a given field
     * 验证给定字段的 CRON 表达式
     *
     * @param string $value CRON expression value to validate
     *
     * @return bool Returns TRUE if valid, FALSE otherwise
     */
    public function validate($value);
}
