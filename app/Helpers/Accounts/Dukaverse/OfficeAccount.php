<?php
namespace App\Helpers\Accounts\Dukaverse;

use App\Helpers\Accounts\Account;
use App\Models\Office;
use App\Models\Retail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OfficeAccount implements OfficeAccountContract
{
    protected $user;

    public $account         = null;
    protected $account_type = null;
    public $error           = null;

    public function __construct($user, Office $office = null, Retail $retail = null)
    {
        if (! $user) {
            return false;
        }

        $this->user = $user;

        $this->error = null;

        if ($office) {
            $this->setSessionOffice($office);
        }

        $this->setOffice();
    }

    private function setSessionOffice(Office $office)
    {

        $sessionAccount = $this->user->sessionAccount()->create(
            ["sessionable_id"  => $office->id,
                "sessionable_type" => Office::class,
                "user_id"          => $this->user->id,
                "token"            => Hash::make($this->user->username),
                "last_used_at"     => now(),
                "expires_at"       => null,
            ]
        );

        if ($sessionAccount) {
            $this->account = $sessionAccount->sessionable;
        } else {
            $this->error = "This account could not create session for " . Office::class . " please check the existence of the " . User::class . " and " . Office::class . " lasses ";
            Account::setErrors("Office", $this->error);

        }

    }

    public function getOffice()
    {

        $office = $this->user->sessionAccount->sessionable;

        if (! $office) {
            $this->error = "This account session office could be found. Have you set the session office";
            Account::setErrors("Office", $this->error);
        }
        return $office;

    }
    public function setOffice()
    {
        $sessionAccount = $this->user->sessionAccount;

        $account = null;

        if (! $sessionAccount) {
            $available_offices = $this->getAvailableOffices();
            if ($available_offices && count($available_offices) == 1) {
                $this->setSessionOffice($available_offices[0]);
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

        $this->error = "Could not set session for this Office user.";

        Account::setErrors("Office", $this->error);

    }
    public function getAvailableOffices()
    {
        $offices = null;
        if (
            $this->user->role == User::ADMIN_ACCOUNT_TYPE
        ) {
            $offices = $this->user->offices;
        } else if ($this->user->role == User::DUKAVERSE_EMPLOYEE_ACCOUNT_TYPE
            || $this->user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
            $offices = $this->user->employee->ownerable()->get();
        }

        if (! $offices) {
            $this->error = "This account offices could not be found.";
        }

        Account::setErrors("Office", $this->error);

        return $offices;

    }

    public function getAccount()
    {
        return $this->account;

        $account = $this->account;

        if (! $account) {
            $this->error = "This account could be found. Have you set the session account?";
            Account::setErrors("Office", $this->error);

        }

        return $account;

    }

    public function adminAccount($user)
    {

        if ($user->role == User::DUKAVERSE_ADMIN_ACCOUNT_TYPE) {
            return true;
        } else if ($user->role == User::DUKAVERSE_EMPLOYEE_ACCOUNT_TYPE) {
            return false;
        } else {
            return false;
        }

    }

    public function permissions($user)
    {

        $permissions = [];

        if ($user->role == User::DUKAVERSE_ADMIN_ACCOUNT_TYPE) {
            $permissions = Role::EMPLOYEE_PERMISSIONS;
        } else if ($user->role == User::DUKAVERSE_EMPLOYEE_ACCOUNT_TYPE || $user->role == User::EMPLOYEE_ACCOUNT_TYPE) {
            $rawPermissions = $user->employee->roles()
                ->get(["permissions"])
                ->pluck('permissions')
                ->flatten()
                ->toArray();

            // Step 2: Map only the allowed keys from EMPLOYEE_PERMISSIONS
            $permissions = array_intersect_key(Role::EMPLOYEE_PERMISSIONS, array_flip($rawPermissions));
        }

        return $permissions;
    }

    public function owners()
    {
        return $this->account->officeable;
    }

}
