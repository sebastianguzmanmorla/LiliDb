<?php

namespace LiliDb\MySql\Types\Time;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbYear extends FieldTypeTime
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::Year);
    }

    public function FromSql(mixed $Value): mixed
    {
        return $Value !== null ? (int) $Value : null;
    }

    public function ToSql(mixed $Value): mixed
    {
        if (is_numeric($Value)) {
            return (int) $Value;
        }

        return null;
    }
}
