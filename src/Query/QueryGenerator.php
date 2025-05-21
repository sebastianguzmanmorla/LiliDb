<?php

namespace LiliDb\Query;

use ArrayObject;
use Closure;
use Exception;
use Generator;
use LiliDb\Interfaces\IField;
use LiliDb\Model;
use LiliDb\OrderBy;
use LiliDb\Token;
use LiliDb\Value;
use LiliDb\Where;
use PhpToken;
use ReflectionClass;
use ReflectionFunction;

trait QueryGenerator
{
    protected static function QueryGenerator(Closure $Function, bool $Logic = true): Generator
    {
        $Reflection = new ReflectionFunction($Function);

        $UsedVariables = [];
        $Parameters = [];

        foreach ($Reflection->getClosureUsedVariables() as $Variable => $Value) {
            $UsedVariables['$' . $Variable] = $Value;
        }

        foreach ($Reflection->getParameters() as $Parameter) {
            $ParameterType = $Parameter->getType();
            if ($ParameterType !== null) {
                $ParameterClass = new ReflectionClass($ParameterType->getName());

                $ParameterTraits = $ParameterClass->getTraits();

                if (!isset($ParameterTraits[Model::class])) {
                    throw new Exception("{$ParameterType->getName()} isn't use Model", false);
                }

                $Parameters['$' . $Parameter->getName()] = $ParameterClass->getStaticPropertyValue('Table', null);
            }
        }

        $OrderByReflection = new ReflectionClass(OrderBy::class);

        $WhereReflection = new ReflectionClass(Where::class);

        $Tokens = self::ReadClosure($Reflection);

        foreach ($Tokens as $Index => &$Token) {
            // Save Tokens
            // 33 = !
            // 40 = (
            // 41 = )
            if (in_array($Token->id, [33, 40, 41])) {
                continue;
            }

            $Value = null;

            if (in_array($Token->text, ['$_SESSION', '$_GET', '$_POST', '$_COOKIE', '$_SERVER', '$_ENV', '$this'])) {
                $Value = new Value(
                    Variable: $Token->text,
                    Expression: '',
                    Value: match ($Token->text) {
                        '$_SESSION' => $_SESSION,
                        '$_GET' => $_GET,
                        '$_POST' => $_POST,
                        '$_COOKIE' => $_COOKIE,
                        '$_SERVER' => $_SERVER,
                        '$_ENV' => $_ENV,
                        '$this' => $Reflection->getClosureThis(),
                        default => null
                    }
                );
            }

            if (isset($UsedVariables[$Token->text])) {
                $Value = new Value(
                    Variable: $Token->text,
                    Expression: '',
                    Value: $UsedVariables[$Token->text]
                );
            }

            $UnsetIndex = $Index;

            if ($Value !== null) {
                ++$UnsetIndex;

                self::ValueProcess($Value, $Tokens, $UnsetIndex);

                $Token = $Value;

                continue;
            }

            if (in_array($Token->text, ['OrderBy', 'Where'])) {
                $TraitReflection = match ($Token->text) {
                    'OrderBy' => $OrderByReflection,
                    'Where' => $WhereReflection,
                };

                ++$UnsetIndex;

                $Access = $Tokens[$UnsetIndex]->text;

                unset($Tokens[$UnsetIndex]);

                ++$UnsetIndex;

                $Name = $Tokens[$UnsetIndex];

                unset($Tokens[$UnsetIndex]);

                ++$UnsetIndex;

                if ($Access == '::') {
                    $Method = $TraitReflection->getMethod($Name);

                    $Args = [];

                    $pendingParenthesisCount = 0;

                    while (isset($Tokens[$UnsetIndex])) {
                        $TempToken = $Tokens[$UnsetIndex];

                        unset($Tokens[$UnsetIndex]);

                        ++$UnsetIndex;

                        if (self::isOpenParenthesis($TempToken->text)) {
                            ++$pendingParenthesisCount;

                            continue;
                        } elseif (self::isCloseParenthesis($TempToken->text)) {
                            --$pendingParenthesisCount;

                            if ($pendingParenthesisCount === 0) {
                                break;
                            }

                            continue;
                        } elseif ($TempToken->text === ',' || $TempToken->text === ';') {
                            if ($pendingParenthesisCount === 0) {
                                break;
                            }

                            continue;
                        }

                        if (isset($Parameters[$TempToken->text])) {
                            $Table = $Parameters[$TempToken->text];

                            $Access = $Tokens[$UnsetIndex]->text;

                            unset($Tokens[$UnsetIndex]);

                            ++$UnsetIndex;

                            $Name = $Tokens[$UnsetIndex];

                            unset($Tokens[$UnsetIndex]);

                            ++$UnsetIndex;

                            if ($Access == '->') {
                                $Args[] = $Table->Field($Name);

                                continue;
                            }

                            throw new Exception('Unexpected token ' . $Access);
                        }

                        $Value = null;

                        if (in_array($TempToken->text, ['$_SESSION', '$_GET', '$_POST', '$_COOKIE', '$_SERVER', '$_ENV', '$this'])) {
                            $Value = new Value(
                                Variable: $TempToken->text,
                                Expression: '',
                                Value: match ($TempToken->text) {
                                    '$_SESSION' => $_SESSION,
                                    '$_GET' => $_GET,
                                    '$_POST' => $_POST,
                                    '$_COOKIE' => $_COOKIE,
                                    '$_SERVER' => $_SERVER,
                                    '$_ENV' => $_ENV,
                                    '$this' => $Reflection->getClosureThis(),
                                    default => null
                                }
                            );
                        }

                        if (isset($UsedVariables[$TempToken->text])) {
                            $Value = new Value(
                                Variable: $TempToken->text,
                                Expression: '',
                                Value: $UsedVariables[$TempToken->text]
                            );
                        }

                        if ($Value !== null) {
                            self::ValueProcess($Value, $Tokens, $UnsetIndex);

                            $Args[] = $Value->Value;

                            $Value = null;

                            continue;
                        }

                        $DbToken = Token::FromToken($TempToken);

                        $Args[] = $DbToken !== null ? $DbToken : (is_numeric($TempToken->text) ? $TempToken->text + 0 : str_replace(["'", '"'], '', $TempToken->text));
                    }

                    $Token = $Method->invoke(null, ...$Args);
                } else {
                    throw new Exception('Unexpected access token ' . $Access);
                }

                continue;
            }

            if (isset($Parameters[$Token->text])) {
                $Table = $Parameters[$Token->text];

                ++$UnsetIndex;

                $Access = $Tokens[$UnsetIndex]->text;

                unset($Tokens[$UnsetIndex]);

                ++$UnsetIndex;

                $Name = $Tokens[$UnsetIndex];

                unset($Tokens[$UnsetIndex]);

                ++$UnsetIndex;

                if ($Access == '->') {
                    $Token = $Table->Field($Name);
                } else {
                    throw new Exception('Unexpected access token ' . $Access);
                }

                continue;
            }

            $DbToken = Token::FromToken($Token);

            if ($DbToken !== null) {
                $Token = $DbToken;

                continue;
            }

            $Token = new Value(
                Value: is_numeric($Token->text) ? $Token->text + 0 : str_replace(["'", '"'], '', $Token->text)
            );
        }

        $Group = [];

        foreach ($Tokens as $Index => &$Token) {
            if (($Token instanceof PhpToken && in_array($Token->id, [40, 41], true)) || ($Token instanceof Token && in_array($Token, [Token::And, Token::Or], true))) {
                if (!empty($Group)) {
                    yield self::GroupProcess($Group, $Logic);

                    $Group = [];
                }

                yield $Token;
            } else {
                $Group[] = $Token;
            }
        }

        if (!empty($Group)) {
            yield self::GroupProcess($Group, $Logic);
        }
    }

