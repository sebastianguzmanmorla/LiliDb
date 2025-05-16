<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbVarchar extends FieldTypeString
{
    public function __construct(
        ?int $Length = null,
    ) {
        parent::__construct(Type: FieldTypeEnum::Varchar, Length: $Length);
    }
}
