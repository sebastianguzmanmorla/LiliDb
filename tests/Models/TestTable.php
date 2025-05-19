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

    #[MySqlKey(FieldName: 'Id')]
    #[PostgreSqlKey(FieldName: 'Id')]
    public ?int $TestId;

    #[MySqlField(FieldType: new MySqlVarchar(50), FieldName: 'Name', FieldDefault: 'Test')]
    #[PostgreSqlField(FieldType: new PostgreSqlVarchar(50), FieldName: 'TestName', FieldDefault: 'Test')]
    public ?string $TestName;

    #[MySqlField(FieldType: new DbDateTime(), FieldDefault: Token::DateTimeNow)]
    #[PostgreSqlField(FieldType: new DbTimestamp(), FieldDefault: Token::DateTimeNow)]
    public ?DateTime $TestDateTime;

    #[MySqlField(
        FieldName: 'State',
        FieldType: new MySqlBoolean(),
        FieldDefault: true,
    )]
    #[PostgreSqlField(
        FieldName: 'State',
        FieldType: new PostgreBoolean(),
        FieldDefault: true,
    )]
    public ?bool $State;
}
