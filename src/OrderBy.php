<?php

namespace LiliDb;

class OrderBy
{
    public static function Asc(mixed $Field): Value
    {
        return new Value(
            Field: $Field,
            Order: Token::Asc
        );
    }

    public static function Desc(mixed $Field): Value
    {
        return new Value(
            Field: $Field,
            Order: Token::Desc
        );
    }
}
