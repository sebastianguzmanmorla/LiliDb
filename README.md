<p align="center">
  <img title="Lilith" src="https://github.com/sebastianguzmanmorla/LiliDb/blob/develop/lili.svg?raw=true" />
</p>

# LiliDb

**A Php ORM with extensive usage of closures, reflection and attributes, taking advantage of Php 8.4 modern features**

*Compatible with Mysql and PostgreSQL*

## Model Definition

### Tables

To define a table, it must have the trait class **LiliDb\\Model** and each property use the attributes **LiliDb\\*\\Attributes\\Field** or **LiliDb\\*\\Attributes\\Key** to map it to the table fields

```php
<?php

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
```

[TestTable Example](tests/Models/TestTable.php)

[TestTable2 Example](tests/Models/TestTable2.php)

### Database Schema

To define a database, it must extends the class **LiliDb\\Database** and each property reference a table using the attributes **LiliDb\\*\\Attributes\\Table**

```php
<?php

use TestTable;
use LiliDb\Database;
use LiliDb\Interfaces\ITable;
use LiliDb\MySql\Attributes\Table as MySqlTable;
use LiliDb\PostgreSql\Attributes\Table as PostgreSqlTable;

class TestDatabase extends Database
{
    #[MySqlTable(Model: TestTable::class, Name: 'A')]
    #[PostgreSqlTable(Model: TestTable::class, Schema: 'public', Name: 'TestTable')]
    public ITable $TestTable;
}
```

[TestDatabase Example](tests/Models/TestDatabase.php)

## Database Connection

To define a connection is necessary to instance a **LiliDb\\*\\Connection** class

MySql connection:
```php
<?php

use TestDatabase;
use LiliDb\MySql\Connection as MySqlConnection;

$Connection = new MySqlConnection(
  Hostname: getenv('MYSQL_HOST'),
  Port: getenv('MYSQL_PORT'),
  Database: getenv('MYSQL_DATABASE'),
  Username: getenv('MYSQL_USERNAME'),
  Password: getenv('MYSQL_PASSWORD')
);

$Database = new TestDatabase($Connection, getenv('MYSQL_DATABASE'));
```

PostgreSql connection:
```php
<?php

use TestDatabase;
use LiliDb\PostgreSql\Connection as PostgreSqlConnection;

$Connection = new PostgreSqlConnection(
  Hostname: getenv('POSTGRES_HOST'),
  Port: getenv('POSTGRES_PORT'),
  Database: getenv('POSTGRES_DATABASE'),
  Username: getenv('POSTGRES_USERNAME'),
  Password: getenv('POSTGRES_PASSWORD')
);

$Database = new TestDatabase($Connection, getenv('POSTGRES_DATABASE'));
```

## Auto Create Table

```php
foreach ($Database->DatabaseTables as $Table) {
  $CreateTable = $Table->CreateTable()
    ->ExecuteQuery();

  echo $CreateTable->Query;
}
```
MySql query execution:

```sql
CREATE TABLE IF NOT EXISTS `A` (
  `Id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, 
  `Name` VARCHAR(50) NOT NULL DEFAULT 'Test', 
  `TestDateTime` DATETIME NOT NULL DEFAULT NOW(), 
  `State` TINYINT(1) NOT NULL DEFAULT TRUE, 
  PRIMARY KEY (`Id`)
)
```

PostgreSql query execution:
```sql
CREATE TABLE IF NOT EXISTS "public"."TestTable" (
  "Id" bigserial NOT NULL, 
  "TestName" varchar(50) NOT NULL DEFAULT 'Test', 
  "TestDateTime" timestamp NOT NULL DEFAULT NOW(), 
  "State" boolean NOT NULL DEFAULT TRUE, 
  PRIMARY KEY ("Id")
)
```
