<?php

namespace LiliDb\MySql\Types;

enum FieldTypeEnum: string
{
    //String Data Types
    case Char = 'CHAR';
    case Varchar = 'VARCHAR';
    case Binary = 'BINARY';
    case Varbinary = 'VARBINARY';
    case TinyBlob = 'TINYBLOB';
    case TinyText = 'TINYTEXT';
    case Text = 'TEXT';
    case Blob = 'BLOB';
    case MediumText = 'MEDIUMTEXT';
    case MediumBlob = 'MEDIUMBLOB';
    case LongText = 'LONGTEXT';
    case LongBlob = 'LONGBLOB';
    //Numeric Data Types
    case Bit = 'BIT';
    case TinyInt = 'TINYINT';
    case Bool = 'BOOL';
    case Boolean = 'BOOLEAN';
    case SmallInt = 'SMALLINT';
    case MediumInt = 'MEDIUMINT';
    case Int = 'INT';
    case Integer = 'INTEGER';
    case BigInt = 'BIGINT';
    case UnsignedInt = 'INT UNSIGNED';
    case Float = 'FLOAT';
    case Double = 'DOUBLE';
    case DoublePrecision = 'DOUBLE PRECISION';
    case Decimal = 'DECIMAL';
    case Dec = 'DEC';
    //Date and Time Data Types
    case Date = 'DATE';
    case DateTime = 'DATETIME';
    case Timestamp = 'TIMESTAMP';
    case Time = 'TIME';
    case Year = 'YEAR';
}
