<?php

namespace LiliDb\PostgreSql\Query;

use LiliDb\Interfaces\IField;
use LiliDb\Query\QueryInsert as AbstractQueryInsert;
use LiliDb\Result;
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

        $this->Query .= implode(', ', array_map(fn (IField $Field) => '"' . $Field->Name . '"', $QueryFields));

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

                    $Value = $Field->Type->ToSql($FieldValue->Value) ?? $FieldValue->Value;

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
            $PrimaryKeys = array_map(fn (IField $Field) => '"' . $Field->Name . '"', $this->Table->TablePrimaryKeys);

            $this->Query .= ' ON CONFLICT (' . implode(', ', $PrimaryKeys) . ') DO UPDATE SET ';

            $Update = [];

            foreach ($this->OnDuplicateKeyUpdate as $Field) {
                $Update[] = '"' . $Field->Name . '" = EXCLUDED."' . $Field->Name . '"';
            }

            $this->Query .= implode(', ', $Update);
        }

        return SqlFormatter::format($this->Query, false);
    }

    public function Execute(): Result
    {
        $Result = parent::Execute();

        $PrimaryKeys = array_map(fn (IField $Field) => $Field->Name, $this->Table->TablePrimaryKeys);

        if (!empty($PrimaryKeys)) {
            $PrimaryKey = current($PrimaryKeys);

            $UpdateSequence = $this->Database
                ->RawQuery("SELECT pg_catalog.setval(pg_get_serial_sequence('{$this->Table->TableReference()}', '{$PrimaryKey}'), (SELECT MAX(\"{$PrimaryKey}\") FROM {$this->Table->TableReference()})+1)")
                ->ExecuteQuery();

            if ($UpdateSequence->Error !== null) {
                $Result->Result = false;
                $Result->Error = $UpdateSequence->Error;
            }
        }

        return $Result;
    }
}
