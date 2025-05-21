<?php

namespace LiliDb\Query;

use Exception;
use LiliDb\Interfaces\IField;
use LiliDb\Interfaces\ITable;
use LiliDb\Result;

abstract class QueryInsert extends Query
{
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
                throw new Exception($Item::class . " isn't class " . $this->Table->Model);
            }
        }

        $this->Items = $Items;
    }

    public function OnDuplicateKeyUpdate(IField ...$OnDuplicateKeyUpdate): self
    {
        foreach ($OnDuplicateKeyUpdate as $Field) {
            if ($Field->Table->Model != $this->Table->Model) {
                throw new Exception($Field->FieldReflection->name . " isn't from class " . $this->Table->Model);
            }
        }

        $this->OnDuplicateKeyUpdate = $OnDuplicateKeyUpdate;

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

                throw new Exception($Statement->Error());
            }

            throw new Exception($this->Table->Database->Connection->Error());
        } catch (Exception $ex) {
            return new Result($this, false, $ex);
        }
    }
}
