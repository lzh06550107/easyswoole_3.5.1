<?php

namespace EasySwoole\DDL\Blueprint\Create;

use \EasySwoole\DDL\Enum\Foreign as ForeignType;
use InvalidArgumentException;

/**
 * 外键构造器
 * Class Foreign
 * @package EasySwoole\DDL\Blueprint\Create
 */
class Foreign
{
    protected $foreignName;         // 外键名称
    protected $localColumn;         // 从表字段
    protected $relatedTableName;    // 主表表名
    protected $foreignColumn;       // 主表字段
    protected $onDelete;            // 主表删除操作从表动作
    protected $onUpdate;            // 主表更新操作从表动作

    protected $ForeignOption = [
        ForeignType::CASCADE, // 从父表中删除或更新对应的行，同时自动的删除或更新从表中匹配的行。ON DELETE CANSCADE和ON UPDATE CANSCADE都被InnoDB所支持
        ForeignType::NO_ACTION, // InnoDB拒绝删除或者更新父表
        ForeignType::RESTRICT, // 如果想要删除父表的记录时，而在子表中有关联该父表的记录，则不允许删除父表中的记录
        ForeignType::SET_NULL, // 从父表中删除或更新对应的行，同时将从表中的外键列设为空。注意，这些在外键列没有被设为NOT NULL时才有效。ON DELETE SET NULL和ON UPDATE SET SET NULL都被InnoDB所支持
    ];

    /**
     * Foreign constructor.
     * @param string|null $foreignName
     * @param string|array $localColumn
     * @param string $relatedTableName
     * @param string|array $foreignColumn
     */
    public function __construct(?string $foreignName, $localColumn, string $relatedTableName, $foreignColumn)
    {
        $this->setForeignName($foreignName);
        $this->setLocalColumn($localColumn);
        $this->setRelatedTableName($relatedTableName);
        $this->setForeignColumn($foreignColumn);
    }

    /**
     * 设置外键名称
     * @param string|null $foreignName
     * @return Foreign
     */
    public function setForeignName(?string $foreignName = null): Foreign
    {
        $this->foreignName = is_string($foreignName) ? trim($foreignName) : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getForeignName()
    {
        return $this->foreignName;
    }

    /**
     * 设置从表字段
     * @param string|array $localColumn
     * @return Foreign
     */
    public function setLocalColumn($localColumn): Foreign
    {
        $localColumn = is_string($localColumn) ? trim($localColumn) : $localColumn;
        if (empty($localColumn)) {
            throw new InvalidArgumentException('localColumn is miss');
        }
        $this->localColumn = $localColumn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocalColumn()
    {
        return $this->localColumn;
    }

    /**
     * 设置主表表名
     * @param string $relatedTableName
     * @return Foreign
     */
    public function setRelatedTableName(string $relatedTableName): Foreign
    {
        $relatedTableName = trim($relatedTableName);
        if (empty($relatedTableName)) {
            throw new InvalidArgumentException('relatedTableName is miss');
        }
        $this->relatedTableName = $relatedTableName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelatedTableName()
    {
        return $this->relatedTableName;
    }

    /**
     * 设置主表字段
     * @param string|array $foreignColumn
     * @return Foreign
     */
    public function setForeignColumn($foreignColumn): Foreign
    {
        $foreignColumn = is_string($foreignColumn) ? trim($foreignColumn) : $foreignColumn;
        if (empty($foreignColumn)) {
            throw new InvalidArgumentException('foreignColumn is miss');
        }
        $this->foreignColumn = $foreignColumn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getForeignColumn()
    {
        return $this->foreignColumn;
    }

    /**
     * 主表删除操作从表动作
     * \EasySwoole\DDL\Enum\Foreign
     * @param string $option
     * @return Foreign
     */
    public function setOnDelete(string $option)
    {
        $option = trim($option);
        if (!in_array($option, $this->ForeignOption)) {
            throw new InvalidArgumentException('on delete option is invalid');
        }
        $this->onDelete = $option;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOnDelete()
    {
        return $this->onDelete;
    }

    /**
     * 主表更新操作从表动作
     * \EasySwoole\DDL\Enum\Foreign
     * @param string $option
     * @return Foreign
     */
    public function setOnUpdate(string $option)
    {
        $option = trim($option);
        if (!in_array($option, $this->ForeignOption)) {
            throw new InvalidArgumentException('on update option is invalid');
        }
        $this->onUpdate = $option;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOnUpdate()
    {
        return $this->onUpdate;
    }

    /**
     * 组装主表字段名
     * @return string
     */
    public function parseLocalColumns()
    {
        $columnDDLs   = [];
        $localColumns = $this->getLocalColumn();
        if (is_string($localColumns)) {
            $localColumns = [$localColumns];
        }
        foreach ($localColumns as $localColumn) {
            $columnDDLs[] = '`' . $localColumn . '`';
        }
        return '(' . implode(',', $columnDDLs) . ')';
    }

    /**
     * 组装外键字段名
     * @return string
     */
    public function parseForeignColumns()
    {
        $columnDDLs   = [];
        $foreignColumns = $this->getForeignColumn();
        if (is_string($foreignColumns)) {
            $foreignColumns = [$foreignColumns];
        }
        foreach ($foreignColumns as $foreignColumn) {
            $columnDDLs[] = '`' . $foreignColumn . '`';
        }
        return '(' . implode(',', $columnDDLs) . ')';
    }

    /**
     * 生成索引DDL结构
     * 带有下划线的方法请不要自行调用
     * @return string
     */
    public function __createDDL()
    {
        return implode(' ',
            array_filter(
                [
                    $this->getForeignName() ? "CONSTRAINT `{$this->getForeignName()}`" : null,
                    "FOREIGN KEY",
                    $this->parseLocalColumns(),
                    "REFERENCES `{$this->getRelatedTableName()}`",
                    $this->parseForeignColumns(),
                    $this->getOnDelete() ? "ON DELETE {$this->getOnDelete()}" : null,
                    $this->getOnUpdate() ? "ON UPDATE {$this->getOnUpdate()}" : null,
                ]
            )
        );
    }

    /**
     * 转化为字符串
     * @return string
     */
    public function __toString()
    {
        return $this->__createDDL();
    }
}