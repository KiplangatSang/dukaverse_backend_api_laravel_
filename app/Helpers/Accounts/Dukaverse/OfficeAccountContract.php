<?php
namespace App\Helpers\Accounts\Dukaverse;

interface OfficeAccountContract
{
    public function getOffice();

    public function setOffice();

    public function getAvailableOffices();

    public function getAccount();

    public function adminAccount($user);

    public function permissions($user);

    public function owners();
}
