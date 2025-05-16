<?php

namespace LiliDb\Query;

use LiliDb\Interfaces\ITable;
use LiliDb\Query\Traits\Where;
use LiliDb\Result;

abstract class QueryDelete extends Query
{
    use Where;

    public function __construct(
        ITable &$Table
    ) {
        parent::__construct(
            Database: $Table->Database,
            Table: $Table
        );
    }

    public function Execute(): Result
    {
        return $this->ExecuteQuery();
    }
}
