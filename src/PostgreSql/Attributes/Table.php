<?php

namespace LiliDb\PostgreSql\Attributes;

use Attribute;
use Closure;
use Exception;
use LiliDb\Database;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\ITable;
use LiliDb\PostgreSql\Query\QueryDelete;
use LiliDb\PostgreSql\Query\QueryInsert;
use LiliDb\PostgreSql\Query\QuerySelect;
use LiliDb\PostgreSql\Query\QueryUpdate;
use LiliDb\Query\Query;
use LiliDb\Token;
use LiliDb\Value;
use ReflectionClass;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Table implements ITable
{
    public Database $Database;

    public ReflectionClass $Reflection;

    public string $ModelName;

    public array $Fields = [];

    public array $PrimaryKeys = [];

    public function __construct(
        public string $Model,
        public string $Schema,
        public ?string $Name = null
    ) {
        $this->Reflection = new ReflectionClass($Model);
        $this->ModelName = $this->Reflection->getShortName();
    }

    public function __toString(): string
    {
        return $this->ModelName;
    }

    public function &Field(string $Name): Field
    {
        if (isset($this->Fields[$Name])) {
            return $this->Fields[$Name];
        }

        throw new Exception("Field with name '{$Name}' doesn't exist");
    }

    public function TableReference(): string
    {
        return "\"{$this->Schema}\".\"{$this->Name}\"";
    }

    public function CreateTable($IfNotExists = true): Query
    {
        $Fields = array_map(fn (Field $Field) => $Field->FieldDefinition(), $this->Fields);

        $PrimaryKeys = array_map(fn (Field $Field) => '"' . $Field->Name . '"', $this->PrimaryKeys);

        if (!empty($PrimaryKeys)) {
            $Fields[] = 'PRIMARY KEY (' . implode(', ', $PrimaryKeys) . ')';
        }

        $Fields = implode(', ', $Fields);

        $Query = new Query(
            Database: $this->Database
        );

        $Query->Query = 'CREATE TABLE ' . ($IfNotExists ? 'IF NOT EXISTS' : '') . " {$this->TableReference()} ({$Fields})";

        return $Query;
    }

    public function TruncateTable(): Query
    {
        $Query = new Query(
            Database: $this->Database
        );

        $Query->Query = "TRUNCATE TABLE {$this->TableReference()}";

        return $Query;
    }

    public function DropTable(): Query
    {
        $Query = new Query(
            Database: $this->Database
        );

        $Query->Query = "DROP TABLE {$this->TableReference()}";

        return $Query;
    }

    public function AnyAll(): bool
    {
        $Result = new QuerySelect(Table: $this)
            ->SelectRaw('true')
            ->Execute(null, 1);

        return !$Result->EOF;
    }

    public function Any(Closure $Where): bool
    {
        $Result = new QuerySelect(Table: $this)
            ->Where($Where)
            ->SelectRaw('true')
            ->Execute(null, 1);

        return !$Result->EOF;
    }

    public function CountAll(): int
    {
        $Result = new QuerySelect(Table: $this)
            ->SelectRaw('COUNT(*) AS Count')
            ->Execute();

        return $Result->Count ?? throw new Exception('Failed to Count... ' . $Result->Error?->getMessage(), previous: $Result->Error);
    }

    public function Count(Closure $Where): int
    {
        $Result = new QuerySelect(Table: $this)
            ->Where($Where)
            ->SelectRaw('COUNT(*) AS Count')
            ->Execute();

        return $Result->Count ?? throw new Exception('Failed to Count... ' . $Result->Error?->getMessage(), previous: $Result->Error);
    }

    public function GroupBy(Closure $GroupBy): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->GroupBy($GroupBy);
    }

    public function InnerJoin(Closure $Join): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->InnerJoin($Join);
    }

    public function LeftJoin(Closure $Join): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->LeftJoin($Join);
    }

    public function RightJoin(Closure $Join): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->RightJoin($Join);
    }

    public function CrossJoin(Closure $Join): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->CrossJoin($Join);
    }

    public function OrderBy(Closure $OrderBy): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->OrderBy($OrderBy);
    }

    public function OrderByValue(Value|IField $OrderBy): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->OrderByValue($OrderBy);
    }

    public function WhereRaw(string $Where, array $Parameters = [], Token $Prefix = Token::And): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->WhereRaw($Where, $Parameters, $Prefix);
    }

    public function Where(Closure $Where, Token $Prefix = Token::And): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->Where($Where, $Prefix);
    }

    public function WhereValue(Value $Where, Token $Prefix = Token::And): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->WhereValue($Where, $Prefix);
    }

    public function SelectRaw(string ...$Fields): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->SelectRaw(...$Fields);
    }

    public function SelectField(IField ...$Fields): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->SelectField(...$Fields);
    }

    public function Select(Closure $Select): QuerySelect
    {
        return new QuerySelect(
            Table: $this
        )
            ->Select($Select);
    }

    public function Delete(): QueryDelete
    {
        return new QueryDelete(
            $this
        );
    }

    public function Insert(object ...$Items): QueryInsert
    {
        return new QueryInsert(
            $this,
            ...$Items
        );
    }

    public function Update(object $Value): QueryUpdate
    {
        return new QueryUpdate(
            $this,
            $Value
        );
    }
}
