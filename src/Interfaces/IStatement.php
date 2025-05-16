<?php

namespace LiliDb\Interfaces;

use LiliDb\Query\Query;

interface IStatement
{
    public function __construct(IConnection &$Connection, Query $Query, ?IField $Field = null);

    public function Execute(): bool;

    public function Result(bool $Associative = false): array|false;

    public function InsertId(): int|string|false;

    public function Error(): string;

    public function Close(): void;
}
