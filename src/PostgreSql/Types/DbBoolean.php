<?php

namespace LiliDb\PostgreSql\Types;

use LiliDb\Token;

class DbBoolean extends FieldType
{
    public function __construct()
    {
        $this->FieldType = FieldTypeEnum::Boolean;
    }

    public function TypeDefinition(): string
    {
        return $this->FieldType->value;
    }

    public function FromSql(mixed $Value): mixed
    {
        return $Value !== null ? ($Value == 't' ? true : false) : null;
    }

    public function ToSql(mixed $Value): mixed
    {
        if (is_bool($Value)) {
            return $Value ? Token::True : Token::False;
        }

        return null;
    }
}
