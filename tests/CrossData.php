<?php

namespace LiliDb\Tests;

use DateTime;
use LiliDb\Tests\Models\TestTable;
use LiliDb\Tests\Models\TestTable2;

trait CrossData
{
    protected static TestTable $Item1;

    protected static TestTable $Item2;

    protected static TestTable2 $Item3;

    protected static TestTable2 $Item4;

    protected static TestTable2 $Item5;

    protected static function InitData(): void
    {
        self::$Item1 = new TestTable();
        self::$Item1->TestId = 1;
        self::$Item1->TestName = 'Test 1';
        self::$Item1->TestDateTime = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
        self::$Item1->State = true;

        self::$Item2 = new TestTable();
        self::$Item2->TestId = 2;
        self::$Item2->TestName = 'Test 2';
        self::$Item2->TestDateTime = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
        self::$Item2->State = true;

        self::$Item3 = new TestTable2();
        self::$Item3->Test2Id = 1;
        self::$Item3->TestId = 1;
        self::$Item3->Test2Number = 11;
        self::$Item3->Test2State = true;

        self::$Item4 = new TestTable2();
        self::$Item4->Test2Id = 2;
        self::$Item4->TestId = 1;
        self::$Item4->Test2Number = 12;
        self::$Item4->Test2State = false;

        self::$Item5 = new TestTable2();
        self::$Item5->Test2Id = 3;
        self::$Item5->TestId = 2;
        self::$Item5->Test2Number = 13;
        self::$Item5->Test2State = true;
    }
}
