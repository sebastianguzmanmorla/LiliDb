<?php

namespace LiliDb\Interfaces;

use Generator;
use LiliDb\Query\Query;

interface IStatement
{
    public function __construct(IConnection &$Connection, Query $Query, ?IField $Field = null);

    public function Execute(): bool;

    public function FetchAll(bool $Associative = false): array|false;

    public function Fetch(bool $Associative = false): Generator;

    public function InsertId(): int|string|false;

    public function Error(): string;

    public function Close(): void;
}
