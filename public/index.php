<?php

require_once __DIR__ . './../vendor/autoload.php';

use LiliDb\MySql\Connection as MySqlConnection;
use LiliDb\OrderBy;
use LiliDb\PostgreSql\Connection as PostgreSqlConnection;
use LiliDb\Tests\Models\TestDatabase;
use LiliDb\Tests\Models\TestSelect;
use LiliDb\Tests\Models\TestSelect2;
use LiliDb\Tests\Models\TestTable;
use LiliDb\Tests\Models\TestTable2;
use LiliDb\Token;
use LiliDb\Where;

$Connections = [
    new MySqlConnection(
        Hostname: getenv('MYSQL_HOST'),
        Port: getenv('MYSQL_PORT'),
        Database: getenv('MYSQL_DATABASE'),
        Username: getenv('MYSQL_USERNAME'),
        Password: getenv('MYSQL_PASSWORD')
    ),
    new PostgreSqlConnection(
        Hostname: getenv('POSTGRES_HOST'),
        Port: getenv('POSTGRES_PORT'),
        Database: getenv('POSTGRES_DB'),
        Username: getenv('POSTGRES_USER'),
        Password: getenv('POSTGRES_PASSWORD')
    ),
];

foreach ($Connections as $Connection) {
    $Database = new TestDatabase($Connection, getenv('DB_DATABASE'));

    foreach ($Database->DatabaseTables as $Table) {
        $CreateTable = $Table->CreateTable()
            ->ExecuteQuery();

        var_dump($CreateTable);

        echo $CreateTable->Query;

        echo '<hr>';
    }

    $Insert1 = TestTable::New(
        TestId: 1,
        TestName: 'a ' . date('Y-m-d H:i:s'),
        TestDateTime: new DateTime(),
    );

    var_dump($Insert1);

    $Insert2 = new TestTable();
    $Insert2->TestId = 2;
    $Insert2->TestName = date('Y-m-d H:i:s') . ' b';
    $Insert2->TestDateTime = new DateTime();

    var_dump($Insert2);

    $Insert = TestTable::Insert($Insert1, $Insert2)
        ->OnDuplicateKeyUpdate(
            TestTable::Field('TestName'),
            TestTable::Field('TestDateTime')
        )
        ->Execute();

    var_dump($Insert);

    echo $Insert->Query;

    echo '<hr>';

    $Update1 = new TestTable();
    $Update1->TestName = 'a ' . date('Y-m-d H:i:s');
    $Update1->TestDateTime = new DateTime();

    var_dump($Update1);

    $Update = TestTable::Update($Update1)
        ->Where(fn (TestTable $x) => $x->TestId == 1)
        ->Execute();

    var_dump($Update);

    echo $Update->Query;

    echo '<hr>';

    $Insert1 = new TestTable2();
    $Insert1->Test2Id = 1;
    $Insert1->TestId = 1;
    $Insert1->Test2Number = 11;
    $Insert1->Test2State = true;

    var_dump($Insert1);

    $Insert2 = new TestTable2();
    $Insert2->Test2Id = 2;
    $Insert2->TestId = 1;
    $Insert2->Test2Number = 12;
    $Insert2->Test2State = false;

    var_dump($Insert2);

    $Insert = TestTable2::Insert($Insert1, $Insert2)
        ->OnDuplicateKeyUpdate(
            TestTable2::Field('TestId'),
            TestTable2::Field('Test2Number'),
            TestTable2::Field('Test2State'),
        )
        ->Execute();

    var_dump($Insert);

    echo $Insert->Query;

    echo '<hr>';

    $Update1 = new TestTable2();
    $Update1->TestId = 1;
    $Update1->Test2Number = 11;
    $Update1->Test2State = true;

    var_dump($Update1);

    $Test2Id = [1, 2];

    $Update = TestTable2::Update($Update1)
        ->Where(fn (TestTable2 $x) => Where::In($x->Test2Id, $Test2Id))
        ->Execute();

    var_dump($Update);

    echo $Update->Query;

    echo '<hr>';

    $_GET['Test']['Test'] = true;

    $Object = new TestTable2();
    $Object->Test2State = false;

    $value['Test']['Test'] = true;

    $False = false;
    $True = true;

    $Like = 'a%';

    $From = new DateTime();
    $From->modify('-15 minutes');

    $To = new DateTime();
    $To->modify('+15 minutes');

    $Select = TestTable::LeftJoin(
        fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId
            || Where::Between($x->TestId, $y->Test2Number, $y->Test2Number)
    )
        ->Where(
            fn (TestTable $x, TestTable2 $y) => Where::Between($x->TestDateTime, $From, $To)
                || Where::Like($x->TestName, '%a%')
                || Where::NotLike($x->TestName, $Like)
                || Where::IsNull($y->Test2Number)
                || Where::IsNotNull($y->Test2Number)
                || Where::Between($y->Test2Number, 9, 12)
                || $y->Test2State == $value['Test']['Test']
                || $y->Test2State == $_GET['Test']['Test']
                || $y->Test2Number == 11
                || (
                    $y->Test2State == $y->Test2State
                    || $y->Test2State == $Object->Test2State
                    || $y->Test2State == $False
                    || $y->Test2State != false
                    || $y->Test2State
                    || !$y->Test2State
                )
        )
        ->OrderBy(fn (TestTable $x) => $x->TestName && OrderBy::Desc($x->TestDateTime))
        ->Select(fn (TestTable $x, TestTable2 $y): TestSelect => new TestSelect(
            TestId: $x->TestId,
            TestName: $x->TestName,
            TestDateTime: $x->TestDateTime,
            Test2Id: $y->Test2Id,
            Test2Number: $y->Test2Number
        ))
        ->ExecutePage(0, 1);

    var_dump($Select);

    echo $Select->Query;

    foreach ($Select as $Item) {
        var_dump($Item);
    }

    echo '<hr>';

    $Select = TestTable::InnerJoin(fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId && $y->Test2State == true)
        ->OrderBy(fn (TestTable $x) => $x->TestName)
        ->SelectField(TestTable::Field('TestId'), TestTable2::Field('Test2State'))
        ->SelectRaw('NOW() as Now')
        ->Execute();

    var_dump($Select);

    echo $Select->Query;

    foreach ($Select as $Item) {
        var_dump($Item->TestTable);
        var_dump($Item->TestId);
        var_dump($Item->TestTable2);
        var_dump($Item->Test2State);
        var_dump($Item->Now);

        var_dump($Item);
    }

    echo '<hr>';

    $Delete = TestTable::Delete()
        ->Where(fn (TestTable $x) => $x->TestId == 2)
        ->Execute();

    var_dump($Delete);

    echo $Delete->Query;

    echo '<hr>';

    $Select = TestTable2::Where(fn (TestTable2 $x) => $x->TestId > 0)
        ->WhereValue(Where::Equal(TestTable2::Field('Test2Id'), 10), Token::Or)
        ->WhereRaw('1 = ?', [1], Token::Or)
        ->SelectField(TestTable2::Field('Test2Id'))
        ->SelectRaw('NOW() as Now')
        ->OrderByValue(OrderBy::Desc(TestTable2::Field('Test2Id')))
        ->Execute();

    var_dump($Select);

    echo $Select->Query;

    foreach ($Select as $Item) {
        var_dump($Item);
    }

    echo '<hr>';

    $Select = TestTable::InnerJoin(fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId)
        ->Where(fn (TestTable $x) => $x->TestId > 0)
        ->Select(fn (TestTable2 $x): TestTable2 => $x)
        ->Execute();

    var_dump($Select);

    echo $Select->Query;

    foreach ($Select as $Item) {
        var_dump($Item);
    }

    echo '<hr>';

    $Select = TestTable::InnerJoin(fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId)
        ->Where(fn (TestTable $x) => $x->TestId > 0)
        ->Select(fn (TestTable $Test, TestTable2 $Test2): TestSelect2 => new TestSelect2(
            TestTable: $Test,
            TestTable2: $Test2
        ))
        ->Execute();

    var_dump($Select);

    echo $Select->Query;

    foreach ($Select as $Item) {
        var_dump($Item);
    }

    echo '<hr>';

    foreach ($Database->DatabaseTables as $Table) {
        $DropTable = $Table->DropTable()
            ->ExecuteQuery();

        var_dump($DropTable);

        echo $DropTable->Query;

        echo '<hr>';
    }
}
