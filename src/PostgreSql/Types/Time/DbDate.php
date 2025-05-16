<?php

namespace LiliDb\PostgreSql\Types\Time;

use DateTime;
use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbDate extends FieldTypeTime
{
    public function __construct()
    {
        parent::__construct(Type: FieldTypeEnum::Date);
    }

    public function FromSql(mixed $Value): mixed
    {
        return $Value !== null ? DateTime::createFromFormat('Y-m-d', $Value) : null;
    }

    public function ToSql(mixed $Value): mixed
    {
        if ($Value instanceof DateTime) {
            return $Value->format('Y-m-d');
        }

        return null;
    }
}
