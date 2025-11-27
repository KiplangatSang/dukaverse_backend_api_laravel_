<?php
namespace App\Helpers\Accounts;

interface AccountContract
{
    public function dukaverseAccount();

    public function retailAccount();

    public function supplierAccount();

    public function pushErrors($source, $errors);

    public function adminAccount($user);

}
