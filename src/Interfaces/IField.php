<?php

namespace LiliDb\Interfaces;

use ReflectionProperty;

interface IField
{
    public ITable $Table { get; }

    public ReflectionProperty $FieldReflection { get; }

    public function FieldDefinition(): string;

    public function FieldReference(bool $FullReference): string;

    public function FieldSetValue(object $Class, mixed $Value): void;
}
