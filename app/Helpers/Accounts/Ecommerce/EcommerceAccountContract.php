<?php
namespace App\Helpers\Accounts\Ecommerce;

interface EcommerceAccountContract
{
    public function getEcommerce();

    public function setEcommerce();

    public function getAvailableEcommerces();

    public function getAccount();

    public function permissions($user);
    public function owners();
}
