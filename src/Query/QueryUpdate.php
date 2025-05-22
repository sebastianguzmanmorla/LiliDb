<?php

namespace LiliDb\Query;

use LiliDb\Exceptions\QueryException;
use LiliDb\Interfaces\ITable;
use LiliDb\Query\Traits\Where;
use LiliDb\Result;

abstract class QueryUpdate extends Query
{
    use Where;

    protected object $Value;

    public function __construct(
        ITable &$Table,
        object $Value,
    ) {
        parent::__construct(
            Database: $Table->Database,
            Table: $Table
        );

        if ($Value::class != $this->Table->Model) {
            throw new QueryException($Value::class . " isn't class " . $this->Table->Model);
        }

        $this->Value = $Value;
    }

    public function Execute(): Result
    {
        return $this->ExecuteQuery();
    }
}
