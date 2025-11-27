<?php
namespace App\Helpers\Accounts\Retail;

use App\Helpers\Accounts\Account;
use App\Models\Retail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RetailAccount implements RetailAccountContract
{
    protected $user;

    public $account         = null;
    protected $account_type = null;
    public $error           = null;

    public function __construct(User $user, Retail $retail = null)
    {

        if (! $user) {
            return false;
        }

        $this->user = $user;

        $this->error = null;

        if ($retail) {
            $this->setSessionRetail($retail);
        }

        $this->setRetail();

    }

    private function setSessionRetail(Retail $retail)
    {

        $sessionAccout = $this->user->sessionAccount()->create(
            ["sessionable_id"  => $retail->id,
                "sessionable_type" => Retail::class,
                "user_id"          => $this->user->id,
                "token"            => Hash::make($this->user->username),
                "last_used_at"     => now(),
                "expires_at"       => null,
            ]
        );

        if ($sessionAccout) {
            $this->account = $sessionAccout->sessionable;
        } else {
            $this->error = "This account could not create session for " . Retail::class . " please check the existence of the " . User::class . " and " . Retail::class . " lasses ";
        }

    }

    public function getRetail()
    {

        $retail = $this->user->sessionAccount->sessionable;

        if (! $retail) {
            $this->error = "This account session retail could be found. Have you set the session retail";
        }
        return $retail;

    }

    public function setRetail()
    {
        // $this->account = $this->user->sessionAccount->sessionable;

        $sessionAccount = $this->user->sessionAccount;

        $account = null;

        if (! $sessionAccount) {
            $available_retails = $this->getAvailableRetails();
            if ($available_retails && count($available_retails) == 1) {
                $this->setSessionRetail($available_retails[0]);
                $sessionAccount = $this->user->sessionAccount;

            } else {
                return false;

            }

        }

        if ($sessionAccount) {
            $account = $this->user->sessionAccount->sessionable;

        }
        if ($account) {
            $this->account = $account;
        }

        $this->error = "Could not set session for this Dukaverse user.";

        Account::setErrors("RetailsAccount", $this->error);

    }

    public function getAvailableRetails()
    {

        $retails = $this->user->userRetails;

        if (! $retails) {
            $this->error = "This account retails could not be found.";
        }

        return $retails;
    }

    public function getAccount()
    {
        return $this->account;

        $account = $this->account;

        if (! $account) {
            $this->error = "This account could be found. Have you set the session account?";
        }

        return $account;

    }

    public function adminAccount($user)
    {

        if ($user->role == User::RETAILER_ACCOUNT_TYPE) {
            return true;
        } else if ($user->role == User::RETAIL_EMPLOYEE_ACCOUNT_TYPE) {
            return false;
        }

    }

    public function permissions($user)
    {
        $permissions = [];
        if ($user->role == User::RETAILER_ACCOUNT_TYPE) {
            $permissions = Role::EMPLOYEE_PERMISSIONS;

        } else if ($user->role == User::RETAIL_EMPLOYEE_ACCOUNT_TYPE) {
            $permissions = $user->employee->roles()->with('permissions')->get()->pluck('permissions')->flatten()->toArray();

        }

        return $permissions;

    }

    public function owners()
    {
        return $this->account->retailable;
    }

}
