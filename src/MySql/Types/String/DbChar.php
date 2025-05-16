<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbChar extends FieldTypeString
{
    public function __construct(
        ?int $Length = null,
    ) {
        parent::__construct(Type: FieldTypeEnum::Char, Length: $Length);
    }
}
