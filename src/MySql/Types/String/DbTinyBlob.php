<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbTinyBlob extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::TinyBlob, Length: null);
    }
}
