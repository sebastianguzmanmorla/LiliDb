<?php

namespace LiliDb\Tests;

use LiliDb\MySql\Connection;
use LiliDb\Tests\Models\TestDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('MySql Test')]
#[RequiresPhpExtension('mysqli')]
final class MySqlTest extends CrossTestCase
{
    public static function setUpBeforeClass(): void
    {
        $DatabaseConnection = new Connection(
            Hostname: getenv('MYSQL_HOST'),
            Port: getenv('MYSQL_PORT'),
            Database: getenv('MYSQL_DATABASE'),
            Username: getenv('MYSQL_USERNAME'),
            Password: getenv('MYSQL_PASSWORD')
        );

        self::$Database = new TestDatabase($DatabaseConnection, getenv('MYSQL_DATABASE'));

        self::InitData();
    }
}
