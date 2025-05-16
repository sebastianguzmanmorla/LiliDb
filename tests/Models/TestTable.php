<?php

namespace LiliDb\Tests\Models;

use DateTime;
use LiliDb\Model;
use LiliDb\MySql\Attributes\Field as MySqlField;
use LiliDb\MySql\Attributes\Key as MySqlKey;
use LiliDb\MySql\Types\String\DbVarchar as MySqlVarchar;
use LiliDb\MySql\Types\Time\DbDateTime;
use LiliDb\PostgreSql\Attributes\Field as PostgreSqlField;
use LiliDb\PostgreSql\Attributes\Key as PostgreSqlKey;
use LiliDb\PostgreSql\Types\String\DbVarchar as PostgreSqlVarchar;
use LiliDb\PostgreSql\Types\Time\DbTimestamp;

class TestTable
{
    use Model;

    #[MySqlKey(FieldName: 'Id')]
    #[PostgreSqlKey(FieldName: 'Id')]
    public ?int $TestId;

    #[MySqlField(FieldType: new MySqlVarchar(50), FieldName: 'Name')]
    #[PostgreSqlField(FieldType: new PostgreSqlVarchar(50), FieldName: 'TestName')]
    public ?string $TestName;

    #[MySqlField(FieldType: new DbDateTime())]
    #[PostgreSqlField(FieldType: new DbTimestamp())]
    public ?DateTime $TestDateTime;
}
