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

    #[MySqlKey(Name: 'Id')]
    #[PostgreSqlKey(Name: 'Test2Id')]
    public ?int $Test2Id;

    #[MySqlField(Type: new DbInt(11, true), Name: 'B')]
    #[PostgreSqlField(Type: new DbInteger(), Name: 'TestId')]
    public ?int $TestId;

    #[MySqlField(Type: new DbInt(11, true), Name: 'C')]
    #[PostgreSqlField(Type: new DbInteger(), Name: 'Test2Number')]
    public ?int $Test2Number;

    #[MySqlField(Type: new MySqlBoolean(), Name: 'D')]
    #[PostgreSqlField(Type: new PostgreSqlBoolean(), Name: 'Test2State')]
    public ?bool $Test2State;
}
