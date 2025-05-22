<p align="center">
  <img title="Lilith" src="https://github.com/sebastianguzmanmorla/LiliDb/blob/main/lili.svg?raw=true" />
</p>

# LiliDb

**A Php ORM with extensive usage of closures, reflection and attributes, taking advantage of Php modern features**

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

## Create Table

```php
foreach ($Database->DatabaseTables as $Table) {
  $CreateTable = $Table->CreateTable()
    ->ExecuteQuery();

  echo $CreateTable->Query;
}
```
Or
```php
$CreateTable = TestTable::CreateTable()
  ->ExecuteQuery();

echo $CreateTable->Query;
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

## Truncate Table

```php
foreach ($Database->DatabaseTables as $Table) {
  $TruncateTable = $Table->TruncateTable()
    ->ExecuteQuery();

  echo $TruncateTable->Query;
}
```
Or
```php
$TruncateTable = TestTable::TruncateTable()
  ->ExecuteQuery();

echo $TruncateTable->Query;
```

MySql query execution:
```sql
TRUNCATE TABLE `A`
```

PostgreSql query execution:
```sql
TRUNCATE TABLE "public"."TestTable"
```

## Drop Table

```php
foreach ($Database->DatabaseTables as $Table) {
  $DropTable = $Table->DropTable()
    ->ExecuteQuery();

  echo $DropTable->Query;
}
```
Or
```php
$DropTable = TestTable::DropTable()
  ->ExecuteQuery();

echo $DropTable->Query;
```

MySql query execution:
```sql
DROP TABLE `A`
```

PostgreSql query execution:
```sql
DROP TABLE "public"."TestTable"
```

## Insert

```php
$Insert1 = TestTable::New(
    TestId: 1,
    TestName: 'a',
    TestDateTime: new DateTime(),
);

$Insert2 = new TestTable();
$Insert2->TestId = 2;
$Insert2->TestName = 'b';
$Insert2->TestDateTime = new DateTime();

$Insert = TestTable::Insert($Insert1, $Insert2)
    ->OnDuplicateKeyUpdate(
        TestTable::Field('TestName'),
        TestTable::Field('TestDateTime')
    )
    ->Execute();

echo $Insert->Query;
```

MySql query execution:
```sql
INSERT INTO `A` (`Id`, `Name`, `TestDateTime`)
VALUES
  (1, 'a', '2025-05-22 03:18:51'),
  (2, 'b', '2025-05-22 03:18:51') as new
ON DUPLICATE KEY UPDATE
  `Name` = new.`Name`,
  `TestDateTime` = new.`TestDateTime`
```

PostgreSql query execution:
```sql
INSERT INTO "public"."TestTable" ("Id", "TestName", "TestDateTime") 
VALUES 
  (1, 'a', '2025-05-22 03:18:51'), 
  (2, 'b', '2025-05-22 03:18:51')
ON CONFLICT ("Id") DO UPDATE SET 
  "TestName" = EXCLUDED."TestName", 
  "TestDateTime" = EXCLUDED."TestDateTime"
```

## Update

```php
$Update1 = new TestTable();
$Update1->TestName = 'a';
$Update1->TestDateTime = new DateTime();

$Update = TestTable::Update($Update1)
    ->Where(fn (TestTable $x) => $x->TestId == 1)
    ->Execute();

echo $Update->Query;
```

MySql query execution:
```sql
UPDATE 
  `A` 
SET 
  `A`.`Name` = 'a', 
  `A`.`TestDateTime` = '2025-05-22 03:18:51' 
WHERE 
  (`A`.`Id` = 1)
```

PostgreSql query execution:
```sql
UPDATE 
  "public"."TestTable" 
SET 
  "TestName" = 'a', 
  "TestDateTime" = '2025-05-22 03:18:51' 
WHERE 
  ("TestTable"."Id" = 1)
```

## Delete

```php
$Delete = TestTable::Delete()
    ->Where(fn (TestTable $x) => $x->TestId == 2)
    ->Execute();

echo $Delete->Query;
```

MySql query execution:
```sql
DELETE FROM `A` 
WHERE 
  (`A`.`Id` = 2)
```

PostgreSql query execution:
```sql
DELETE FROM "public"."TestTable" 
WHERE 
  ("TestTable"."Id" = 2)
```

## Select

### Select With Raw Fields

```php
$Select = TestTable::InnerJoin(fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId && $y->Test2State)
  ->OrderBy(fn (TestTable $x) => $x->TestName)
  ->SelectField(TestTable::Field('TestId'), TestTable2::Field('Test2State'))
  ->SelectRaw('NOW() as Now')
  ->Execute();

