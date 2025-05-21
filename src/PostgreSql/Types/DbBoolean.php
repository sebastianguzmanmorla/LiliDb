<?php

namespace LiliDb\PostgreSql\Types;

use LiliDb\Token;

class DbBoolean extends FieldType
{
    public function __construct()
    {
        $this->Type = FieldTypeEnum::Boolean;
    }

    public function TypeDefinition(): string
    {
        return $this->Type->value;
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
