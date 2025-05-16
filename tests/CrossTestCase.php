<?php

namespace LiliDb\Tests;

use DateTime;
use Exception;
use LiliDb\MySql\Connection as MySqlConnection;
use LiliDb\PostgreSql\Connection as PostgreSqlConnection;
use LiliDb\Tests\Models\TestDatabase;
use LiliDb\Tests\Models\TestSelect;
use LiliDb\Tests\Models\TestSelect2;
use LiliDb\Tests\Models\TestTable;
use LiliDb\Tests\Models\TestTable2;
use LiliDb\Where;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

abstract class CrossTestCase extends TestCase
{
    use CrossData;

    protected static ?TestDatabase $Database = null;

    public static function tearDownAfterClass(): void
    {
        self::$Database = null;
    }

    #[Test]
    #[TestDox('Database RawQuery')]
    public function schemaRawQuery(): void
    {
        $Query = match (self::$Database->Connection::class) {
            MySqlConnection::class => self::$Database->RawQuery('SELECT version() as version'),
            PostgreSqlConnection::class => self::$Database->RawQuery('SHOW server_version'),
            default => throw new Exception(self::$Database->Connection::class . ' not implemented')
        };

        $Result = $Query->ExecuteResultSet();

        self::assertNull($Result->Error);

        $Version = match (self::$Database->Connection::class) {
            MySqlConnection::class => $Result->version,
            PostgreSqlConnection::class => $Result->server_version,
            default => throw new Exception(self::$Database->Connection::class . ' not implemented')
        };

        fwrite(STDERR, PHP_EOL . self::$Database->Connection::class . " - Database version: {$Version} " . PHP_EOL);
    }

    #[Test]
    #[TestDox('Create: TestTable')]
    public function createTestTable(): void
    {
        $Result = TestTable::CreateTable(false)
            ->ExecuteQuery();

        self::assertNull($Result->Error);
    }

    #[Test]
    #[TestDox('Create: TestTable2')]
    public function createTestTable2(): void
    {
        $Result = TestTable2::CreateTable(false)
            ->ExecuteQuery();

        self::assertNull($Result->Error);
    }

    #[Test]
    #[TestDox('Insert TestTable')]
    #[Depends('createTestTable')]
    public function insertTestTable(): void
    {
        $Result = TestTable::Insert(self::$Item1, self::$Item2)
            ->Execute();

        self::assertNull($Result->Error);
    }

    #[Test]
    #[TestDox('Count TestTable')]
    #[Depends('insertTestTable')]
    public function countTestTable(): void
    {
        $Count = TestTable::CountAll();

        self::assertEquals($Count, 2, 'Not all entries inserted');
    }

    #[Test]
    #[TestDox('Insert OnDuplicateKeyUpdate TestTable')]
    #[Depends('insertTestTable')]
    public function insertOnDuplicateKeyUpdateTestTable(): void
    {
        $TestDateTime1 = self::$Item1->TestDateTime;
        $TestDateTime2 = self::$Item2->TestDateTime;

        sleep(2);

        self::$Item1->TestDateTime = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        self::$Item2->TestDateTime = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        $Result = TestTable::Insert(self::$Item1, self::$Item2)
            ->OnDuplicateKeyUpdate(
                TestTable::Field('TestName'),
                TestTable::Field('TestDateTime')
            )
            ->Execute();

        self::assertNull($Result->Error);

        $TestIds = [self::$Item1->TestId, self::$Item2->TestId];

        $ResultSet = TestTable::Where(fn (TestTable $x) => Where::In($x->TestId, $TestIds))
            ->Execute();

        foreach ($ResultSet as $Item) {
            $CompareDate = $Item->TestId == 1 ? $TestDateTime1 : $TestDateTime2;
            $CompareItem = $Item->TestId == 1 ? self::$Item1 : self::$Item2;

            self::assertNotEquals($Item->TestDateTime->format('c'), $CompareDate->format('c'), 'It was not updated');

            self::assertEquals($Item, $CompareItem, 'It was not updated');
        }
    }

    #[Test]
    #[TestDox('Insert TestTable2')]
    #[Depends('createTestTable2')]
    public function insertTestTable2(): void
    {
        $Result = TestTable2::Insert(self::$Item3, self::$Item4, self::$Item5)
            ->Execute();

        self::assertNull($Result->Error);
    }

    #[Test]
    #[TestDox('Count TestTable2')]
    #[Depends('insertTestTable2')]
    public function countTestTable2(): void
    {
        $Count = TestTable2::CountAll();

        self::assertEquals($Count, 3, 'Not all entries inserted');
    }

    #[Test]
    #[TestDox('Count TestTable2 State False')]
    #[Depends('insertTestTable2')]
    public function countTestTable2StateFalse(): void
    {
        $Count = TestTable2::Count(fn (TestTable2 $x) => $x->Test2State == false);

        self::assertEquals($Count, 1, 'Count Failed');
    }

