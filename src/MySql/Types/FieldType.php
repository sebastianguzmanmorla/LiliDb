<?php

namespace LiliDb\MySql\Types;

abstract class FieldType
{
    public FieldTypeEnum $Type;

    abstract public function TypeDefinition(): string;

    abstract public function FromSql(mixed $Value): mixed;

    abstract public function ToSql(mixed $Value): mixed;
}
