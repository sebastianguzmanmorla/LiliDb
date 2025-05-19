<?php

namespace LiliDb\MySql\Attributes;

use Attribute;
use LiliDb\MySql\Types\Numeric\DbBigInt;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Key extends Field
{
    public function __construct(
        ?string $Name = null
    ) {
        parent::__construct(
            Type: new DbBigInt(Unsigned: true),
            Name: $Name,
            AllowNull: false,
            PrimaryKey: true,
            AutoIncrement: true
        );
    }
}
