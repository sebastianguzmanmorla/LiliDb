<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldType;
use LiliDb\MySql\Types\FieldTypeEnum;

class FieldTypeString extends FieldType
{
    public function __construct(
        FieldTypeEnum $Type,
        public ?int $Length = null,
    ) {
        $this->Type = $Type;
    }

    public function TypeDefinition(): string
    {
        $Length = $this->Length !== null ? "({$this->Length})" : '';

        return $this->Type->value . $Length;
    }

    public function FromSql(mixed $Value): mixed
    {
        return $Value !== null ? (string) $Value : null;
    }

    public function ToSql(mixed $Value): mixed
    {
        return $Value !== null ? (string) $Value : null;
    }
}
