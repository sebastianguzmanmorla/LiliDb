<?php

namespace LiliDb\MySql\Types\Numeric;

use LiliDb\MySql\Types\FieldType;
use LiliDb\MySql\Types\FieldTypeEnum;

abstract class FieldTypeNumeric extends FieldType
{
    public function __construct(
        FieldTypeEnum $Type,
        public ?int $Length = null,
        public ?int $Decimal = null,
        public bool $Unsigned = false,
        public bool $Zerofill = false
    ) {
        $this->FieldType = $Type;
    }

    public function TypeDefinition(): string
    {
        $Decimal = $this->Decimal !== null ? ",{$this->Decimal}" : '';
        $Length = $this->Length !== null ? "({$this->Length}{$Decimal})" : '';
        $Unsigned = $this->Unsigned ? ' UNSIGNED' : '';
        $Zerofill = $this->Zerofill ? ' ZEROFILL' : '';

        return $this->FieldType->value . $Length . $Unsigned . $Zerofill;
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
