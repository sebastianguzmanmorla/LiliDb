<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbLongText extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::LongText, Length: null);
    }
}
