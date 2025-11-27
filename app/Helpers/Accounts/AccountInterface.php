<?php
namespace App\Helpers\Accounts;

interface AccountInterface
{
    public function getAccount();
    public function getCurrentAccountType();
    public function permissions($user);
    public function adminAccount($user);
    public function members();
}
