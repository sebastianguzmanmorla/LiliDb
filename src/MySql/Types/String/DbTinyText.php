<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbTinyText extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::TinyText, Length: null);
    }
}
