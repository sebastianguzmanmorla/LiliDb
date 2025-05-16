<?php

namespace LiliDb\Query\Traits;

use Closure;
use LiliDb\Interfaces\IField;
use LiliDb\Query\QueryGenerator;
use LiliDb\Query\WhereItem;
use LiliDb\Token;
use LiliDb\Value;

trait Where
{
    use QueryGenerator;

    public array $Where = [];

    public function WhereRaw(string $Where, array $Parameters = [], Token $Prefix = Token::And): self
    {
        $WhereItem = new WhereItem(
            Prefix: $Prefix
        );

        $WhereItem->Query = $Where;
        $WhereItem->Parameters = $Parameters;

        $this->Where[] = $WhereItem;

        return $this;
    }

    public function Where(Closure $Where, Token $Prefix = Token::And): self
    {
        $WhereItem = new WhereItem(
            Prefix: $Prefix
        );

        foreach (self::QueryGenerator($Where) as $Item) {
            $WhereItem->Items[] = $Item;
        }

        $this->Where[] = $WhereItem;

        return $this;
    }

    public function WhereValue(Value $Where, Token $Prefix = Token::And): self
    {
        $WhereItem = new WhereItem(
            Prefix: $Prefix
        );

        $WhereItem->Items = [$Where];

        $this->Where[] = $WhereItem;

        return $this;
    }

    protected function GenerateWhereSql(): void
    {
        if (!empty($this->Where)) {
            $this->Query .= ' WHERE ';

            $Where = [];

            foreach ($this->Where as $WhereItem) {
                if (!empty($Where)) {
                    $Where[] = $WhereItem->Prefix->value;
                }

                if ($WhereItem->Query != '') {
                    $Query = $WhereItem->Query;

                    if ($this->ParameterMarker instanceof Closure) {
                        while (str_contains($Query, '?')) {
                            $Query = substr_replace($Query, $this->ParameterMarker->__invoke(), strpos($Query, '?'), 1);
                        }
                    }

                    $Where[] = "({$Query})";

                    array_push($this->Parameters, ...$WhereItem->Parameters);
                } else {
                    $Query = [];
                    $Parameters = [];

                    foreach ($WhereItem->Items as $Item) {
                        if ($Item instanceof Value) {
                            $Item->ParameterMarker = $this->ParameterMarker;

                            $Query[] = $Item;

                            if (is_array($Item->Value)) {
                                foreach ($Item->Value as $Value) {
                                    $Value = $Item->Field?->FieldType->ToSql($Value) ?? $Value;

                                    if ($Value !== null && !($Value instanceof IField) && !($Value instanceof Token)) {
                                        $Parameters[] = $Value;
                                    }
                                }
                            } else {
                                $Value = $Item->Field?->FieldType->ToSql($Item->Value) ?? $Item->Value;

                                if ($Value !== null && !($Value instanceof IField) && !($Value instanceof Token)) {
                                    $Parameters[] = $Value;
                                }
                            }
                        } elseif ($Item instanceof IField) {
                            $Item = new Value(
                                Field: $Item,
                                Where: Token::Equal,
                                Value: Token::True
                            );

                            $Query[] = $Item;
                        } elseif ($Item instanceof Token) {
                            $Query[] = $Item->value;
                        } else {
                            $Query[] = $Item;
                        }
                    }

                    $Where[] = '(' . implode(' ', $Query) . ')';

                    array_push($this->Parameters, ...$Parameters);
                }
            }

            $this->Query .= implode(' ', $Where);
        }
    }
}
