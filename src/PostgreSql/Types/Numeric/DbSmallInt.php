<?php

namespace LiliDb\PostgreSql\Types\Numeric;

use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbSmallInt extends FieldTypeNumeric
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::SmallInt);
    }
}
