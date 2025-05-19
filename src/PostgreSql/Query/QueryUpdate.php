<?php

namespace LiliDb\PostgreSql\Query;

use LiliDb\Interfaces\IField;
use LiliDb\Query\QueryUpdate as AbstractQueryUpdate;
use LiliDb\SqlFormatter;
use LiliDb\Token;
use LiliDb\Value;

class QueryUpdate extends AbstractQueryUpdate
{
    public function GenerateSql(): string
    {
        $this->Query = "UPDATE {$this->Table->TableReference()} SET ";
        $this->Parameters = [];

        $SetFields = [];

        foreach ($this->Table->TableFields as $Field) {
            if ($Field->FieldReflection->isInitialized($this->Value)) {
                $FieldValue = new Value(
                    Field: $Field,
                    Where: Token::Equal,
                    Value: $Field->FieldReflection->getValue($this->Value),
                    ParameterMarker: $this->ParameterMarker,
                    FullReference: false
                );

                $SetFields[] = $FieldValue;

                $Value = $Field->Type->ToSql($FieldValue->Value) ?? $FieldValue->Value;

                if ($Value !== null && !($Value instanceof IField) && !($Value instanceof Token)) {
                    $this->Parameters[] = $Value;
                }
            }
        }

        $this->Query .= implode(', ', $SetFields);

        $this->GenerateWhereSql();

        return SqlFormatter::format($this->Query, false);
    }
}