foreach ($Select as $Item) {
  var_dump($Item);
}
```
Output:
```
object(LiliDb\ResultRow)[267]
  public 'TestTable' => 
    object(LiliDb\Tests\Models\TestTable)[213]
      public ?int 'TestId' => int 1
      public ?string 'TestName' => *uninitialized*
      public ?DateTime 'TestDateTime' => *uninitialized*
      public ?bool 'State' => *uninitialized*
  public 'TestTable2' => 
    object(LiliDb\Tests\Models\TestTable2)[221]
      public ?int 'Test2Id' => *uninitialized*
      public ?int 'TestId' => *uninitialized*
      public ?int 'Test2Number' => *uninitialized*
      public ?bool 'Test2State' => boolean true
  public 'Now' => string '2025-05-22 03:43:53' (length=19)
```

MySql query execution:
```sql
SELECT 
  `A`.`Id`, 
  `B`.`D`, 
  NOW() as Now 
FROM `A` 
INNER JOIN `B` ON `B`.`B` = `A`.`Id` AND `B`.`D` = TRUE 
ORDER BY 
  `A`.`Name`
```

PostgreSql query execution:
```sql
SELECT 
  "TestTable"."Id", 
  "TestTable2"."Test2State", 
  NOW() as Now 
FROM "public"."TestTable" 
INNER JOIN "public"."TestTable2" ON "TestTable2"."TestId" = "TestTable"."Id" AND "TestTable2"."Test2State" = TRUE 
ORDER BY 
  "TestTable"."TestName"
```

### Select a table

```php
$Select = TestTable::InnerJoin(fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId)
    ->Where(fn (TestTable $x) => $x->TestId > 0)
    ->Select(fn (TestTable2 $x): TestTable2 => $x)
    ->Execute();

foreach ($Select as $Item) {
    var_dump($Item);
}
```
Output:
```
object(LiliDb\Tests\Models\TestTable2)[317]
  public ?int 'Test2Id' => int 1
  public ?int 'TestId' => int 1
  public ?int 'Test2Number' => int 11
  public ?bool 'Test2State' => boolean true

object(LiliDb\Tests\Models\TestTable2)[236]
  public ?int 'Test2Id' => int 2
  public ?int 'TestId' => int 1
  public ?int 'Test2Number' => int 11
  public ?bool 'Test2State' => boolean true
```

MySql query execution:
```sql
SELECT 
  `B`.`Id`, 
  `B`.`B`, 
  `B`.`C`, 
  `B`.`D` 
FROM `A` 
INNER JOIN `B` ON `B`.`B` = `A`.`Id` 
WHERE 
  (`A`.`Id` > 0)
```

PostgreSql query execution:
```sql
SELECT 
  "TestTable2"."Test2Id", 
  "TestTable2"."TestId", 
  "TestTable2"."Test2Number", 
  "TestTable2"."Test2State" 
FROM "public"."TestTable" 
INNER JOIN "public"."TestTable2" ON "TestTable2"."TestId" = "TestTable"."Id" 
WHERE 
  ("TestTable"."Id" > 0)
```

### Select a custom class

```php
use DateTime;

class TestSelect
{
    public function __construct(
        public ?int $TestId,
        public ?string $TestName,
        public ?DateTime $TestDateTime,
        public ?int $Test2Id,
        public ?int $Test2Number,
    ) {
    }
}
```
[TestSelect](tests/Models/TestSelect.php)

```php
$Select = TestTable::InnerJoin(fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId)
    ->Where(fn (TestTable $x) => $x->TestId > 0)
    ->Select(fn (TestTable $x, TestTable2 $y): TestSelect => new TestSelect(
        TestId: $x->TestId,
        TestName: $x->TestName,
        TestDateTime: $x->TestDateTime,
        Test2Id: $y->Test2Id,
        Test2Number: $y->Test2Number
    ))
    ->Execute();

foreach ($Select as $Item) {
    var_dump($Item);
}
```
Output:
```
object(LiliDb\Tests\Models\TestSelect)[246]
  public ?int 'TestId' => int 1
  public ?string 'TestName' => string 'a' (length=21)
  public ?DateTime 'TestDateTime' => 
    object(DateTime)[286]
      public 'date' => string '2025-05-22 03:58:42.000000' (length=26)
      public 'timezone_type' => int 3
      public 'timezone' => string 'UTC' (length=3)
  public ?int 'Test2Id' => int 1
  public ?int 'Test2Number' => int 11

