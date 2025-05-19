<?php

namespace LiliDb\Query\Traits;

use Closure;
use Exception;
use LiliDb\Interfaces\IField;
use LiliDb\Query\JoinItem;
use LiliDb\Query\JoinType;
use LiliDb\Query\QueryGenerator;
use LiliDb\Token;
use LiliDb\Value;
use ReflectionClass;
use ReflectionFunction;

trait Join
{
    use QueryGenerator;

    public array $Join = [];

    public function InnerJoin(Closure $Join): self
    {
        return $this->Join($Join, JoinType::Inner);
    }

    public function LeftJoin(Closure $Join): self
    {
        return $this->Join($Join, JoinType::Left);
    }

    public function RightJoin(Closure $Join): self
    {
        return $this->Join($Join, JoinType::Right);
    }

    public function CrossJoin(Closure $Join): self
    {
        return $this->Join($Join, JoinType::Cross);
    }

    protected function Join(Closure $Join, JoinType $JoinType): self
    {
        $Reflection = new ReflectionFunction($Join);

        $Parameter = $Reflection->getParameters()[0] ?? null;

        if ($Parameter === null) {
            throw new Exception('El parametro no es de la clase requerida.', false);
        }

        $Table = new ReflectionClass($Parameter->getType()->getName());

        $Table = $Table->getStaticPropertyValue('Table', null);

        if ($Table === null) {
            throw new Exception('El parametro no es de la clase requerida.', false);
        }

        $JoinItem = new JoinItem(
            Table: $Table,
            JoinType: $JoinType
        );

        foreach (self::QueryGenerator($Join) as $Item) {
            $JoinItem->On[] = $Item;
        }

        $this->Join[] = $JoinItem;

        return $this;
    }

    protected function GenerateJoinSql(): void
    {
        if (!empty($this->Join)) {
            foreach ($this->Join as $JoinItem) {
                $Query = [];
                $Parameters = [];

                foreach ($JoinItem->On as $Item) {
                    if ($Item instanceof Value) {
                        $Item->ParameterMarker = $this->ParameterMarker;

                        $Query[] = $Item;

                        if (is_array($Item->Value)) {
                            foreach ($Item->Value as $Value) {
                                $Value = $Item->Field?->Type->ToSql($Value) ?? $Value;

                                if ($Value !== null && !($Value instanceof IField) && !($Value instanceof Token)) {
                                    $Parameters[] = $Value;
                                }
                            }
                        } else {
                            $Value = $Item->Field?->Type->ToSql($Item->Value) ?? $Item->Value;

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

                $this->Query .= " {$JoinItem->JoinType->value} {$JoinItem->Table->TableReference()} ON " . implode(' ', $Query);

                array_push($this->Parameters, ...$Parameters);
            }
        }
    }
}
