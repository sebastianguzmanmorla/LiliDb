<?php

namespace LiliDb\Tests\Models;

use DateTime;

class TestSelect
{
    public function __construct(
        public ?int $TestId,
        public ?string $TestName,
        public ?DateTime $TestDateTime,
        public ?int $Test2Id,
        public ?int $Test2Number,
    ) {
    }
}