    protected static function SelectGenerator(Closure $Select): Generator
    {
        $Reflection = new ReflectionFunction($Select);

        $Parameters = [];

        foreach ($Reflection->getParameters() as $Parameter) {
            $ParameterType = $Parameter->getType();
            if ($ParameterType !== null) {
                $ParameterClass = new ReflectionClass($Parameter->getType()->getName());

                $Table = $ParameterClass->getStaticPropertyValue('Table', null);

                if ($Table === null) {
                    throw new Exception('El parametro no es de la clase requerida.', false);
                }

                $Parameters['$' . $Parameter->getName()] = $Table;
            }
        }

        $Tokens = self::ReadClosure($Reflection);
        $Tokens = new ArrayObject($Tokens);
        $Tokens = $Tokens->getIterator();

        $Token = fn () => $Tokens->current();

        while ($Tokens->valid()) {
            if (in_array($Token()->id, [T_VARIABLE, T_STRING]) && isset($Parameters[$Token()->text])) {
                $Instance = $Parameters[$Token()->text];

                $Tokens->next();

                if ($Tokens->valid() && in_array($Token()->id, [T_OBJECT_OPERATOR])) {
                    $Tokens->next();
                    yield $Instance->Field($Token()->text);
                } else {
                    foreach ($Instance->Fields as $Field) {
                        yield $Field;
                    }
                }
            }
            $Tokens->next();
        }
    }

