<?php

namespace LiliDb\PostgreSql;

use ErrorException;
use Exception;
use LiliDb\Interfaces\IConnection;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\IStatement;
use LiliDb\Query\Query;
use PgSql\Connection as PgConnection;
use SensitiveParameter;
use Throwable;

class Connection implements IConnection
{
    public PgConnection|false $Client = false;

    public bool $Connected
        {
        get => $this->Client != false && pg_connection_status($this->Client) === PGSQL_CONNECTION_OK;
    }

    public function __construct(
        #[SensitiveParameter]
        public ?string $Hostname,
        #[SensitiveParameter]
        public ?int $Port,
        #[SensitiveParameter]
        public ?string $Database,
        #[SensitiveParameter]
        public ?string $Username,
        #[SensitiveParameter]
        public ?string $Password
    ) {
    }

    public function Connect(): void
    {
        if ($this->Connected == false) {
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                if (0 === error_reporting()) {
                    return false;
                }

                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            });

            try {
                $this->Client = pg_connect(
                    (null === $this->Hostname ? '' : "host={$this->Hostname} ")
                    . (null === $this->Port ? '' : "port={$this->Port} ")
                    . (null === $this->Database ? '' : "dbname={$this->Database} ")
                    . (null === $this->Username ? '' : "user={$this->Username} ")
                    . (null === $this->Password ? '' : "password={$this->Password} ")
                );

                pg_set_client_encoding($this->Client, 'utf8');
            } catch (Throwable $ex) {
                throw new Exception('Database connection failed: ' . $ex->getMessage());
            }

            restore_error_handler();
        }
    }

    public function Prepare(Query &$Query, ?IField $Field = null): IStatement
    {
        $this->Connect();

        return new Statement($this, $Query, $Field);
    }

    public function Error(): string
    {
        return pg_last_error($this->Client);
    }

    public function Close(): void
    {
        pg_close($this->Client);
    }

    public function Status(): string
    {
        try {
            return match (pg_connection_status($this->Client)) {
                PGSQL_CONNECTION_OK => 'Database connection is in a valid state',
                PGSQL_CONNECTION_BAD => 'Database connection is in an invalid state',
                PGSQL_CONNECTION_AUTH_OK => 'PGSQL_CONNECTION_AUTH_OK',
                PGSQL_CONNECTION_AWAITING_RESPONSE => 'PGSQL_CONNECTION_AUTH_OK',
                PGSQL_CONNECTION_MADE => 'PGSQL_CONNECTION_AUTH_OK',
                PGSQL_CONNECTION_SETENV => 'PGSQL_CONNECTION_AUTH_OK',
                PGSQL_CONNECTION_STARTED => 'PGSQL_CONNECTION_AUTH_OK',
                default => 'FAIL'
            };
        } catch (Throwable $ex) {
            return $ex->getMessage();
        }
    }
}
