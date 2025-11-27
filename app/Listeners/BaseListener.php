<?php

namespace App\Listeners;

use App\Http\Controllers\BaseController;

class BaseListener
{

    public function __construct()
    {
        //
    }


    public function retail()
    {
        //
        $basecontroller = new BaseController();
        return $basecontroller->getAccount();
    }
}
