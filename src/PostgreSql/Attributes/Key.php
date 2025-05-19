<?php

namespace LiliDb\PostgreSql\Attributes;

use Attribute;
use LiliDb\PostgreSql\Types\Numeric\DbBigSerial;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Key extends Field
{
    public function __construct(
        ?string $Name = null
    ) {
        parent::__construct(
            Type: new DbBigSerial(),
            Name: $Name,
            AllowNull: false,
            PrimaryKey: true
        );
    }
}
