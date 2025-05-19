<?php

namespace LiliDb;

use PhpToken;

enum Token: string
{
    case Comma = ',';

    case DateTimeNow = 'NOW()';

    //Value
    case False = 'FALSE';
    case True = 'TRUE';
    case Null = 'NULL';

    //Operator
    case And = 'AND';
    case Or = 'OR';
    case Equal = '=';
    case NotEqual = '<>';
    case MoreThan = '>';
    case MoreEqualThan = '>=';
    case LessThan = '<';
    case LessEqualThan = '<=';
    case In = 'IN';
    case NotIn = 'NOT IN';
    case Like = 'LIKE';
    case NotLike = 'NOT LIKE';
    case IsNull = 'IS NULL';
    case IsNotNull = 'IS NOT NULL';
    case Between = 'BETWEEN';

    //OrderBy
    case Asc = 'ASC';
    case Desc = 'DESC';

    public static function FromToken(PhpToken $Token): ?static
    {
        if ($Token->text == 'false') {
            return self::False;
        }
        if ($Token->text == 'true') {
            return self::True;
        }

        if ($Token->text == '>') {
            return self::MoreThan;
        }
        if ($Token->text == '<') {
            return self::LessThan;
        }

        return match ($Token->id) {
            T_LOGICAL_AND => self::And,
            T_BOOLEAN_AND => self::And,
            T_LOGICAL_OR => self::Or,
            T_BOOLEAN_OR => self::Or,
            T_IS_EQUAL => self::Equal,
            T_IS_IDENTICAL => self::Equal,
            T_IS_NOT_EQUAL => self::NotEqual,
            T_IS_NOT_IDENTICAL => self::NotEqual,
            T_IS_GREATER_OR_EQUAL => self::MoreEqualThan,
            T_IS_SMALLER_OR_EQUAL => self::LessEqualThan,
            default => null,
        };
    }
}
