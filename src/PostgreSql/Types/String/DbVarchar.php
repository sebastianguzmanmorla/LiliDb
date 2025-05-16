<?php

namespace LiliDb\PostgreSql\Types\String;

use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbVarchar extends FieldTypeString
{
    public function __construct(
        int $Length = 1,
    ) {
        parent::__construct(Type: FieldTypeEnum::Varchar, Length: $Length);
    }

    public function FromSql(mixed $Value): mixed
    {
        return $Value !== null ? (string) $Value : null;
    }
}
