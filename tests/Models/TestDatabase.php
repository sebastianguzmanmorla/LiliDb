<?php

namespace LiliDb\Tests\Models;

use LiliDb\Database;
use LiliDb\Interfaces\ITable;
use LiliDb\MySql\Attributes\Table as MySqlTable;
use LiliDb\PostgreSql\Attributes\Table as PostgreSqlTable;

class TestDatabase extends Database
{
    #[MySqlTable(Model: TestTable::class, TableName: 'A')]
    #[PostgreSqlTable(Model: TestTable::class, Schema: 'public', TableName: 'TestTable')]
    public ITable $TestTable;

    #[MySqlTable(Model: TestTable2::class, TableName: 'B')]
    #[PostgreSqlTable(Model: TestTable2::class, Schema: 'public', TableName: 'TestTable2')]
    public ITable $TestTable2;
}
