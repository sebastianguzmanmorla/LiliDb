<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbBlob extends FieldTypeString
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::Blob, Length: null);
    }
}
