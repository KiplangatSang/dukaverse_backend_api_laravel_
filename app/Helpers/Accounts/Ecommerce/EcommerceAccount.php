<?php
namespace App\Helpers\Accounts\Ecommerce;

use App\Models\Ecommerce;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EcommerceAccount implements EcommerceAccountContract
{
    protected $user;

    public $account         = null;
    protected $account_type = null;
    public $error           = null;

    public function __construct(User $user, Ecommerce $ecommerce = null)
    {

        $this->user = $user;

        $this->error = null;

        if ($ecommerce) {
            $this->setSessionEcommerce($ecommerce);
        }

        $this->setEcommerce();

    }

    private function setSessionEcommerce(Ecommerce $ecommerce)
    {

        $sessionAccout = $this->user->sessionAccount()->create(
            ["sessionable_id"  => $ecommerce->id,
                "sessionable_type" => Ecommerce::class,
                "user_id"          => $this->user->id,
                "token"            => Hash::make($this->user->username),
                "last_used_at"     => now(),
                "expires_at"       => null,
            ]
        );

        if ($sessionAccout) {
            $this->account = $this->user->sessionAccount->sessionable;
        } else {
            $this->error = "This account could not create session for " . Ecommerce::class . " please check the existence of the " . User::class . " and " . Ecommerce::class . " lasses ";
        }

    }

    public function getEcommerce()
    {

        $ecommerce = $this->user->sessionAccount->sessionable;

        if (! $ecommerce) {
            $this->error = "This account session ecommerce $ecommerce could be found. Have you set the session ecommerce$ecommerce";
        }
        return $ecommerce;

    }

    public function setEcommerce()
    {
        $this->account = $this->user->sessionAccount->sessionable;

    }

    public function getAvailableEcommerces()
    {

        $ecommerces = $this->user->ecommerces;

        if (! $ecommerces) {
            $this->error = "This account ecommerces could not be found.";
        }

        return $ecommerces;
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
        } else if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
            return false;
        }

    }

    public function permissions($user)
    {
        $permissions = [];
        if ($user->role == User::RETAILER_ACCOUNT_TYPE) {
            $permissions = Role::EMPLOYEE_PERMISSIONS;

        } else if ($user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
            $permissions = $user->employee->roles()->with('permissions')->get()->pluck('permissions')->flatten()->toArray();

        }

        return $permissions;

    }

    public function owners()
    {
        return $this->account->user;
    }
}
