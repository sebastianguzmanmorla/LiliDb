<?php

namespace LiliDb\MySql\Query;

use LiliDb\Query\QueryDelete as AbstractQueryDelete;
use LiliDb\SqlFormatter;

class QueryDelete extends AbstractQueryDelete
{
    public function GenerateSql(): string
    {
        $this->Query = "DELETE FROM {$this->Table->TableReference()}";

        $this->Parameters = [];

        $this->GenerateWhereSql();

        return SqlFormatter::format($this->Query, false);
    }
}
