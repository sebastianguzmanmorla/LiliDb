<?php

namespace LiliDb\MySql\Types\Numeric;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbFloat extends FieldTypeNumeric
{
    public function __construct(
        ?int $Length = null,
        ?int $Decimal = null,
        bool $Unsigned = false,
        bool $Zerofill = false
    ) {
        parent::__construct(Type: FieldTypeEnum::Float, Length: $Length, Decimal: $Decimal, Unsigned: $Unsigned, Zerofill: $Zerofill);
    }
}
