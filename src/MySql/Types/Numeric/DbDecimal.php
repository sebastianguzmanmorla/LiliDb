<?php

namespace LiliDb\MySql\Types\Numeric;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbDecimal extends FieldTypeNumeric
{
    public function __construct(
        ?int $Length = null,
        ?int $Decimal = null,
        bool $Unsigned = false,
        bool $Zerofill = false
    ) {
        parent::__construct(Type: FieldTypeEnum::Decimal, Length: $Length, Decimal: $Decimal, Unsigned: $Unsigned, Zerofill: $Zerofill);
    }
}
