<?php

namespace LiliDb\Interfaces;

use Closure;
use LiliDb\Query\Query;
use LiliDb\Query\QueryDelete;
use LiliDb\Query\QueryInsert;
use LiliDb\Query\QuerySelect;
use LiliDb\Query\QueryUpdate;
use LiliDb\Token;
use LiliDb\Value;

interface ITable
{
    public function &Field(string $Name): IField;

    public function TableReference(): string;

    public function CreateTable($IfNotExists = true): Query;

    public function TruncateTable(): Query;

    public function DropTable(): Query;

    public function AnyAll(): bool;

    public function Any(Closure $Where): bool;

    public function CountAll(): int;

    public function Count(Closure $Where): int;

    public function GroupBy(Closure $GroupBy): QuerySelect;

    public function InnerJoin(Closure $Join): QuerySelect;

    public function LeftJoin(Closure $Join): QuerySelect;

    public function RightJoin(Closure $Join): QuerySelect;

    public function CrossJoin(Closure $Join): QuerySelect;

    public function OrderBy(Closure $OrderBy): QuerySelect;

    public function OrderByValue(Value|IField $OrderBy): QuerySelect;

    public function WhereRaw(string $Where, array $Parameters = [], Token $Prefix = Token::And): QuerySelect;

    public function Where(Closure $Where, Token $Prefix = Token::And): QuerySelect;

    public function WhereValue(Value $Where, Token $Prefix = Token::And): QuerySelect;

    public function SelectRaw(string ...$Fields): QuerySelect;

    public function SelectField(IField ...$Fields): QuerySelect;

    public function Select(Closure $Select): QuerySelect;

    public function Delete(): QueryDelete;

    public function Insert(object ...$Items): QueryInsert;

    public function Update(object $Value): QueryUpdate;
}
