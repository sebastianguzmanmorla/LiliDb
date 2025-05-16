<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbMediumText extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::MediumText, Length: null);
    }
}
