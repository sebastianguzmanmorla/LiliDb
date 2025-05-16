<?php

namespace LiliDb\PostgreSql\Types\Numeric;

use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbDecimal extends FieldTypeNumeric
{
    public function __construct(
        ?int $Precision = null,
        ?int $Scale = null
    ) {
        parent::__construct(Type: FieldTypeEnum::Decimal, Precision: $Precision, Scale: $Scale);
    }
}
