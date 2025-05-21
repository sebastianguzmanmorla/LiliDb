<?php

namespace LiliDb\PostgreSql\Types\Numeric;

use LiliDb\PostgreSql\Types\FieldType;
use LiliDb\PostgreSql\Types\FieldTypeEnum;

abstract class FieldTypeNumeric extends FieldType
{
    public function __construct(
        FieldTypeEnum $Type,
        public ?int $Precision = null,
        public ?int $Scale = null
    ) {
        $this->Type = $Type;
    }

    public function TypeDefinition(): string
    {
        $Scale = $this->Scale !== null ? ",{$this->Scale}" : '';
        $Precision = $this->Precision !== null ? "({$this->Precision}{$Scale})" : '';

        return $this->Type->value . $Precision;
    }

    public function FromSql(mixed $Value): mixed
    {
        return $Value !== null ? $Value + 0 : null;
    }

    public function ToSql(mixed $Value): mixed
    {
        if (is_numeric($Value)) {
            return $Value + 0;
        }

        return null;
    }
}
