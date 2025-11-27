<?php
namespace App\Repositories;

use Illuminate\Support\Str;

class AccountRepository
{
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
        $result = $account->accounts()->create(
            [
                "account"     => $account_name,
                "account_ref" => $account_ref,
            ]
        );
        return $result;
    }
}