    private static function ReadClosure(ReflectionFunction $reflection): array
    {
        $fileName = $reflection->getFileName();
        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();

        if ($fileName === false || $start === false || $end === false || ($fileContent = file($fileName)) === false) {
            return [];
        }

        --$start;

        $tokens = PhpToken::tokenize('<?php ' . implode('', array_slice($fileContent, $start, $end - $start)));

        array_shift($tokens);

        $closureTokens = [];
        $pendingParenthesisCount = 0;

        $ClosureStart = false;

        foreach ($tokens as $token) {
            if (in_array($token->id, [390], true)) {
                $ClosureStart = true;

                continue;
            }

            if (!$ClosureStart) {
                continue;
            }

            if (in_array($token->id, [T_WHITESPACE], true)) {
                continue;
            }

            if (self::isOpenParenthesis($token->text)) {
                ++$pendingParenthesisCount;
            } elseif (self::isCloseParenthesis($token->text)) {
                if ($pendingParenthesisCount === 0) {
                    break;
                }
                --$pendingParenthesisCount;
            } elseif ($token->text === ',' || $token->text === ';') {
                if ($pendingParenthesisCount === 0) {
                    break;
                }
            }

            $closureTokens[] = $token;
        }

        return $closureTokens;
    }

    private static function isOpenParenthesis(string $value): bool
    {
        return in_array($value, ['{', '[', '(']);
    }

    private static function isCloseParenthesis(string $value): bool
    {
        return in_array($value, ['}', ']', ')']);
    }

    private static function ValueProcess(Value &$Value, array &$Tokens, int &$UnsetIndex): void
    {
        while (isset($Tokens[$UnsetIndex]) && in_array($Tokens[$UnsetIndex]->id, [T_OBJECT_OPERATOR, 91])) {
            $Value->Expression .= $Tokens[$UnsetIndex]->text;

            unset($Tokens[$UnsetIndex]);

            ++$UnsetIndex;

            $Key = str_replace(["'", '"'], '', $Tokens[$UnsetIndex]->text);

            if (is_array($Value->Value)) {
                $Value->Value = $Value->Value[$Key] ?? null;
            } else {
                $ValueReflection = new ReflectionClass($Value->Value);
                $ValueProperty = $ValueReflection->getProperty($Key);
                $Value->Value = $ValueProperty->getValue($Value->Value) ?? null;
            }

            $Value->Expression .= $Tokens[$UnsetIndex]->text;

            unset($Tokens[$UnsetIndex]);

            ++$UnsetIndex;

            if (isset($Tokens[$UnsetIndex]) && in_array($Tokens[$UnsetIndex]->id, [93])) {
                $Value->Expression .= $Tokens[$UnsetIndex]->text;

                unset($Tokens[$UnsetIndex]);

                ++$UnsetIndex;
            }
        }
    }

    private static function GroupProcess(array $Group, bool $Logic): Value|IField
    {
        if ($Logic && count($Group) == 3 && $Group[0] instanceof IField && $Group[1] instanceof Token && $Group[2] instanceof Value) {
            $Group[2]->Field = $Group[0];
            $Group[2]->Where = $Group[1];

            $Group = $Group[2];
        } elseif ($Logic && count($Group) == 3 && $Group[0] instanceof IField && $Group[1] instanceof Token) {
            $Group = new Value(
                Field: $Group[0],
                Where: $Group[1],
                Value: $Group[2]
            );
        } elseif ($Logic && count($Group) == 2 && $Group[0] instanceof PhpToken && $Group[1] instanceof IField) {
            $Group = new Value(
                Field: $Group[1],
                Where: Token::Equal,
                Value: Token::False
            );
        } elseif ($Logic && count($Group) == 1 && $Group[0] instanceof IField) {
            $Group = new Value(
                Field: $Group[0],
                Where: Token::Equal,
                Value: Token::True
            );
        } elseif (isset($Group[0]) && count($Group) == 1 && $Group[0] instanceof IField) {
            $Group = $Group[0];
        } elseif (isset($Group[0]) && count($Group) == 1 && $Group[0] instanceof Value) {
            $Group = $Group[0];
        }

        if (is_array($Group)) {
            throw new Exception('Unexpected Group');
        }

        return $Group;
    }
}
