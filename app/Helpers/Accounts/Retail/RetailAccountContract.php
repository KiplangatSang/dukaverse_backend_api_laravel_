<?php
namespace App\Helpers\Accounts\Retail;

interface RetailAccountContract
{
    public function getRetail();

    public function setRetail();

    public function getAvailableRetails();

    public function getAccount();

    public function adminAccount($user);

    public function permissions($user);
    public function owners();

}
