<?php

namespace LiliDb\PostgreSql\Types\Numeric;

use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbNumeric extends FieldTypeNumeric
{
    public function __construct(
        ?int $Precision = null,
        ?int $Scale = null
    ) {
        parent::__construct(Type: FieldTypeEnum::Numeric, Precision: $Precision, Scale: $Scale);
    }
}
