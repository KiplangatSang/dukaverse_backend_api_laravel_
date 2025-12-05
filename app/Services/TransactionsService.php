<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Supply;
use Illuminate\Support\Str;

class TransactionsService  extends BaseService
{


    protected $STRLENGTH = 8;
   
    public function __construct(
         StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function saveTransaction(
        $gateway,
        $sender_account_id,
        $receiver_account_id,
        $amount,
        $message,
        $transaction_type,
        $cost,
        $currency,
        $purpose,
        $sender_phone_number = null,
        $receiver_phone_number = null,
        $purposeable_id = null

    ) {
        # code...
        if ($purposeable_id) {
            $purposeable_type = $this->setPurposeable($purpose);
        }

        $code   = "MNA" . Str::random($this->STRLENGTH) . "OP" . rand(10, 99);
        $result = $this->account->accountTransactions()->create([
            "transaction_id"        => $code,
            "sender_accounts_id"    => $sender_account_id,
            "receiver_accounts_id"  => $receiver_account_id,
            "amount"                => $amount,
            "gateway"               => $gateway,
            "status"                => false,
            "transaction_type"      => $transaction_type,
            "message"               => $message,
            "cost"                  => $cost,
            "currency"              => $currency,
            "purpose"               => $purpose,
            "total_amount"          => $cost + $amount,
            "purposeable_id"        => $purposeable_id,
            "purposeable_type"      => $purposeable_type,
            "sender_phone_number"   => $sender_phone_number,
            "receiver_phone_number" => $receiver_phone_number,

        ]);

        return $result;
    }

     public function saveSaleTransaction(
        string $gateway,
        ?int $sender_account_id,
        int $receiver_account_id,
        string $sender_phone_number,
        string $receiver_phone_number,
        string $amount,
        string $transaction_type,
        string $cost,
        string $purpose,
        string $message,
        ?int $purpose_id = null,
        $currency = "ksh",
    ) {
        # code...

        $requestdata = [
            "gateway"               => $gateway,
            'sender_account_id'     => $sender_account_id,
            'receiver_account_id'   => $receiver_account_id,
            'sender_phone_number'   => $sender_phone_number,
            'receiver_phone_number' => $receiver_phone_number,
            "amount"                => $amount,
            "transaction_type"      => $transaction_type,
            "cost"                  => $cost,
            "currency"              => $currency,
            "purpose"               => $purpose,
            "message"               => $message,
            "purpose_id"            => $purpose_id,
        ];

 
        $result                = $this->saveTransaction(
            gateway          :$gateway,
            sender_account_id   :$sender_account_id,
            receiver_account_id :$receiver_account_id,
            sender_phone_number :$sender_phone_number,
            receiver_phone_number:$receiver_phone_number,
            amount              :$amount,
            transaction_type    :$transaction_type,
            cost                :$cost,
            currency            :$currency,
            purpose             :$purpose,
            message             :$message,
            purposeable_id          :$purpose_id,
        );

        if (! $result) {
            return false;
        }

        return $result;
    }
  

    public function getTransaction($id)
    {
        # code...
        $transaction = $this->account->accountTransactions()->where('id', $id)
            ->first();
        return $transaction;
    }

    public function getTransactions()
    {
        # code...
        $transactions = $this->account->accountTransactions()
            ->get();
        return $transactions;
    }
    public function getSalesTransactions()
    {
        # code...
        $transactions = $this->account->accountTransactions()
            ->where('purpose', "SALES")
            ->get();
        return $transactions;
    }
    public function getSuppliesTransactions()
    {
        # code...
        $transactions = $this->account->accountTransactions()
            ->where('purpose', "SUPPLY")
            ->get();
        return $transactions;
    }
    public function getLoansTransactions()
    {
        # code...
        $transactions = $this->account->accountTransactions()
            ->where('purpose', "LOANS")
            ->get();
        return $transactions;
    }

    public function setPurposeable($purpose)
    {
        # code...
        if ($purpose == "SALES") {
            $purposable_type = Sale::class;
        } else if ($purpose == "LOANS") {
            $purposable_type = Loan::class;
        } else if ($purpose == "SUPPLIES") {
            $purposable_type = Supply::class;
        } else if ($purpose == "ORDERS") {
            $purposable_type = Order::class;
        } else {
            $purposable_type = null;
        }

        return $purposable_type;
    }
}
