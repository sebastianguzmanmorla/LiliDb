<?php

namespace LiliDb;

use ArrayAccess;
use Exception;
use JsonSerializable;
use ReflectionClass;
use ReflectionObject;
use stdClass;

class ResultRow extends stdClass implements ArrayAccess, JsonSerializable
{
    private ReflectionObject $Reflection;

    public function __construct()
    {
        $this->Reflection = new ReflectionObject($this);
    }

    public function __isset(string $name): bool
    {
        if ($this->Reflection->hasProperty($name)) {
            return true;
        }

        $Properties = $this->Reflection->getProperties();

        foreach ($Properties as $Property) {
            $Table = $Property->getValue($this);

            if (is_object($Table)) {
                $TableReflection = new ReflectionClass($Table);

                if ($TableReflection->hasProperty($name)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function __get(string $name): mixed
    {
        if ($this->Reflection->hasProperty($name)) {
            return $this->Reflection->getProperty($name)->getValue($this);
        }

        $Properties = $this->Reflection->getProperties();

        foreach ($Properties as $Property) {
            $Table = $Property->getValue($this);

            if (is_object($Table)) {
                $TableReflection = new ReflectionClass($Table);

                if ($TableReflection->hasProperty($name)) {
                    $Property = $TableReflection->getProperty($name);

                    return $Property->isInitialized($Table) ? $Property->getValue($Table) : null;
                }
            }
        }

        throw new Exception("Property {$name} not found");
    }

    public function __set(string $name, mixed $Value): void
    {
        if ($this->Reflection->name == self::class) {
            $this->{$name} = $Value;

            return;
        }

        if ($this->Reflection->hasProperty($name)) {
            $this->Reflection->getProperty($name)->setValue($this, $Value);

            return;
        }

        $Properties = $this->Reflection->getProperties();

        foreach ($Properties as $Property) {
            $Table = $Property->getValue($this);

            if (is_object($Table)) {
                $TableReflection = new ReflectionClass($Table);

                if ($TableReflection->hasProperty($name)) {
                    $TableReflection->getProperty($name)->setValue($Table, $Value);

                    return;
                }
            }
        }

        throw new Exception("Property {$name} not found");
    }

    public function __unset($name): void
    {
        if ($this->Reflection->hasProperty($name)) {
            $this->Reflection->getProperty($name)->setValue($this, null);

            return;
        }

        $Properties = $this->Reflection->getProperties();

        foreach ($Properties as $Property) {
            $Table = $Property->getValue($this);

            if (is_object($Table)) {
                $TableReflection = new ReflectionClass($Table);

                if ($TableReflection->hasProperty($name)) {
                    $TableReflection->getProperty($name)->setValue($Table, null);

                    return;
                }
            }
        }
    }

    public function jsonSerialize(): mixed
    {
        return $this;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet(mixed $offset, mixed $Value): void
    {
        $this->__set($offset, $Value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }
}
