<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbMediumBlob extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::MediumBlob, Length: null);
    }
}
