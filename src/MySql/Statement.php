<?php

namespace LiliDb\MySql;

use DateTime;
use Generator;
use LiliDb\Exceptions\StatementException;
use LiliDb\Interfaces\IConnection;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\IStatement;
use LiliDb\Query\Query;
use LiliDb\Query\QuerySelect;
use LiliDb\Value;
use mysqli_stmt;
use Throwable;

class Statement implements IStatement
{
    private mysqli_stmt|false $Statement = false;

    public function __construct(IConnection &$Connection, Query $Query, ?IField $Field = null)
    {
        $Query->ParameterMarker = '?';

        $Query->GenerateSql();

        if ($Query instanceof QuerySelect) {
            if ($Query->Limit !== null && $Query->Offset === null) {
                $Query->Query .= ' LIMIT ' . $Query->Limit;
            }

            if ($Query->Limit !== null && $Query->Offset !== null) {
                $Query->Query .= ' LIMIT ' . $Query->Offset . ', ' . $Query->Limit;
            }
        }

        try {
            $this->Statement = $Connection->Client->prepare($Query->Query);

            if (count($Query->Parameters) > 0) {
                $params = [$this->bindTypes($Query->Parameters)];
                foreach ($Query->Parameters as $k => &$v) {
                    if ($v instanceof Value) {
                        $v = $v->Value;
                    }
                    if ($v instanceof DateTime) {
                        $v = $v->format('Y-m-d H:i:s');
                    }

                    $params[] = &$Query->Parameters[$k];
                }
                call_user_func_array([$this->Statement, 'bind_param'], $params);
            }
        } catch (Throwable $ex) {
            throw new StatementException('Failed to prepare query statement', previous: $ex);
        }
    }

    public function Execute(): bool
    {
        if ($this->Statement === false) {
            throw new StatementException("There isn't a prepared query statement to execute");
        }

        return $this->Statement->execute();
    }

    public function FetchAll(bool $Associative = false): array|false
    {
        $result = $this->Statement->get_result();

        if ($result === false) {
            throw new StatementException("There isn't a result to fetch");
        }

        return $result->fetch_all($Associative ? MYSQLI_ASSOC : MYSQLI_NUM);
    }

    public function Fetch(bool $Associative = false): Generator
    {
        $result = $this->Statement->get_result();

        if ($result === false) {
            throw new StatementException("There isn't a result to fetch");
        }

        while ($row = $result->fetch_array($Associative ? MYSQLI_ASSOC : MYSQLI_NUM)) {
            yield $row;
        }

        $result->free();
    }

    public function InsertId(): int|string|false
    {
        return $this->Statement->insert_id;
    }

    public function Error(): string
    {
        return $this->Statement->error;
    }

    public function Close(): void
    {
        $this->Statement->close();
    }

    private function bindTypes($params)
    {
        $types = [];

        foreach ($params as $param) {
            if (is_float($param)) {
                $types[] = 'd';
            } elseif (is_int($param)) {
                $types[] = 'i';
            } else {
                $types[] = 's';
            }
        }

        return implode('', $types);
    }
}
