<?php

namespace LiliDb\Query;

use Closure;
use Exception;
use LiliDb\Exceptions\QueryException;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\ITable;
use LiliDb\Result;
use ReflectionFunction;

abstract class QueryInsert extends Query
{
    use QueryGenerator;

    protected array $Items = [];

    protected array $OnDuplicateKeyUpdate = [];

    public function __construct(
        ITable &$Table,
        object ...$Items,
    ) {
        parent::__construct(
            Database: $Table->Database,
            Table: $Table
        );

        foreach ($Items as $Item) {
            if ($Item::class != $this->Table->Model) {
                throw new QueryException($Item::class . " isn't class " . $this->Table->Model);
            }
        }

        $this->Items = $Items;
    }

    public function OnDuplicateKeyUpdateField(IField ...$OnDuplicateKeyUpdate): self
    {
        foreach ($OnDuplicateKeyUpdate as $Field) {
            if ($Field->Table->Model != $this->Table->Model) {
                throw new QueryException($Field->FieldReflection->name . " isn't from class " . $this->Table->Model);
            }
        }

        $this->OnDuplicateKeyUpdate = $OnDuplicateKeyUpdate;

        return $this;
    }

    public function OnDuplicateKeyUpdate(Closure $Fields): self
    {
        $this->OnDuplicateKeyUpdate = [];

        $Reflection = new ReflectionFunction($Fields);

        foreach ($Reflection->getParameters() as $Parameter) {
            $ParameterType = $Parameter->getType();

            if ($ParameterType !== null) {
                if ($ParameterType->getName() != $this->Table->Model) {
                    throw new QueryException($ParameterType->getName() . " isn't class " . $this->Table->Model);
                }
            }
        }

        foreach (self::SelectGenerator($Fields) as $Field) {
            $this->OnDuplicateKeyUpdate[] = $Field;
        }

        return $this;
    }

    public function Execute(): Result
    {
        try {
            $this->Database->Query = $this;

            $PrimaryKey = count($this->Table->PrimaryKeys) == 1 && !empty($this->Query->OnDuplicateKeyUpdate) ? current($this->Table->PrimaryKeys) : null;

            if ($Statement = $this->Table->Database->Connection->Prepare($this, $PrimaryKey)) {
                if ($Statement->Execute()) {
                    $InsertId = $Statement->InsertId();

                    if ($PrimaryKey !== null && is_int($InsertId)) {
                        foreach ($this->Items as $Index => $Item) {
                            $PrimaryKey->FieldReflection->setValue($Item, $InsertId + $Index);
                        }
                    }

                    $Statement->Close();

                    return new Result($this, true);
                }

                throw new QueryException($Statement->Error());
            }

            throw new QueryException($this->Table->Database->Connection->Error());
        } catch (Exception $ex) {
            return new Result($this, false, $ex);
        }
    }
}
