<?php

namespace LiliDb\PostgreSql\Types;

abstract class FieldType
{
    public FieldTypeEnum $FieldType;

    abstract public function TypeDefinition(): string;

    abstract public function FromSql(mixed $Value): mixed;

    abstract public function ToSql(mixed $Value): mixed;
}
