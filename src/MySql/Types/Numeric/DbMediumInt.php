<?php

namespace LiliDb\MySql\Types\Numeric;

use LiliDb\MySql\Types\FieldTypeEnum;

class DbMediumInt extends FieldTypeNumeric
{
    public function __construct(
        ?int $Length = null,
        bool $Unsigned = false,
        bool $Zerofill = false
    ) {
        parent::__construct(Type: FieldTypeEnum::MediumInt, Length: $Length, Unsigned: $Unsigned, Zerofill: $Zerofill);
    }
}
