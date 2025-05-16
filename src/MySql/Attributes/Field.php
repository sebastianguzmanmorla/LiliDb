<?php

namespace LiliDb\MySql\Attributes;

use Attribute;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\ITable;
use LiliDb\MySql\Types\FieldType;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field implements IField
{
    public ITable $Table;

    public ReflectionProperty $FieldReflection;

    public function __construct(
        public FieldType $FieldType,
        public ?string $FieldName = null,
        public bool $FieldAllowNull = false,
        public bool $FieldPrimaryKey = false,
        public bool $FieldAutoIncrement = false
    ) {
    }

    public function FieldDefinition(): string
    {
        $AllowNull = $this->FieldAllowNull ? ' NULL' : ' NOT NULL';
        $AutoIncrement = $this->FieldAutoIncrement ? ' AUTO_INCREMENT' : '';

        return "`{$this->FieldName}` {$this->FieldType->TypeDefinition()}{$AllowNull}{$AutoIncrement}";
    }

    public function FieldReference(bool $FullReference): string
    {
        $TableName = $FullReference ? "`{$this->Table->TableName}`." : '';

        return "{$TableName}`{$this->FieldName}`";
    }

    public function FieldSetValue(object $Class, mixed $Value): void
    {
        $Value = $this->FieldType->FromSql($Value);

        $this->FieldReflection->setValue($Class, $Value);
    }
}
