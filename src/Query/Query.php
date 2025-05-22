<?php

namespace LiliDb\Query;

use Closure;
use Generator;
use LiliDb\Database;
use LiliDb\Exceptions\QueryException;
use LiliDb\Interfaces\ITable;
use LiliDb\Result;
use LiliDb\ResultRow;
use LiliDb\ResultSet;
use LiliDb\SqlFormatter;
use Throwable;

class Query
{
    public string $Query = '';

    public array $Parameters = [];

    public Closure|string $ParameterMarker = '?';

    public function __construct(
        protected Database &$Database,
        protected ?ITable &$Table = null
    ) {
    }

    public function __toString(): string
    {
        $this->ParameterMarker = '?';

        $Query = $this->Query != '' ? $this->Query : $this->GenerateSql();

        $Parameters = $this->Parameters;

        while (str_contains($Query, '?')) {
            $Value = array_shift($Parameters);
            $Query = substr_replace($Query, ($Value !== null ? (is_numeric($Value) ? $Value : "'" . $Value . "'") : 'NULL'), strpos($Query, '?'), 1);
        }

        return SqlFormatter::format($Query, true);
    }

    public function GenerateSql(): string
    {
        return SqlFormatter::format($this->Query, false);
    }

    public function FetchAllResultSet(): ResultSet
    {
        $Result = $this->FetchAll(true);

        if ($Result->Result !== false) {
            foreach ($Result->Result as &$Row) {
                $ResultRow = new ResultRow();

                foreach ($Row as $Index => $Value) {
                    $ResultRow->__set($Index, $Value);
                }

                $Row = $ResultRow;
            }

            return new ResultSet(
                Query: $this,
                Result: $Result->Result
            );
        }

        return new ResultSet(
            Query: $this,
            Result: [],
            Error: $Result->Error
        );
    }

    public function FetchAll(bool $Associative = true): Result
    {
        try {
            $this->Database->Query = $this;

            if ($Statement = $this->Database->Connection->Prepare($this)) {
                if ($Statement->Execute()) {
                    $Data = $Statement->FetchAll($Associative);

                    $Statement->Close();

                    return new Result($this, $Data);
                }

                throw new QueryException($Statement->Error());
            } else {
                throw new QueryException($this->Database->Connection->Error());
            }
        } catch (Throwable $ex) {
            return new Result($this, false, $ex);
        }
    }

    public function Fetch(bool $Associative = true): Generator
    {
        $this->Database->Query = $this;

        if ($Statement = $this->Database->Connection->Prepare($this)) {
            if ($Statement->Execute()) {
                foreach ($Statement->Fetch($Associative) as $Row) {
                    yield $Row;
                }

                $Statement->Close();
            } else {
                throw new QueryException($Statement->Error());
            }
        } else {
            throw new QueryException($this->Database->Connection->Error());
        }
    }

    public function ExecuteQuery(): Result
    {
        try {
            $this->Database->Query = $this;

            if ($Statement = $this->Database->Connection->Prepare($this)) {
                if ($Statement->Execute()) {
                    $Statement->Close();

                    return new Result($this, true);
                }

                throw new QueryException($Statement->Error());
            } else {
                throw new QueryException($this->Database->Connection->Error());
            }
        } catch (Throwable $ex) {
            return new Result($this, false, $ex);
        }
    }
}
