<?php

namespace LiliDb\PostgreSql\Types\Numeric;

use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbBigInt extends FieldTypeNumeric
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::BigInt);
    }
}
