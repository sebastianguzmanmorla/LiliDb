<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbBinary extends FieldTypeString
{
    public function __construct(
        ?int $Length = null,
    ) {
        parent::__construct(Type: FieldTypeEnum::Binary, Length: $Length);
    }
}
