<?php

namespace LiliDb\Query\Traits;

use Closure;
use LiliDb\Interfaces\IField;
use LiliDb\Query\QueryGenerator;
use LiliDb\Token;
use LiliDb\Value;

trait OrderBy
{
    use QueryGenerator;

    public array $OrderBy = [];

    public function OrderBy(Closure $OrderBy): self
    {
        foreach (self::QueryGenerator($OrderBy, false) as $Item) {
            if ($Item instanceof IField || $Item instanceof Value) {
                $this->OrderBy[] = $Item;
            }
        }

        return $this;
    }

    public function OrderByValue(Value|IField $OrderBy): self
    {
        $this->OrderBy[] = $OrderBy;

        return $this;
    }

    protected function GenerateOrderBySql(): void
    {
        if (!empty($this->OrderBy)) {
            $this->Query .= ' ORDER BY ';

            $OrderBy = [];

            foreach ($this->OrderBy as $Item) {
                if (!empty($OrderBy)) {
                    $OrderBy[] = Token::Comma->value;
                }

                if ($Item instanceof IField) {
                    $OrderBy[] = $Item->FieldReference(true);
                } else {
                    $OrderBy[] = $Item->__toString();
                }
            }

            $this->Query .= implode(' ', $OrderBy);
        }
    }
}
