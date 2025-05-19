<?php

namespace LiliDb\Tests\Models;

use DateTime;
use LiliDb\Model;
use LiliDb\MySql\Attributes\Field as MySqlField;
use LiliDb\MySql\Attributes\Key as MySqlKey;
use LiliDb\MySql\Types\Numeric\DbBoolean as MySqlBoolean;
use LiliDb\MySql\Types\String\DbVarchar as MySqlVarchar;
use LiliDb\MySql\Types\Time\DbDateTime;
use LiliDb\PostgreSql\Attributes\Field as PostgreSqlField;
use LiliDb\PostgreSql\Attributes\Key as PostgreSqlKey;
use LiliDb\PostgreSql\Types\DbBoolean as PostgreBoolean;
use LiliDb\PostgreSql\Types\String\DbVarchar as PostgreSqlVarchar;
use LiliDb\PostgreSql\Types\Time\DbTimestamp;
use LiliDb\Token;

class TestTable
{
    use Model;

    #[MySqlKey(Name: 'Id')]
    #[PostgreSqlKey(Name: 'Id')]
    public ?int $TestId;

    #[MySqlField(Type: new MySqlVarchar(50), Name: 'Name', Default: 'Test')]
    #[PostgreSqlField(Type: new PostgreSqlVarchar(50), Name: 'TestName', Default: 'Test')]
    public ?string $TestName;

    #[MySqlField(Type: new DbDateTime(), Default: Token::DateTimeNow)]
    #[PostgreSqlField(Type: new DbTimestamp(), Default: Token::DateTimeNow)]
    public ?DateTime $TestDateTime;

    #[MySqlField(
        Name: 'State',
        Type: new MySqlBoolean(),
        Default: true,
    )]
    #[PostgreSqlField(
        Name: 'State',
        Type: new PostgreBoolean(),
        Default: true,
    )]
    public ?bool $State;
}
