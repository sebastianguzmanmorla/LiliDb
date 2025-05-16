<?php

namespace LiliDb;

use Closure;
use LiliDb\Interfaces\IField;

class Value
{
    public function __construct(
        public ?IField $Field = null,
        public ?Token $Where = null,
        public ?Token $Order = null,
        public mixed $Value = null,
        public Closure|string $ParameterMarker = '?',
        public mixed $Variable = null,
        public ?string $Expression = null,
        public bool $FullReference = true,
    ) {
    }

    public function __toString()
    {
        $Return = [];

        if (is_array($this->Value)) {
            $Value = array_map(fn ($item) => $this->GetSqlValue($item), $this->Value);
        } else {
            $Value = $this->GetSqlValue($this->Value);
        }

        if ($this->Where !== null) {
            switch ($this->Where) {
                case Token::Between:
                    $Return = [
                        $this->Field?->FieldReference($this->FullReference),
                        $this->Where->value,
                        $Value[0],
                        Token::And->value,
                        $Value[1],
                    ];

                    break;
                case Token::In:
                    $Return = [
                        $this->Field?->FieldReference($this->FullReference),
                        $this->Where->value,
                        '(' . implode(', ', $Value) . ')',
                    ];

                    break;
                case Token::NotIn:
                    $Return = [
                        $this->Field?->FieldReference($this->FullReference),
                        $this->Where->value,
                        '(' . implode(', ', $Value) . ')',
                    ];

                    break;
                case Token::IsNull:
                    $Return = [
                        $this->Field?->FieldReference($this->FullReference),
                        $this->Where->value,
                    ];

                    break;
                case Token::IsNotNull:
                    $Return = [
                        $this->Field?->FieldReference($this->FullReference),
                        $this->Where->value,
                    ];

                    break;
                default:
                    $Return = [
                        $this->Field?->FieldReference($this->FullReference),
                        $this->Where->value,
                        $Value,
                    ];

                    break;
            }
        } elseif ($this->Order !== null) {
            $Return = [
                $this->Field?->FieldReference($this->FullReference),
                $this->Order->value,
            ];
        } else {
            $Return = [
                $Value,
            ];
        }

        return implode(' ', $Return);
    }

    public function GetParameterMarker(): string
    {
        if ($this->ParameterMarker instanceof Closure) {
            return $this->ParameterMarker->__invoke();
        }

        return $this->ParameterMarker;
    }

    private function GetSqlValue(mixed $Value)
    {
        $Value = $this->Field?->FieldType->ToSql($Value) ?? $Value;

        if ($Value instanceof IField) {
            return $Value->FieldReference($this->FullReference);
        } elseif ($Value instanceof Token) {
            return $Value->value;
        } elseif ($Value !== null) {
            return $this->GetParameterMarker();
        }

        return Token::Null->value;
    }
}
