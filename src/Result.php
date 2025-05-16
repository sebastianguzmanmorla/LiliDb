<?php

namespace LiliDb;

use LiliDb\Query\Query;
use Throwable;

class Result
{
    public function __construct(
        public Query $Query,
        public array|bool $Result,
        public ?Throwable $Error = null
    ) {
    }
}
