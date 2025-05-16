<?php

namespace LiliDb\Query\Traits;

use Closure;
use LiliDb\Interfaces\IField;
use LiliDb\Query\QueryGenerator;
use LiliDb\Token;

trait GroupBy
{
    use QueryGenerator;

    public array $GroupBy = [];

    public function GroupBy(Closure $GroupBy): self
    {
        foreach (self::QueryGenerator($GroupBy, false) as $Item) {
            if ($Item instanceof IField) {
                $this->GroupBy[] = $Item;
            }
        }

        return $this;
    }

    protected function GenerateGroupBySql(): void
    {
        if (!empty($this->GroupBy)) {
            $this->Query .= ' GROUP BY ';

            $GroupBy = [];

            foreach ($this->GroupBy as $Item) {
                if (!empty($GroupBy)) {
                    $GroupBy[] = Token::Comma->value;
                }

                $GroupBy[] = $Item->FieldReference(true);
            }

            $this->Query .= implode(' ', $GroupBy);
        }
    }
}