    #[Test]
    #[TestDox('Count TestTable2 State True')]
    #[Depends('insertTestTable2')]
    public function countTestTable2StateTrue(): void
    {
        $True = true;

        $Count = TestTable2::Count(fn (TestTable2 $x) => $x->Test2State == $True);

        self::assertEquals($Count, 2, 'Count Failed');
    }

    #[Test]
    #[TestDox('Update TestTable2')]
    #[Depends('insertTestTable2')]
    public function updateTestTable2(): void
    {
        $ItemUpdate = new TestTable2();
        $ItemUpdate->Test2Number = 5;

        $TestIds = [self::$Item3->Test2Id, self::$Item4->Test2Id];

        $Result = TestTable2::Update($ItemUpdate)
            ->Where(fn (TestTable2 $x) => Where::in($x->Test2Id, $TestIds))
            ->Execute();

        self::assertNull($Result->Error);

        $TestIds = [self::$Item3->Test2Id, self::$Item4->Test2Id, self::$Item5->Test2Id];

        $ResultSet = TestTable2::Where(fn (TestTable2 $x) => Where::In($x->Test2Id, $TestIds))
            ->Execute();

        foreach ($ResultSet as $Item) {
            $CompareItem = match ($Item->Test2Id) {
                1 => self::$Item3,
                2 => self::$Item4,
                3 => self::$Item5,
            };

            if ($Item->Test2Id == 3) {
                self::assertEquals($Item, $CompareItem, 'It was updated');
            } else {
                self::assertEquals($Item->Test2Number, $ItemUpdate->Test2Number, 'It was not updated');

                self::assertNotEquals($Item, $CompareItem, 'It was not updated');
            }

            $CompareItem->Test2Number = $ItemUpdate->Test2Number;
        }
    }

    #[Test]
    #[TestDox('Select Closure Specific Fields')]
    #[Depends('insertTestTable')]
    #[Depends('insertTestTable2')]
    public function selectClosureSpecificFields(): void
    {
        $Select = fn (TestTable $x, TestTable2 $y): TestSelect => new TestSelect(
            TestId: $x->TestId,
            TestName: $x->TestName,
            TestDateTime: $x->TestDateTime,
            Test2Id: $y->Test2Id,
            Test2Number: $y->Test2Number
        );

        $Result = TestTable::InnerJoin(fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId)
            ->Where(fn (TestTable $x) => $x->TestId == 1)
            ->Select($Select)
            ->Execute();

        self::assertNull($Result->Error);

        foreach ($Result as $Item) {
            $CompareItem = match ($Item->Test2Id) {
                1 => self::$Item3,
                2 => self::$Item4,
                3 => self::$Item5,
            };

            $CompareClosure = $Select(self::$Item1, $CompareItem);

            self::assertEquals($Item, $CompareClosure, 'Is not equal');
        }
    }

    #[Test]
    #[TestDox('Select Closure All Fields')]
    #[Depends('insertTestTable')]
    #[Depends('insertTestTable2')]
    public function selectClosureAllFields(): void
    {
        $Select = fn (TestTable $x, TestTable2 $y): TestSelect2 => new TestSelect2(
            TestTable: $x,
            TestTable2: $y
        );

        $Result = TestTable::InnerJoin(fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId)
            ->Where(fn (TestTable $x) => $x->TestId == 1)
            ->Select($Select)
            ->Execute();

        self::assertNull($Result->Error);

        foreach ($Result as $Item) {
            $CompareItem = match ($Item->TestTable2->Test2Id) {
                1 => self::$Item3,
                2 => self::$Item4,
                3 => self::$Item5,
            };

            $CompareClosure = $Select(self::$Item1, $CompareItem);

            self::assertEquals($Item, $CompareClosure, 'Is not equal');
        }
    }

    #[Test]
    #[TestDox('Truncate: TestTable')]
    #[Depends('createTestTable')]
    public function truncateTestTable(): void
    {
        $Result = TestTable::TruncateTable()
            ->ExecuteQuery();

        self::assertNull($Result->Error);

        self::assertFalse(TestTable::AnyAll(), 'It was not truncated');
    }

    #[Test]
    #[TestDox('Drop: TestTable')]
    #[Depends('createTestTable')]
    public function dropTestTable(): void
    {
        $Result = TestTable::DropTable()
            ->ExecuteQuery();

        self::assertNull($Result->Error);
    }

    #[Test]
    #[TestDox('Truncate: TestTable2')]
    #[Depends('createTestTable2')]
    public function truncateTestTable2(): void
    {
        $Result = TestTable2::TruncateTable()
            ->ExecuteQuery();

        self::assertNull($Result->Error);

        self::assertFalse(TestTable2::AnyAll(), 'It was not truncated');
    }

    #[Test]
    #[TestDox('Drop: TestTable2')]
    #[Depends('createTestTable2')]
    public function dropTestTable2(): void
    {
        $Result = TestTable2::DropTable()
            ->ExecuteQuery();

        self::assertNull($Result->Error);
    }

    #[Test]
    #[TestDox('Connection Close')]
    public function connectionClose(): void
    {
        self::$Database->Connection->Close();

        $Status = self::$Database->Connection->Status();

        self::assertStringContainsString('closed', $Status);
    }
}
