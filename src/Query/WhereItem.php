<?php

namespace LiliDb\Query;

use LiliDb\Token;

class WhereItem
{
    public array $Items = [];

    public string $Query = '';

    public array $Parameters = [];

    public function __construct(
        public ?Token $Prefix = null
    ) {
    }
}
