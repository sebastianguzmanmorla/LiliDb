<?php

namespace LiliDb\Tests;

use LiliDb\PostgreSql\Connection;
use LiliDb\Tests\Models\TestDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('PostgreSql Test')]
#[RequiresPhpExtension('pgsql')]
final class PostgreSqlTest extends CrossTestCase
{
    public static function setUpBeforeClass(): void
    {
        $DatabaseConnection = new Connection(
            Hostname: getenv('POSTGRES_HOST'),
            Port: getenv('POSTGRES_PORT'),
            Database: getenv('POSTGRES_DB'),
            Username: getenv('POSTGRES_USER'),
            Password: getenv('POSTGRES_PASSWORD')
        );

        self::$Database = new TestDatabase($DatabaseConnection, getenv('POSTGRES_DB'));

        self::InitData();
    }
}
