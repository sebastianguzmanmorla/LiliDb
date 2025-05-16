<?php

namespace LiliDb\PostgreSql\Query;

use Exception;
use LiliDb\Interfaces\IField;
use LiliDb\Query\QuerySelect as AbstractQuerySelect;
use LiliDb\SqlFormatter;

class QuerySelect extends AbstractQuerySelect
{
    private int $ParameterMarkerCount = 1;

    public function Count(): int
    {
        $Query = new static(Table: $this->Table);

        $Query->GroupBy = $this->GroupBy;
        $Query->Join = $this->Join;
        $Query->Where = $this->Where;

        $this->ParameterMarkerCount = 1;

        $Query->ParameterMarker = fn () => '$' . $this->ParameterMarkerCount++;

        $Sql = $Query
            ->SelectRaw('1 as C')
            ->GenerateSql();

        $Result = $this->Database
            ->RawQuery("SELECT SUM(C) AS \"Count\" FROM ({$Sql}) as \"CountTable\"", $Query->Parameters)
            ->ExecuteResultSet();

        return $Result->Count ?? throw new Exception('Failed to Count... ' . $Result->Error?->getMessage(), previous: $Result->Error);
    }

    public function GenerateSql(): string
    {
        $this->Query = 'SELECT ';
        $this->Parameters = [];

        if (!empty($this->Select)) {
            $Select = [];

            foreach ($this->Select as $Field) {
                if ($Field instanceof IField) {
                    $Select[] = $Field->FieldReference(true);
                } else {
                    $Select[] = $Field;
                }
            }

            $this->Query .= implode(', ', $Select);
        } else {
            $this->Query .= '*';
        }

        $this->Query .= " FROM {$this->Table->TableReference()}";

        $this->GenerateJoinSql();

        $this->GenerateWhereSql();

        $this->GenerateGroupBySql();

        $this->GenerateOrderBySql();

        return SqlFormatter::format($this->Query, false);
    }
}
