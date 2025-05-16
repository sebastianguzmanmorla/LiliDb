<?php

namespace LiliDb;

use ArrayAccess;
use Iterator;
use JsonSerializable;
use LiliDb\Query\Query;
use Throwable;

class ResultSet extends Result implements ArrayAccess, Iterator, JsonSerializable
{
    public bool $EOF = true;

    public int $Rows = 0;

    public int $Index = 0;

    public function __construct(
        Query $Query,
        array $Result,
        public ?int $Offset = null,
        public ?int $Limit = null,
        public ?int $Total = null,
        ?Throwable $Error = null
    ) {
        parent::__construct($Query, $Result, $Error);

        $this->EOF = !isset($this->Result[$this->Index]);
        $this->Rows = count($this->Result);
    }

    public function __get($key): mixed
    {
        return $this->current()?->__get($key);
    }

    public function __set($key, $Value): void
    {
        $this->current()?->__set($key, $Value);
    }

    public function __isset($key): bool
    {
        return $this->current()?->__isset($key) ?? false;
    }

    public function __unset($key): void
    {
        $this->current()?->__unset($key);
    }

    public function __toString()
    {
        return json_encode($this->Result[$this->Index]);
    }

    public function RecordCount(): int
    {
        return $this->Rows;
    }

    public function current(): ?object
    {
        return $this->Result[$this->Index] ?? null;
    }

    public function key(): int
    {
        return $this->Index;
    }

    public function next(): void
    {
        ++$this->Index;
        $this->EOF = !isset($this->Result[$this->Index]);
    }

    public function MoveNext(): void
    {
        $this->next();
    }

    public function rewind(): void
    {
        $this->Index = 0;
        $this->EOF = !isset($this->Result[$this->Index]);
    }

    public function MoveFirst(): void
    {
        $this->rewind();
    }

    public function valid(): bool
    {
        return !$this->EOF;
    }

    public function EOF(): bool
    {
        return $this->EOF;
    }

    public function Move($x): void
    {
        $this->Index = $x;
        $this->EOF = !isset($this->Result[$this->Index]);
    }

    public function offsetGet($key): mixed
    {
        return $this->current()->__get($key);
    }

    public function Field($key): mixed
    {
        return $this->current()->__get($key);
    }

    public function offsetSet($key, $Value): void
    {
        $this->current()->__set($key, $Value);
    }

    public function offsetExists(mixed $key): bool
    {
        return $this->current()->__isset($key);
    }

    public function offsetUnset($key): void
    {
        $this->current()->offsetUnset($key);
    }

    public function jsonSerialize(): mixed
    {
        return $this->Result;
    }

    public function Collection(string $key, string ...$keys): array
    {
        $array = [];
        foreach ($this->Result as $item) {
            $Value = $item->__get($key);

            foreach ($keys as $k) {
                $Value = [$item->__get($k) => $Value];
            }
            if (is_array($Value)) {
                $array = static::arrayMerge($array, $Value);
            } else {
                $array[] = $Value;
            }
        }

        return $array;
    }

    private static function arrayMerge(array $array1, array $array2)
    {
        foreach ($array2 as $key => $Value) {
            if (!array_key_exists($key, $array1)) {
                $array1[$key] = $Value;
            } else {
                $array1[$key] = is_array($array1[$key]) ? $array1[$key] : [$array1[$key]];
                $array1[$key][] = $Value;
            }
        }

        return $array1;
    }
}
