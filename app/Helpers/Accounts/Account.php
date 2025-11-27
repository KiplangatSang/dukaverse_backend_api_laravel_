<?php
namespace App\Helpers\Accounts;

use App\Helpers\Accounts\AccountContract;
use App\Helpers\Accounts\Dukaverse\OfficeAccount;
use App\Helpers\Accounts\Ecommerce\EcommerceAccount;
use App\Helpers\Accounts\Retail\RetailAccount;
use App\Models\Ecommerce;
use App\Models\Office;
use App\Models\Retail;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class Account implements AccountContract
{
    protected $user;

    public $account         = null;
    protected $account_type = null;
    public $error           = null;
    public static $errors   = [];

    const DUKAVERSE_ACCOUNT_TYPE = "DUKAVERSE_ACCOUNT";
    const RETAIL_ACCOUNT_TYPE    = "RETAIL_ACCOUNT";
    const ECOMMERCE_ACCOUNT_TYPE = "ECOMMERCE_ACCOUNT";
    const SUPPLIER_ACCOUNT_TYPE  = "SUPPLIER_ACCOUNT";

    public function __construct($user = null)
    {
        if (! $user) {

            if (Auth::check()) {$user = User::where('id', Auth::id())->first();} else {
                return false;
            }

        }

        $this->user = $user;

        $this->dukaverseAccount();
        $this->retailAccount();
        $this->supplierAccount();

        $this->account = $this->getAccount();

        if (! $this->account) {
            info(["__construct Account error" => $this->error]);
        }

        info(["Account account" => $this->account]);

    }

    private function getAccount()
    {
        info(["account_type" => $this->account_type]);
        switch ($this->account_type) {
            case self::DUKAVERSE_ACCOUNT_TYPE:
                info(["getAccount account type" => "DUKAVERSE_ACCOUNT_TYPE"]);
                $officeAccount = new OfficeAccount($this->user);
                $account       = $officeAccount->account;

                info(["getAccount account " => $account]);

                if (! $account) {
                    $this->error = $officeAccount->error;
                }

                return $account;
                break;

            case self::ECOMMERCE_ACCOUNT_TYPE:
                $ecommerceAccount = new EcommerceAccount($this->user);
                $account          = $ecommerceAccount->account;
                if (! $account) {
                    $this->error = $ecommerceAccount->error;
                }

                info(["account type" => "ECOMMERCE_ACCOUNT_TYPE"]);

                return $account;
                break;
            case self::RETAIL_ACCOUNT_TYPE:
                $retailAccount = new RetailAccount($this->user);
                $account       = $retailAccount->account;
                if (! $account) {
                    $this->error = $retailAccount->error;
                }

                info(["account type" => "RETAIL_ACCOUNT_TYPE"]);

                return $account;
                break;
            default:
                return $this->user;
        }

        info(["account error" => $this->error]);

    }

    public static function getAccountInstance($account)
    {

        try {
            switch ($account) {
                case $account instanceof (Office::class):
                    return Office::class;
                    break;
                case $account instanceof (Ecommerce::class):
                    return Ecommerce::class;
                    break;
                case $account instanceof (Retail::class):
                    return Retail::class;
                    break;
                default:
                    return User::class;
            }
        } catch (Exception $e) {
            info(["account error" => $e->getMessage()]);
        }

    }

    public static function getAccountType($account)
    {
        $account_instance = self::getAccountInstance($account);

        $parts               = explode('//', $account_instance);
        $lastItem            = count($parts) - 1;
        $originalAccountName = $parts[$lastItem]; // Get the first part of the split
        return $originalAccountName;
    }

    public function setAccount($accountType, $account)
    {

        switch ($accountType) {
            case self::DUKAVERSE_ACCOUNT_TYPE:
                $officeAccount = new OfficeAccount($this->user, $account);
                $account       = $officeAccount->account;
                if (! $account) {
                    $this->error = $officeAccount->error;
                }
                return $account;
                break;

            case self::ECOMMERCE_ACCOUNT_TYPE:
                $ecommerceAccount = new EcommerceAccount($this->user, $account);
                $account          = $ecommerceAccount->account;
                if (! $account) {
                    $this->error = $ecommerceAccount->error;
                }
                return $account;
                break;
            case self::RETAIL_ACCOUNT_TYPE:
                $retailAccount = new RetailAccount($this->user, $account);
                $account       = $retailAccount->account;
                if (! $account) {
                    $this->error = $retailAccount->error;
                }
                return $account;
                break;
            default:
                return $this->user;
        }

        info(["account error" => $this->error]);

    }

    public function getAccountList()
    {
        $accounts      = [];
        $user_accounts = null;
        $state         = false;

        switch ($this->account_type) {
            case self::DUKAVERSE_ACCOUNT_TYPE:
                $officeAccount = new OfficeAccount($this->user);

                $user_accounts = $officeAccount->getAvailableOffices();

                if (! $user_accounts) {
                    $this->error = $officeAccount->error;
                    $state       = false;

                } else {
                    $state = true;

                }

                break;

            case self::ECOMMERCE_ACCOUNT_TYPE:
                $ecommerceAccount = new EcommerceAccount($this->user);
                $user_accounts    = $ecommerceAccount->getAvailableEcommerces();
                if (! $user_accounts) {
                    $this->error = $ecommerceAccount->error;
                    $state       = false;
                } else {
                    $state = true;

                }

                break;
            case self::RETAIL_ACCOUNT_TYPE:
                $retailAccount = new RetailAccount($this->user);
                $user_accounts = $retailAccount->getAvailableRetails();
                if (! $user_accounts) {
                    $this->error = $retailAccount->error;
                    $state       = false;

                } else {
                    $state = true;

                }
                break;
            default:
                $user_accounts = null;
                $state         = true;

        }

        if (! $user_accounts) {

            return $user_accounts;
        }

        if (count($user_accounts) == 1) {
            $this->setAccount($this->account_type, $user_accounts[0]);
            $state = true;
        }
        info(["accounts fetching error" => $this->error]);
        $accounts["account_type"] = $this->account_type;

        $accounts["accounts"] = $user_accounts;
        $accounts["state"]    = $state;

        return $accounts;

    }

    public function dukaverseAccount()
    {
        if ($this->user->role == User::ADMIN_ACCOUNT_TYPE || $this->user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
            return $this->account_type = self::DUKAVERSE_ACCOUNT_TYPE;
        }

    }

    public function retailAccount()
    {
        if ($this->user->role == User::RETAILER_ACCOUNT_TYPE) {
            return $this->account_type = self::RETAIL_ACCOUNT_TYPE;
        }

    }

    public function supplierAccount()
    {
        if ($this->user->role == User::SUPPLIER_ACCOUNT_TYPE) {
            return $this->account_type = self::SUPPLIER_ACCOUNT_TYPE;
        }

    }

    public function pushErrors($source, $new_errors)
    {

        $this::setErrors($source, $new_errors);

        return true;

    }

    public static function setErrors($source, $new_errors)
    {

        if ($new_errors) {
            $errors = self::$errors;
            array_push($errors, [now()->format("YY-mm-dd H:i:s") => [$source => $new_errors]]);
            self::$errors = $errors;
        }
        return true;
    }

    public function adminAccount($user)
    {

        $is_admin = false;

        switch ($this->account_type) {
            case self::DUKAVERSE_ACCOUNT_TYPE:
                $officeAccount = new OfficeAccount($user);

                $is_admin = $officeAccount->adminAccount($user);
                break;

            case self::ECOMMERCE_ACCOUNT_TYPE:
                $ecommerceAccount = new EcommerceAccount($user);
                $is_admin         = $ecommerceAccount->adminAccount($user);
                break;
            case self::RETAIL_ACCOUNT_TYPE:
                $retailAccount = new RetailAccount($user);
                $is_admin      = $retailAccount->adminAccount($user);
                break;
            default:
                $is_admin = false;

        }
        return $is_admin;
    }

    public function permissions($user)
    {

        $permissions = null;

        switch ($this->account_type) {
            case self::DUKAVERSE_ACCOUNT_TYPE:
                $officeAccount = new OfficeAccount($user);

                $permissions = $officeAccount->permissions($user);

                if (! $permissions) {
                    $this->error = $officeAccount->error;
                }

                break;

            case self::ECOMMERCE_ACCOUNT_TYPE:
                $ecommerceAccount = new EcommerceAccount($user);
                $permissions      = $ecommerceAccount->permissions($user);
                if (! $permissions) {
                    $this->error = $ecommerceAccount->error;
                }

                break;
            case self::RETAIL_ACCOUNT_TYPE:
                $retailAccount = new RetailAccount($user);
                $permissions   = $retailAccount->permissions($user);
                if (! $permissions) {
                    $this->error = $retailAccount->error;

                }
                break;
            default:
                $permissions = [];

        }

        return $permissions;

    }

    public function authenticatedUserPermissions($user = null)
    {

        if (! $user) {

            if (Auth::check()) {$user = User::where('id', Auth::id())->first();} else {
                return false;
            }

        }

        $permissions = self::permissions($user);

        return $permissions;

    }

    public function members()
    {

        $members                     = new Collection();
        $employees                   = $this->account->employees()->with('user')->get();
        $users_from_employee_account = null;
        if ($employees) {
            $users                       = $employees->pluck('user')->flatten();
            $users_from_employee_account = $users;
        }
        $owners = $this->account->owners()->get();
        if ($owners) {
            if ($owners && $users_from_employee_account) {
                $members = $owners;
                $members = $members->merge($users_from_employee_account);

            } else {
                $members = $owners;
            }

        }
        return $members;

    }

    public function getCurrentAccountType()
    {
        return $this->account_type;
    }
}
