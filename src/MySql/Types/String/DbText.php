<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbText extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::Text, Length: null);
    }
}
