<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbLongBlob extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::LongBlob, Length: null);
    }
}
