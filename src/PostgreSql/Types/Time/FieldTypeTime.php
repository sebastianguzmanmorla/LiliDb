<?php

namespace LiliDb\PostgreSql\Types\Time;

use LiliDb\PostgreSql\Types\FieldType;
use LiliDb\PostgreSql\Types\FieldTypeEnum;

abstract class FieldTypeTime extends FieldType
{
    public function __construct(
        FieldTypeEnum $Type,
        public ?int $Precision = null,
        public bool $WithTimeZone = false,
    ) {
        $this->Type = $Type;
    }

    public function TypeDefinition(): string
    {
        $Precision = $this->Precision !== null ? "({$this->Precision})" : '';
        $WithTimeZone = $this->WithTimeZone ? ' with time zone' : '';

        return $this->Type->value . $Precision . $WithTimeZone;
    }
}
