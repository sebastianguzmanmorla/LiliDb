<?php

namespace LiliDb\PostgreSql\Types;

enum FieldTypeEnum: string
{
    //Numeric Data Types
    case BigInt = 'bigint';
    case BigSerial = 'bigserial';
    case Decimal = 'decimal';
    case DoublePrecision = 'double precision';
    case Integer = 'integer';
    case Numeric = 'numeric';
    case Real = 'real';
    case Serial = 'serial';
    case SmallInt = 'smallint';
    case SmallSerial = 'smallserial';

    //String Data Types
    case Varchar = 'varchar';
    case Text = 'text';

    //Date and Time Data Types
    case Date = 'date';
    case Time = 'time';
    case Timestamp = 'timestamp';
    case Interval = 'interval';

    //Boolean Type
    case Boolean = 'boolean';
}
