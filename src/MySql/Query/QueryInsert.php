<?php

namespace LiliDb\MySql\Query;

use LiliDb\Interfaces\IField;
use LiliDb\Query\QueryInsert as AbstractQueryInsert;
use LiliDb\SqlFormatter;
use LiliDb\Token;
use LiliDb\Value;

class QueryInsert extends AbstractQueryInsert
{
    public function GenerateSql(): string
    {
        $this->Query = "INSERT INTO {$this->Table->TableReference()} (";
        $this->Parameters = [];

        $QueryFields = [];

        foreach ($this->Items as $Item) {
            foreach ($this->Table->TableFields as $FieldIndex => $Field) {
                if ($Field->FieldReflection->isInitialized($Item)) {
                    $QueryFields[$FieldIndex] = $Field;
                }
            }
        }

        $this->Query .= implode(', ', array_map(fn (IField $Field) => $Field->FieldReference(false), $QueryFields));

        $Values = [];

        foreach ($this->Items as $ItemIndex => $Item) {
            if (!isset($Values[$ItemIndex])) {
                $Values[$ItemIndex] = [];
            }

            foreach ($QueryFields as $FieldIndex => $Field) {
                if ($Field->FieldReflection->isInitialized($Item)) {
                    $FieldValue = new Value(
                        Field: $Field,
                        Value: $Field->FieldReflection->getValue($Item),
                        ParameterMarker: $this->ParameterMarker
                    );

                    $Values[$ItemIndex][$FieldIndex] = $FieldValue;

                    $Value = $Field->FieldType->ToSql($FieldValue->Value) ?? $FieldValue->Value;

                    if ($Value !== null && !($Value instanceof IField) && !($Value instanceof Token)) {
                        $this->Parameters[] = $Value;
                    }
                } else {
                    $Values[$ItemIndex][$FieldIndex] = Token::Null->value;
                }
            }
        }

        $Values = implode('),(', array_map(fn ($item) => implode(', ', $item), $Values));

        $this->Query .= ') VALUES (' . $Values . ')';

        if (!empty($this->OnDuplicateKeyUpdate)) {
            $this->Query .= ' ON DUPLICATE KEY UPDATE ';

            $Update = [];

            foreach ($this->OnDuplicateKeyUpdate as $Field) {
                $Update[] = $Field->FieldReference(false) . ' = VALUES(' . $Field->FieldReference(false) . ')';
            }

            $this->Query .= implode(', ', $Update);
        }

        return SqlFormatter::format($this->Query, false);
    }
}
