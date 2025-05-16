<?php

namespace LiliDb\MySql;

use DateTime;
use LiliDb\Interfaces\IConnection;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\IStatement;
use LiliDb\Query\Query;
use LiliDb\Query\QuerySelect;
use LiliDb\Value;
use mysqli_stmt;

class Statement implements IStatement
{
    private mysqli_stmt $Statement;

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
    }

    public function Execute(): bool
    {
        return $this->Statement->execute();
    }

    public function Result(bool $Associative = false): array|false
    {
        $result = $this->Statement->get_result();
        if ($result !== false) {
            return $result->fetch_all($Associative ? MYSQLI_ASSOC : MYSQLI_NUM);
        }

        return false;
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
