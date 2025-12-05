<?php
namespace App\Services;

 use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
  use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService extends BaseService
{

    public function __construct(
        protected readonly StoreFileResource $storeFileResource,
        protected readonly ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }



      public function createAccount($account, $request): array
    {

        $start_code          = null;
        $account_number_code = null;
        $account_name        = null;
        $account_ref         = null;
        $id                  = $account->id ?? 0;

        switch ($request->accountType) {
            case "Client":
                $start_code          = "DV_CL";
                $account_number_code = rand(1000000, 1000000);
                $account_name        = $start_code . $account_number_code . $id;
                $account_ref         = $start_code . Str::random(7);
                break;
            case "Retail":
                $start_code          = "DV_RTL";
                $account_number_code = rand(100000, 1000000);
                $account_name        = $start_code . $account_number_code . $id;
                $start_code          = $start_code . substr($account->retail_county, 0, 3);
                $account_ref         = $start_code . Str::random(7);
                break;
            case "Supplier":
                $start_code          = "DV_SPL";
                $account_number_code = rand(1000000, 1000000);
                $account_name        = $start_code . $account_number_code . $id;
                $account_ref         = $start_code . Str::random(7);
                break;
            case "Admin":
                $start_code          = "DV_ADM";
                $account_number_code = rand(10000, 100000);
                $account_name        = $start_code . $account_number_code . $id;
                $account_ref         = $start_code . Str::random(6);
                break;
            default:
                $start_code          = "DV_CL";
                $account_number_code = rand(100000, 1000000);
                $account_name        = $start_code . $account_number_code . $id;
                $account_ref         = $start_code . Str::random(7);
        }

        # code...
        $result = $account->wallets()->create(
            [
                "account"     => $account_name,
                "account_ref" => $account_ref,
            ]
        );
        return $result;
    }

    
    public function getWallets(): array
    {
        $wallets = $this->account->wallets()->get();

        if (!$wallets) {
            return $this->responseHelper->error('Wallet not found', ['errors' => 'No account found with the given ID']);
        }

        if (isset($wallets['error'])) {
            return $this->responseHelper->error('Bad request', ['errors' => $wallets['error']]);
        }

        return $this->responseHelper->respond(['wallets' => $wallets], 'Account fetched successfully');
    }

     public function getWalletDetails($walletId): array
    {
        $wallet = $this->account->wallets()->where('id', $walletId)->first();

        if (!$wallet) {
            return $this->responseHelper->error('Wallet not found', ['errors' => 'No account found with the given ID']);
        }

        if (isset($wallet['error'])) {
            return $this->responseHelper->error('Bad request', ['errors' => $wallet['error']]);
        }

        return $this->responseHelper->respond(['wallet' => $wallet], 'Account fetched successfully');
    }

    public function getBalanceStatus()
    {
        // implement logic here based on transactions
        $balanceStatus = $this->account->transactions()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(balance) as daily_balance')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return $balanceStatus;

    }

    public function getMoneyHistory()
    {

//          Income

        $revenue = $this->getRevenue();
//  Expenses

        $expenses = $this->getExpenses();

// Transfer
        $transfer = $this->getExpenses();

        return [
            "revenue"  => $revenue,
            "expenses" => $expenses,
            "transfer" => $transfer,
        ];

    }

    public function getTransactionGateways()
    {
        //  Merchant List(Payment Methods)

        $gateways = $this->account->paymentGateways()->get();
        return $gateways;
    }

    public function getTransactionHistory()
    {
        //  Merchant List(Payment Methods)

        $transactions = $this->account->transactions()->get();
        return $transactions;
    }

}
