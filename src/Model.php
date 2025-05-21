<?php

namespace LiliDb;

use Closure;
use DateTime;
use Exception;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\ITable;
use LiliDb\MySql\Attributes\Field as MySqlField;
use LiliDb\MySql\Attributes\Table as MySqlTable;
use LiliDb\PostgreSql\Attributes\Field as PostgreSqlField;
use LiliDb\PostgreSql\Attributes\Table as PostgreSqlTable;
use LiliDb\Query\Query;
use LiliDb\Query\QueryDelete;
use LiliDb\Query\QueryInsert;
use LiliDb\Query\QuerySelect;
use LiliDb\Query\QueryUpdate;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

trait Model
{
    protected static ReflectionClass $ReflectionClass;

    protected static ITable $Table;

    public function __toString(): string
    {
        $Json = [];

        foreach (static::$ReflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $FieldReflection) {
            if ($FieldReflection->isInitialized($this)) {
                $Value = $FieldReflection->getValue($this);

                if ($Value instanceof DateTime) {
                    $Value = $Value->format('c');
                }

                $Json[$FieldReflection->name] = $Value;
            }
        }

        return json_encode($Json);
    }

    public static function Initialize(ITable &$Table): void
    {
        static::$ReflectionClass = new ReflectionClass(static::class);

        $FieldClass = match ($Table::class) {
            MySqlTable::class => MySqlField::class,
            PostgreSqlTable::class => PostgreSqlField::class,
            default => throw new Exception($Table::class . ' not implemented')
        };

        foreach (static::$ReflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $FieldReflection) {
            $Field = $FieldReflection->getAttributes($FieldClass, ReflectionAttribute::IS_INSTANCEOF);

            if (!isset($Field[0])) {
                continue;
            }

            $Field = $Field[0]->newInstance();

            $Field->Table = $Table;
            $Field->Name ??= $FieldReflection->getName();
            $Field->FieldReflection = $FieldReflection;

            $Table->Fields[$FieldReflection->getName()] = $Field;
        }

        $Table->PrimaryKeys = array_filter(array_map(fn (IField $Field) => $Field->PrimaryKey ? $Field : null, $Table->Fields));

        static::$Table = $Table;
    }

    public static function New(mixed ...$Properties): self
    {
        $Item = new self();

        foreach ($Properties as $Name => $Value) {
            $Property = static::$ReflectionClass->getProperty($Name);

            $Property->setValue($Item, $Value);
        }

        return $Item;
    }

    public static function &Field(string $Name): IField
    {
        return static::$Table->Field($Name);
    }

    public static function CreateTable($CreateTable = true): Query
    {
        return static::$Table->CreateTable($CreateTable);
    }

    public static function TruncateTable(): Query
    {
        return static::$Table->TruncateTable();
    }

    public static function DropTable(): Query
    {
        return static::$Table->DropTable();
    }

    public static function AnyAll(): bool
    {
        return static::$Table->AnyAll();
    }

    public static function Any(Closure $Where): bool
    {
        return static::$Table->Any($Where);
    }

    public static function CountAll(): int
    {
        return static::$Table->CountAll();
    }

    public static function Count(Closure $Where): int
    {
        return static::$Table->Count($Where);
    }

    public static function GroupBy(Closure $GroupBy): QuerySelect
    {
        return static::$Table->GroupBy($GroupBy);
    }

    public static function InnerJoin(Closure $Join): QuerySelect
    {
        return static::$Table->InnerJoin($Join);
    }

    public static function LeftJoin(Closure $Join): QuerySelect
    {
        return static::$Table->LeftJoin($Join);
    }

    public static function RightJoin(Closure $Join): QuerySelect
    {
        return static::$Table->RightJoin($Join);
    }

    public static function CrossJoin(Closure $Join): QuerySelect
    {
        return static::$Table->CrossJoin($Join);
    }

    public static function OrderBy(Closure $OrderBy): QuerySelect
    {
        return static::$Table->OrderBy($OrderBy);
    }

    public static function OrderByValue(Value|IField $OrderBy): QuerySelect
    {
        return static::$Table->OrderByValue($OrderBy);
    }

    public static function WhereRaw(string $Where, array $Parameters = [], Token $Prefix = Token::And): QuerySelect
    {
        return static::$Table->WhereRaw($Where, $Parameters, $Prefix);
    }

    public static function Where(Closure $Where, Token $Prefix = Token::And): QuerySelect
    {
        return static::$Table->Where($Where, $Prefix);
    }

    public static function WhereValue(Value $Where, Token $Prefix = Token::And): QuerySelect
    {
        return static::$Table->WhereValue($Where, $Prefix);
    }

    public static function SelectRaw(string ...$Fields): QuerySelect
    {
        return static::$Table->SelectRaw(...$Fields);
    }

    public static function SelectField(IField ...$Fields): QuerySelect
    {
        return static::$Table->SelectField(...$Fields);
    }

    public static function Select(Closure $Select): QuerySelect
    {
        return static::$Table->Select($Select);
    }

    public static function Delete(): QueryDelete
    {
        return static::$Table->Delete();
    }

    public static function Insert(object ...$Items): QueryInsert
    {
        return static::$Table->Insert(...$Items);
    }

    public static function Update(object $Value): QueryUpdate
    {
        return static::$Table->Update($Value);
    }
}
