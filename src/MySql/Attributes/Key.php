<?php

namespace LiliDb\MySql\Attributes;

use Attribute;
use LiliDb\MySql\Types\Numeric\DbBigInt;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Key extends Field
{
    public function __construct(
        ?string $FieldName = null
    ) {
        parent::__construct(
            FieldType: new DbBigInt(Unsigned: true),
            FieldName: $FieldName,
            FieldAllowNull: false,
            FieldPrimaryKey: true,
            FieldAutoIncrement: true
        );
    }
}
