<?php

namespace LiliDb\MySql\Types\String;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbVarbinary extends FieldTypeString
{
    public function __construct(
        ?int $Length = null,
    ) {
        parent::__construct(Type: FieldTypeEnum::Varbinary, Length: $Length);
    }
}
