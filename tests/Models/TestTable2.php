<?php

namespace LiliDb\Tests\Models;

use LiliDb\Model;
use LiliDb\MySql\Attributes\Field as MySqlField;
use LiliDb\MySql\Attributes\Key as MySqlKey;
use LiliDb\MySql\Types\Numeric\DbBoolean as MySqlBoolean;
use LiliDb\MySql\Types\Numeric\DbInt;
use LiliDb\PostgreSql\Attributes\Field as PostgreSqlField;
use LiliDb\PostgreSql\Attributes\Key as PostgreSqlKey;
use LiliDb\PostgreSql\Types\DbBoolean as PostgreSqlBoolean;
use LiliDb\PostgreSql\Types\Numeric\DbInteger;

class TestTable2
{
    use Model;

    #[MySqlKey(FieldName: 'Id')]
    #[PostgreSqlKey(FieldName: 'Test2Id')]
    public ?int $Test2Id;

    #[MySqlField(FieldType: new DbInt(11, true), FieldName: 'B')]
    #[PostgreSqlField(FieldType: new DbInteger(), FieldName: 'TestId')]
    public ?int $TestId;

    #[MySqlField(FieldType: new DbInt(11, true), FieldName: 'C')]
    #[PostgreSqlField(FieldType: new DbInteger(), FieldName: 'Test2Number')]
    public ?int $Test2Number;

    #[MySqlField(FieldType: new MySqlBoolean(), FieldName: 'D')]
    #[PostgreSqlField(FieldType: new PostgreSqlBoolean(), FieldName: 'Test2State')]
    public ?bool $Test2State;
}
