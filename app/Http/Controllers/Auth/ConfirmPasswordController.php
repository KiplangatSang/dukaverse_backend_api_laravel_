<?php
namespace App\Http\Controllers\Auth;

use App\Http\Resources\ApiResource;

use App\Http\Controllers\Controller;

class ConfirmPasswordController extends Controller
{

  public function __construct(
        protected ApiResource $apiResource,
    ) {
        parent::__construct($apiResource);
    }
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
}
