<?php
namespace App\Helpers;

class TransactionHelperContract
{
    protected $gateway;

    public $purposable_type, $purposable_id = null;
    public function __construct($trans_id, $gateway, $trans_type, $amount, public $purpose)
    {
        $this->setPurposeable();
    }

    private function setTransaction()
    {
    }
    private function setMessage()
    {
    }

    private function setTransactionType()
    {
    }
    private function setPurposeableId()
    {
    }

    private function setPurpose()
    {
    }
    private function setPurposeableType()
    {
    }

    public function setPurposeable()
    {
        # code...
        if ($this->purpose = "SALES") {
            $purposable_type = "App\Model\Sale";
        } else if ($this->purpose = "LOANS") {
            $purposable_type = "App\Loans\Loans";
        } else if ($this->purpose = "SUPPLIES") {
            $purposable_type = "App\Supples\Supplies";
        } else {
            $purposable_type = null;
        }

        return $purposable_type;
    }
}
