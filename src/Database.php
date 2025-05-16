<?php

namespace LiliDb;

use Exception;
use LiliDb\Interfaces\IConnection;
use LiliDb\Interfaces\ITable;
use LiliDb\MySql\Attributes\Table as MySqlTable;
use LiliDb\MySql\Connection as MySqlConnection;
use LiliDb\PostgreSql\Attributes\Table as PostgreSqlTable;
use LiliDb\PostgreSql\Connection as PostgreSqlConnection;
use LiliDb\Query\Query;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

class Database
{
    public ?string $DatabaseName = null;

    public array $DatabaseTables = [];

    public IConnection $Connection;

    public ?Query $Query = null;

    public function __construct(IConnection $Connection, ?string $DatabaseName = null)
    {
        $ReflectionClass = new ReflectionClass(static::class);

        $this->Connection = $Connection;

        $this->DatabaseName ??= $DatabaseName ?? $ReflectionClass->getShortName();

        $TableClass = match ($Connection::class) {
            MySqlConnection::class => MySqlTable::class,
            PostgreSqlConnection::class => PostgreSqlTable::class,
            default => throw new Exception($Connection::class . ' not implemented')
        };

        foreach ($ReflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $DatabaseProperty) {
            $Table = $DatabaseProperty->getAttributes($TableClass, ReflectionAttribute::IS_INSTANCEOF);

            if (!isset($Table[0])) {
                continue;
            }

            $Table = $Table[0]->newInstance();

            $Table->Database = $this;

            $Table->TableName ??= $DatabaseProperty->getName();

            $Table->TableReflection->newInstanceWithoutConstructor()::Initialize($Table);

            $DatabaseProperty->setValue($this, $Table);

            $this->DatabaseTables[$DatabaseProperty->getName()] = $Table;
        }
    }

    public function RawQuery(string $Sql, array $Parameters = []): Query
    {
        $Query = new Query(
            Database: $this
        );

        $Query->Query = $Sql;
        $Query->Parameters = $Parameters;

        return $Query;
    }

    public function DatabaseTable(string $Name): ?ITable
    {
        if (!isset($this->DatabaseTables[$Name])) {
            return null;
        }

        return $this->DatabaseTables[$Name];
    }
}
