<?php

namespace LiliDb\PostgreSql\Types\String;

use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbText extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::Text);
    }
}