object(LiliDb\Tests\Models\TestSelect)[119]
  public ?int 'TestId' => int 1
  public ?string 'TestName' => string 'a' (length=21)
  public ?DateTime 'TestDateTime' => 
    object(DateTime)[247]
      public 'date' => string '2025-05-22 03:58:42.000000' (length=26)
      public 'timezone_type' => int 3
      public 'timezone' => string 'UTC' (length=3)
  public ?int 'Test2Id' => int 2
  public ?int 'Test2Number' => int 11
```

MySql query execution:
```sql
SELECT 
  `A`.`Id`, 
  `A`.`Name`, 
  `A`.`TestDateTime`, 
  `B`.`Id`, 
  `B`.`C` 
FROM `A` 
INNER JOIN `B` ON `B`.`B` = `A`.`Id` 
WHERE 
  (`A`.`Id` > 0)
```

PostgreSql query execution:
```sql
SELECT 
  "TestTable"."Id", 
  "TestTable"."TestName", 
  "TestTable"."TestDateTime", 
  "TestTable2"."Test2Id", 
  "TestTable2"."Test2Number" 
FROM "public"."TestTable" 
INNER JOIN "public"."TestTable2" ON "TestTable2"."TestId" = "TestTable"."Id" 
WHERE 
  ("TestTable"."Id" > 0)
```

### Complex Select

```php

use LiliDb\OrderBy;
use LiliDb\Where;

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

$Select = TestTable
    ::LeftJoin(
        fn (TestTable2 $y, TestTable $x) => $y->TestId == $x->TestId || Where::Between($x->TestId, $y->Test2Number, $y->Test2Number)
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
```
MySql query execution:
```sql
SELECT 
  `A`.`Id`, 
  `A`.`Name`, 
  `A`.`TestDateTime`, 
  `B`.`Id`, 
  `B`.`C` 
FROM 
  `A` 
LEFT JOIN `B` ON `B`.`B` = `A`.`Id` OR `A`.`Id` BETWEEN `B`.`C` AND `B`.`C` 
WHERE 
  (
    `A`.`TestDateTime` BETWEEN '2025-05-22 03:52:25' AND '2025-05-22 04:22:25' 
    OR `A`.`Name` LIKE '%a%' 
    OR `A`.`Name` NOT LIKE 'a%' 
    OR `B`.`C` IS NULL 
    OR `B`.`C` IS NOT NULL 
    OR `B`.`C` BETWEEN 9 AND 12 
    OR `B`.`D` = TRUE 
    OR `B`.`D` = TRUE 
    OR `B`.`C` = 11 
    OR (
      `B`.`D` = `B`.`D` 
      OR `B`.`D` = FALSE 
      OR `B`.`D` = FALSE 
      OR `B`.`D` <> FALSE 
      OR `B`.`D` = TRUE 
      OR `B`.`D` = FALSE
    )
  ) 
ORDER BY 
  `A`.`Name`, 
  `A`.`TestDateTime` DESC 
LIMIT 0, 1
```

PostgreSql query execution:
```sql
SELECT 
  "TestTable"."Id", 
  "TestTable"."TestName", 
  "TestTable"."TestDateTime", 
  "TestTable2"."Test2Id", 
  "TestTable2"."Test2Number" 
FROM "public"."TestTable" 
LEFT JOIN "public"."TestTable2" ON "TestTable2"."TestId" = "TestTable"."Id" OR "TestTable"."Id" BETWEEN "TestTable2"."Test2Number" AND "TestTable2"."Test2Number" 
WHERE 
  (
    "TestTable"."TestDateTime" BETWEEN '2025-05-22 03:52:25' AND '2025-05-22 04:22:25'
    OR "TestTable"."TestName" LIKE '%a%' 
    OR "TestTable"."TestName" NOT LIKE 'a%' 
    OR "TestTable2"."Test2Number" IS NULL 
    OR "TestTable2"."Test2Number" IS NOT NULL 
    OR "TestTable2"."Test2Number" BETWEEN 9 AND 12
    OR "TestTable2"."Test2State" = TRUE 
    OR "TestTable2"."Test2State" = TRUE 
    OR "TestTable2"."Test2Number" = 11 
    OR (
      "TestTable2"."Test2State" = "TestTable2"."Test2State" 
      OR "TestTable2"."Test2State" = FALSE 
      OR "TestTable2"."Test2State" = FALSE 
      OR "TestTable2"."Test2State" <> FALSE 
      OR "TestTable2"."Test2State" = TRUE 
      OR "TestTable2"."Test2State" = FALSE
    )
  ) 
ORDER BY 
  "TestTable"."TestName", 
  "TestTable"."TestDateTime" DESC 
LIMIT 
  1 OFFSET 0
```
