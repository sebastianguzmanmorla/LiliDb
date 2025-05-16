<?php

namespace LiliDb;

class Where
{
    public static function Equal(mixed $Field, mixed $Value): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::Equal,
            Value: $Value
        );
    }

    public static function NotEqual(mixed $Field, mixed $Value): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::NotEqual,
            Value: $Value
        );
    }

    public static function MoreThan(mixed $Field, mixed $Value): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::MoreThan,
            Value: $Value
        );
    }

    public static function MoreEqualThan(mixed $Field, mixed $Value): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::MoreEqualThan,
            Value: $Value
        );
    }

    public static function LessThan(mixed $Field, mixed $Value): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::LessThan,
            Value: $Value
        );
    }

    public static function LessEqualThan(mixed $Field, mixed $Value): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::LessEqualThan,
            Value: $Value
        );
    }

    public static function In(mixed $Field, array $Values): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::In,
            Value: $Values
        );
    }

    public static function NotIn(mixed $Field, array $Values): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::NotIn,
            Value: $Values
        );
    }

    public static function Like(mixed $Field, mixed $Value): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::Like,
            Value: $Value
        );
    }

    public static function NotLike(mixed $Field, mixed $Value): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::NotLike,
            Value: $Value
        );
    }

    public static function IsNull(mixed $Field): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::IsNull
        );
    }

    public static function IsNotNull(mixed $Field): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::IsNotNull
        );
    }

    public static function Between(mixed $Field, mixed $Start, mixed $End): Value
    {
        return new Value(
            Field: $Field,
            Where: Token::Between,
            Value: [$Start, $End]
        );
    }
}
