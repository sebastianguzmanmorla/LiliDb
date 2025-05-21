<?php

namespace LiliDb\MySql\Types\Time;

use LiliDb\MySql\Types\FieldType;
use LiliDb\MySql\Types\FieldTypeEnum;

abstract class FieldTypeTime extends FieldType
{
    public function __construct(
        FieldTypeEnum $Type,
        public ?int $Fraction = null,
    ) {
        $this->Type = $Type;
    }

    public function TypeDefinition(): string
    {
        $Fraction = $this->Fraction !== null ? "({$this->Fraction})" : '';

        return $this->Type->value . $Fraction;
    }
}
