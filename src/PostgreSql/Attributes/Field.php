<?php

namespace LiliDb\PostgreSql\Attributes;

use Attribute;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\ITable;
use LiliDb\PostgreSql\Types\FieldType;
use LiliDb\Token;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field implements IField
{
    public ITable $Table;

    public ReflectionProperty $FieldReflection;

    public function __construct(
        public FieldType $Type,
        public ?string $Name = null,
        public bool $AllowNull = false,
        public bool $PrimaryKey = false,
        public mixed $Default = null,
    ) {
    }

    public function FieldDefinition(): string
    {
        $AllowNull = $this->AllowNull ? ' NULL' : ' NOT NULL';

        if ($this->Default !== null) {
            $Value = $this->Default instanceof Token ? $this->Default : $this->Type->ToSql($this->Default);

            if ($Value instanceof Token) {
                $Default = " DEFAULT {$Value->value}";
            } elseif (is_string($Value)) {
                $Default = " DEFAULT '{$Value}'";
            } else {
                $Default = " DEFAULT {$Value}";
            }
        } else {
            $Default = '';
        }

        return "\"{$this->Name}\" {$this->Type->TypeDefinition()}{$AllowNull}{$Default}";
    }

    public function FieldReference(bool $FullReference): string
    {
        $TableName = $FullReference ? "\"{$this->Table->Name}\"." : '';

        return "{$TableName}\"{$this->Name}\"";
    }

    public function FieldSetValue(object $Class, mixed $Value): void
    {
        $Value = $this->Type->FromSql($Value);

        $this->FieldReflection->setValue($Class, $Value);
    }
}
