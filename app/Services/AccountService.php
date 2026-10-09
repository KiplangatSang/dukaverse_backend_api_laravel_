<?php
namespace App\Services;

use App\Helpers\Accounts\Account;
use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountService extends BaseService
{

    public function __construct(
        protected readonly StoreFileResource $storeFileResource,
        protected readonly ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function getIndexData(?string $type)
    {

        $user = User::where('id', $this->user()->id)->first();

        if (! $user) {
            return $this->responseHelper->error('Bad request', ['errors' => 'User not found']);
        }
        if ($type) {
            $validator = Validator::make(
                ['account_type' => $type],
                [
                    'account_type' => [
                        'required',
                        'string',
                        Rule::in(array_keys(User::ACCOUNT_TYPES)),
                    ],
                ]
            );

            if ($validator->fails()) {
                return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
            }

            $accountType = User::ACCOUNT_TYPES[$type] ?? null;

            if (! $accountType) {
                return $this->responseHelper->error('Bad request', ['errors' => 'Account not found']);
            }

            $accounts = [];
            switch ($accountType) {
                case User::RETAILER_ACCOUNT_TYPE:
                    $accounts = $user->retails()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        $accounts = $user->employee->ownerable->retails()->get()->toArray();
                    }
                    break;
                case User::SUPPLIER_ACCOUNT_TYPE:
                    $accounts = $user->retails()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        $accounts = $user->employee->ownerable->get()->toArray();
                    }
                    break;
                case User::ECOMMERCE_ACCOUNT_TYPE:
                    $accounts = $user->ecommerces()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        //retail has employee and also owns the ecommerce
                        $accounts = $user->employee->ownerable->ecommerces()->get()->toArray();
                    }
                    break;
                case User::ECOMMERCE_CUSTOMER_ACCOUNT_TYPE:
                    $accounts = $user->ecommerces()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        $accounts = $user->employee->ownerable->ecommerces()->get()->toArray();
                    }
                    break;
                case User::OFFICE_ACCOUNT_TYPE:
                    $accounts = $user->offices()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        $accounts = $user->employee->ownerable->get()->toArray();
                    }
                    break;
                default:
                    return $this->responseHelper->error('Bad request', ['errors' => 'Account not found']);

            }
        } else {
            //get all accounts
            $accounts = array_merge(
                $user->retails()->get()->toArray(),
                $user->ecommerces()->get()->toArray(),
                $user->offices()->get()->toArray()
            );
            if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                $accounts = array_merge(
                    $user->employee->ownerable->get()->toArray(),
                    // $user->employee->ownerable->get()->toArray(),
                    // $user->employee->ownerable->get()->toArray()
                );
            }
        }

        return $this->responseHelper->respond(['accounts' => $accounts], 'Accounts fetched successfully');
    }

    public function hasAccount(string $type): bool
    {
        $account_list = $this->getAccounts($type);
        if (isset($account_list["error"])) {
            return false;
        }
        $accounts = $account_list["data"]["accounts"];

        if (! $accounts || count($accounts) < 1) {
            return false;
        }
        if (count($account_list['accounts']) >= 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getAccounts(?string $type = null)
    {
        $accounts = [];
        $user     = User::where('id', $this->user()->id)->first();
        if ($type) {
            $accountType = User::ACCOUNT_TYPES[$type] ?? null;

            if (! $accountType) {
                return $this->responseHelper->error('Bad request', ['errors' => 'Account not found']);
            }

            switch ($accountType) {
                case User::RETAILER_ACCOUNT_TYPE:
                    $accounts = $user->retails()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        $accounts = $user->employee->ownerable->retails()->get()->toArray();
                    }
                    break;
                case User::SUPPLIER_ACCOUNT_TYPE:
                    $accounts = $user->retails()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        $accounts = $user->employee->ownerable->get()->toArray();
                    }
                    break;
                case User::ECOMMERCE_ACCOUNT_TYPE:
                    $accounts = $user->ecommerces()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        //retail has employee and also owns the ecommerce
                        $accounts = $user->employee->ownerable->ecommerces()->get()->toArray();
                    }
                    break;
                case User::ECOMMERCE_CUSTOMER_ACCOUNT_TYPE:
                    $accounts = $user->ecommerces()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        $accounts = $user->employee->ownerable->ecommerces()->get()->toArray();
                    }
                    break;
                case User::OFFICE_ACCOUNT_TYPE:
                    $accounts = $user->offices()->get()->toArray();
                    if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                        $accounts = $user->employee->ownerable->get()->toArray();
                    }
                    break;
                default:
                    return $this->responseHelper->error('Bad request', ['errors' => 'Account not found']);

            }
        } else {
            //get all accounts
            $accounts = array_merge(
                $user->retails()->get()->toArray(),
                $user->ecommerces()->get()->toArray(),
                $user->offices()->get()->toArray()
            );
            if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
                $accounts = array_merge(
                    $user->employee->ownerable->get()->toArray(),
                    // $user->employee->ownerable->get()->toArray(),
                    // $user->employee->ownerable->get()->toArray()
                );
            }
        }

        return $this->responseHelper->respond(['accounts' => $accounts], 'Accounts fetched successfully');
    }

    public function setSessionAccounts(array $data)
    {

        $account_type = $data['account_type'];
        $account      = $data['id'];

        if (! in_array($account_type, array_keys(User::ACCOUNT_TYPES))) {
            info(["basecontroller account_manager error" => "Invalid account type"]);
            return false;
        }

        if (! $account) {
            info(["basecontroller account_manager error" => "Account ID is required"]);
            return false;
        }
        $account_manager = new Account(Auth::user());

        $account = $account_manager->setAccount($account_type, $account);

        if ($account_manager->error) {
            info(["basecontroller account_manager error" => $account_manager->error]);
        }
        if (! $account) {
            info(["basecontroller account_manager error" => $account_manager->error]);

            return false;
        }

        return $account;

        return $this->accountRepository->setSessionAccount($type);
    }

    public function getShowData(string $account)
    {

        $accountModel = $this->account;
        if (isset($accountModel['error'])) {
            return $this->responseHelper->error('Bad request', ['errors' => $accountModel['error']]);
        }

        return $this->responseHelper->respond(['account' => $this->account], 'Account fetched successfully');
    }

    public function createSimpleOffice(array $data)
    {

        $validator = Validator::make($data,
            [
                "office_name"        => "required|string|min:3",
                "goods_and_services" => "required|array",
            ]
        );

        if ($validator->fails()) {
            return $this->responseHelper->error('Validation Error', $validator->errors());
        }
        $office_name_id = $this->generateOffieNameID($data['office_name']);
        $officeResult   = $this->user->offices()->updateOrCreate(
            [
                'user_id' => $this->user()->id,
            ],
            [
                "name"               => $data['office_name'],
                'name_id'            => $office_name_id,
                "goods_and_services" => json_encode($data['goods_and_services']),
            ]
        );

        if (! $officeResult) {
            return $this->responseHelper->error('Error registering account ', ["Could not register the office"]);
        }

        return $this->responseHelper->respond(['office' => $officeResult], "Office registered successfully");

    }

    public function generateOffieNameID($office_name)
    {
        $office_name_id = substr($office_name, 0, 3) . rand(1000, 10000000000);
        return $office_name_id;
    }
}
