<?php
namespace App\Repositories;

use App\Helpers\Accounts\Account;
use App\Helpers\Accounts\AccountInterface;

class BaseRepository implements AccountInterface
{
    public function __construct(protected readonly Account $account)
    {
    }

    public function getAccount()
    {
        return $this->account->account;
    }

    public function getCurrentAccountType()
    {
        return $this->account->getCurrentAccountType();
    }

    public function permissions($user)
    {
        return $this->account->permissions($user);
    }

    public function adminAccount($user)
    {
        return $this->account->adminAccount($user);
    }

    public function members()
    {
        return $this->account->members();
    }

}
