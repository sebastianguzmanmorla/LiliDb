<?php

namespace LiliDb\Interfaces;

use LiliDb\Query\Query;

interface IConnection
{
    public bool $Connected
        {
        get;
    }

    public function Connect(): void;

    public function Prepare(Query &$Query, ?IField $Field = null): IStatement;

    public function Error(): string;

    public function Close(): void;

    public function Status(): string;
}
