<?php

namespace LiliDb\Tests\Models;

class TestSelect2
{
    public function __construct(
        public ?TestTable $TestTable,
        public ?TestTable2 $TestTable2
    ) {
    }
}
