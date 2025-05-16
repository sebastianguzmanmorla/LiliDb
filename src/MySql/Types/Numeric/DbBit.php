<?php

namespace LiliDb\MySql\Types\Numeric;

use LiliDb\MySql\Types\FieldTypeEnum;
use LiliDb\Token;

class DbBit extends FieldTypeNumeric
{
    public function __construct(
        ?int $Length = null,
    ) {
        parent::__construct(Type: FieldTypeEnum::Bit, Length: $Length, Unsigned: false, Zerofill: false);
    }

    public function FromSql(mixed $Value): mixed
    {
        return $Value !== null ? ($Value ? true : false) : null;
    }

    public function ToSql(mixed $Value): mixed
    {
        if (is_bool($Value)) {
            return $Value ? Token::True : Token::False;
        }

        return null;
    }
}
