<?php

namespace LiliDb\Query;

enum JoinType: string
{
    case Inner = 'INNER JOIN';
    case Left = 'LEFT JOIN';
    case Right = 'RIGHT JOIN';
    case Cross = 'CROSS JOIN';
}
