<?php

namespace LiliDb\PostgreSql\Types\Time;

use DateTime;
use DateTimeZone;
use LiliDb\PostgreSql\Types\FieldTypeEnum;

class DbTimestamp extends FieldTypeTime
{
    public function __construct(
        ?int $Precision = null,
        bool $WithTimeZone = false,
    ) {
        parent::__construct(Type: FieldTypeEnum::Timestamp, Precision: $Precision, WithTimeZone: $WithTimeZone);
    }

    public function FromSql(mixed $Value): mixed
    {
        $WithTimeZone = $this->WithTimeZone ? 'O' : '';

        if ($Value !== null) {
            $Value = DateTime::createFromFormat('Y-m-d H:i:s' . $WithTimeZone, $Value);

            if ($this->WithTimeZone) {
                $TimeZone = new DateTimeZone(date_default_timezone_get());

                $Value = $Value->setTimezone($TimeZone);
            }

            return $Value;
        }

        return null;
    }

    public function ToSql(mixed $Value): mixed
    {
        if ($Value instanceof DateTime) {
            $WithTimeZone = $this->WithTimeZone ? 'O' : '';

            return $Value->format('Y-m-d H:i:s' . $WithTimeZone);
        }

        return null;
    }
}
