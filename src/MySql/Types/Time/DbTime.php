<?php

namespace LiliDb\MySql\Types\Time;

use DateTime;
use LiliDb\MySql\Types\FieldTypeEnum;

class DbTime extends FieldTypeTime
{
    public function __construct(
        ?int $Fraction = null
    ) {
        parent::__construct(Type: FieldTypeEnum::Time, Fraction: $Fraction);
    }

    public function FromSql(mixed $Value): mixed
    {
        return $Value !== null ? DateTime::createFromFormat('H:i:s', $Value) : null;
    }

    public function ToSql(mixed $Value): mixed
    {
        if ($Value instanceof DateTime) {
            return $Value->format('H:i:s');
        }

        return null;
    }
}
