<?php

namespace LiliDb\PostgreSql\Attributes;

use Attribute;
use LiliDb\PostgreSql\Types\Numeric\DbBigSerial;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Key extends Field
{
    public function __construct(
        ?string $FieldName = null
    ) {
        parent::__construct(
            FieldType: new DbBigSerial(),
            FieldName: $FieldName,
            FieldAllowNull: false,
            FieldPrimaryKey: true
        );
    }
}
