<?php

namespace LiliDb\Query;

use LiliDb\Interfaces\ITable;

class JoinItem
{
    public array $On = [];

    public function __construct(
        public ITable $Table,
        public JoinType $JoinType,
    ) {
    }
}
