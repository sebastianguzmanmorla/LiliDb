<?php

namespace LiliDb\Query;

use Closure;
use Generator;
use LiliDb\Exceptions\QueryException;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\ITable;
use LiliDb\Query\Traits\GroupBy;
use LiliDb\Query\Traits\Join;
use LiliDb\Query\Traits\OrderBy;
use LiliDb\Query\Traits\Where;
use LiliDb\ResultRow;
use LiliDb\ResultSet;
use ReflectionClass;
use ReflectionFunction;

abstract class QuerySelect extends Query
{
    use GroupBy;
    use Join;
    use OrderBy;
    use Where;

    public ?Closure $SelectClosure = null;

    public array $Select = [];

    public function __construct(
        ITable &$Table,
        public ?int $Limit = null,
        public ?int $Offset = null
    ) {
        parent::__construct(
            Database: $Table->Database,
            Table: $Table
        );
    }

    public function SelectRaw(string ...$Fields): self
    {
        foreach ($Fields as $Field) {
            $this->Select[] = $Field;
        }

        return $this;
    }

    public function SelectField(IField ...$Fields): self
    {
        foreach ($Fields as $Field) {
            $this->Select[] = $Field;
        }

        return $this;
    }

    public function Select(Closure $Select): self
    {
        $this->SelectClosure = $Select;

        $Reflection = new ReflectionFunction($Select);

        if ($Reflection->getReturnType() === null) {
            throw new QueryException('Select closure must define a return class', false);
        }

        foreach ($Reflection->getParameters() as $Parameter) {
            $ParameterType = $Parameter->getType();

            if ($ParameterType !== null) {
                $ParameterClass = new ReflectionClass($ParameterType->getName());

                $Table = $ParameterClass->getStaticPropertyValue('Table', null);

                if ($Table === null) {
                    throw new QueryException($ParameterClass->getName() . " doesn't use Model", false);
                }
            }
        }

        foreach (self::SelectGenerator($Select) as $Field) {
            $this->Select[] = $Field;
        }

        return $this;
    }

    public function Delete(): QueryDelete
    {
        $Query = $this->Table->Delete();

        $Query->Where = $this->Where;

        return $Query;
    }

    public function Update(object $Value): QueryUpdate
    {
        $Query = $this->Table->Update($Value);

        $Query->Where = $this->Where;

        return $Query;
    }

    public function Any(): bool
    {
        $Query = new static(Table: $this->Table);

        $Query->Join = $this->Join;
        $Query->Where = $this->Where;

        $Result = $Query
            ->SelectRaw('true')
            ->Execute(null, 1);

        return !$Result->EOF;
    }

    abstract public function Count(): int;

    public function ExecutePage(int $Offset, int $Limit): ResultSet
    {
        return $this->Execute($Offset, $Limit, $this->Count());
    }

    public function ExecuteFetch(?int $Offset = null, ?int $Limit = null): Generator
    {
        $this->Offset = $Offset;
        $this->Limit = $Limit;

        $Associative = empty($this->Select);

        if ($Associative && empty($this->Join)) {
            $this->SelectClosure = fn ($x) => $x;
        }

        $SelectGroup = $this->BuildSelectGroup($Associative);

        $ResultRow = $this->SelectClosure !== null
            ? fn (array &$SelectGroup, array $Row) => $this->ResultRowClosure($SelectGroup, $Row)
            : fn (array &$SelectGroup, array $Row) => $this->ResultRow($SelectGroup, $Row);

        foreach ($this->Fetch($Associative) as $Row) {
            yield $ResultRow->__invoke($SelectGroup, $Row);
        }
    }

    public function Execute(?int $Offset = null, ?int $Limit = null, ?int $Total = null): ResultSet
    {
        $this->Offset = $Offset;
        $this->Limit = $Limit;

        $Associative = empty($this->Select);

        if ($Associative && empty($this->Join)) {
            $this->SelectClosure = fn ($x) => $x;
        }

        $SelectGroup = $this->BuildSelectGroup($Associative);

        $Result = $this->FetchAll($Associative);

        $ResultRow = $this->SelectClosure !== null
            ? fn (array &$SelectGroup, array $Row) => $this->ResultRowClosure($SelectGroup, $Row)
            : fn (array &$SelectGroup, array $Row) => $this->ResultRow($SelectGroup, $Row);

        if ($Result->Result !== false) {
            foreach ($Result->Result as &$Row) {
                $Row = $ResultRow->__invoke($SelectGroup, $Row);
            }

            return new ResultSet(
                Query: $this,
                Result: $Result->Result,
                Offset: $this->Offset,
                Limit: $this->Limit,
                Total: $Total
            );
        }

        return new ResultSet(
            Query: $this,
            Result: [],
            Error: $Result->Error
        );
    }

    private function ResultRowClosure(array &$SelectGroup, array &$Row): object
    {
        $Args = [];

        foreach ($SelectGroup as $TableName => $TableArray) {
            if ($TableName != '') {
                $Class = $TableArray['Table']->Reflection->newInstance();

                foreach ($TableArray['Fields'] as $Index => $Field) {
                    $Field->FieldSetValue($Class, $Row[$Index]);
                }

                $Args[] = $Class;
            }
        }

        return $this->SelectClosure->__invoke(...$Args);
    }

    private function ResultRow(array &$SelectGroup, array &$Row): ResultRow
    {
        $ResultRow = new ResultRow();

        foreach ($SelectGroup as $TableName => $TableArray) {
            if ($TableName == '') {
                foreach ($TableArray as $Index => $Field) {
                    $Value = $Row[$Index] ?? null;

                    $ResultRow->__set($Field, $Value);
                }
            } else {
                $Class = $TableArray['Table']->Reflection->newInstance();

                foreach ($TableArray['Fields'] as $Index => $Field) {
                    $Value = $Row[$Index] ?? null;

                    $Field->FieldSetValue($Class, $Value);
                }

                $ResultRow->__set($TableName, $Class);
            }
        }

        return $ResultRow;
    }

    private function BuildSelectGroup(bool $Associative): array
    {
        $SelectGroup = [];

        if ($Associative) {
            $SelectGroup[$this->Table->ModelName]['Table'] = $this->Table;

            foreach ($this->Table->Fields as $Field) {
                $SelectGroup[$this->Table->ModelName]['Fields'][$Field->Name] = $Field;
            }

            foreach ($this->Join as $Join) {
                $SelectGroup[$Join->Table->ModelName]['Table'] = $Join->Table;

                foreach ($Join->Table->Fields as $Field) {
                    $SelectGroup[$Join->Table->ModelName]['Fields'][$Field->Name] = $Field;
                }
            }
        } else {
            foreach ($this->Select as $Index => $Field) {
                if ($Field instanceof IField) {
                    if (!isset($SelectGroup[$Field->Table->ModelName])) {
                        $SelectGroup[$Field->Table->ModelName]['Table'] = $Field->Table;
                    }

                    $SelectGroup[$Field->Table->ModelName]['Fields'][$Index] = $Field;
                } else {
                    $As = strpos(strtolower($Field), 'as');

                    if ($As) {
                        $SelectGroup[''][$Index] = trim(substr($Field, $As + 2));
                    } else {
                        $SelectGroup[''][$Index] = trim($Field);
                    }
                }
            }
        }

        return $SelectGroup;
    }
}
