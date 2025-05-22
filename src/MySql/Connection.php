<?php

namespace LiliDb\MySql;

use LiliDb\Exceptions\DatabaseException;
use LiliDb\Interfaces\IConnection;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\IStatement;
use LiliDb\Query\Query;
use mysqli;
use SensitiveParameter;
use Throwable;

class Connection implements IConnection
{
    public mysqli|false $Client;

    public bool $Connected {
        get => $_Connected;
    }

    private bool $_Connected = false;

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
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->Client = mysqli_init();
        $this->Client->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    }

    public function Connect(): void
    {
        if (!$this->_Connected) {
            try {
                $this->_Connected = $this->Client->real_connect(
                    hostname: $this->Hostname,
                    username: $this->Username,
                    password: $this->Password,
                    database: $this->Database,
                    port: $this->Port
                );
            } catch (Throwable $ex) {
                $this->_Connected = false;

                throw new DatabaseException('Database connection failed', previous: $ex);
            }
        }
    }

    public function Prepare(Query &$Query, ?IField $Field = null): IStatement
    {
        $this->Connect();

        return new Statement($this, $Query, $Field);
    }

    public function Error(): string
    {
        return $this->Client->error;
    }

    public function Close(): void
    {
        $this->Client->close();
    }

    public function Status(): string
    {
        try {
            return $this->Client->stat();
        } catch (Throwable $ex) {
            return $ex->getMessage();
        }
    }
}
