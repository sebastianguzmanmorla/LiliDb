<?php

namespace LiliDb\PostgreSql\Types\Numeric;

use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbReal extends FieldTypeNumeric
{
    public function __construct(
        ?int $Precision = null,
        ?int $Scale = null
    ) {
        parent::__construct(Type: FieldTypeEnum::Real, Precision: $Precision, Scale: $Scale);
    }
}
