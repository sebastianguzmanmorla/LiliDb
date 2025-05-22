<?php

namespace LiliDb\PostgreSql;

use DateTime;
use ErrorException;
use Generator;
use LiliDb\Exceptions\StatementException;
use LiliDb\Interfaces\IConnection;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\IStatement;
use LiliDb\Query\Query;
use LiliDb\Query\QuerySelect;
use PgSql\Result;
use Throwable;

class Statement implements IStatement
{
    private string $GUID;

    private Connection $Connection;

    private Result|false $Statement = false;

    private Query $Query;

    private ?IField $Field;

    private int $ParameterMarkerCount = 1;

    public function __construct(IConnection &$Connection, Query $Query, ?IField $Field = null)
    {
        $this->Connection = $Connection;
        $this->Field = $Field;

        $this->GUID = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

        $Query->ParameterMarker = fn () => '$' . $this->ParameterMarkerCount++;

        $Query->GenerateSql();

        if ($Query instanceof QuerySelect) {
            if ($Query->Limit !== null) {
                $Query->Query .= ' LIMIT ' . $Query->Limit;
            }

            if ($Query->Offset !== null) {
                $Query->Query .= ' OFFSET ' . $Query->Offset;
            }
        }

        if ($this->Field !== null) {
            $Query->Query .= ' RETURNING ' . $Field->Name;
        }

        $this->Query = $Query;

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (0 === error_reporting()) {
                return false;
            }

            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            $this->Statement = pg_prepare($this->Connection->Client, $this->GUID, $Query->Query);

            if (count($Query->Parameters) > 0) {
                foreach ($Query->Parameters as $k => &$v) {
                    if ($v instanceof DateTime) {
                        $v = $v->format('Y-m-d H:i:s');
                    }
                }
            }
        } catch (Throwable $ex) {
            throw new StatementException('Failed to prepare query statement', previous: $ex);
        }

        restore_error_handler();
    }

    public function Execute(): bool
    {
        if ($this->Statement === false) {
            throw new StatementException("There isn't a prepared query statement to execute");
        }

        $this->Statement = pg_execute($this->Connection->Client, $this->GUID, $this->Query->Parameters);

        return $this->Statement !== false;
    }

    public function FetchAll(bool $Associative = false): array|false
    {
        if ($this->Statement === false) {
            throw new StatementException("There isn't a result to fetch");
        }

        return pg_fetch_all($this->Statement, $Associative ? PGSQL_ASSOC : PGSQL_NUM);
    }

    public function Fetch(bool $Associative = false): Generator
    {
        if ($this->Statement === false) {
            throw new StatementException("There isn't a result to fetch");
        }

        while ($row = pg_fetch_row($this->Statement, mode: $Associative ? PGSQL_ASSOC : PGSQL_NUM)) {
            yield $row;
        }
    }

    public function InsertId(): int|string|false
    {
        if ($this->Statement === false) {
            throw new StatementException("There isn't a result to fetch");
        }

        $Row = pg_fetch_row($this->Statement, mode: PGSQL_NUM);

        return $Row[0] ?? false;
    }

    public function Error(): string
    {
        return $this->Statement === false ? pg_last_error($this->Connection->Client) : pg_result_error($this->Statement);
    }

    public function Close(): void
    {
        if ($this->Statement !== false) {
            pg_free_result($this->Statement);
        }
    }
}
