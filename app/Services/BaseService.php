<?php
namespace App\Services;

use App\Helpers\Accounts\Account;
use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\User;
use App\Services\AppService;
use App\Services\CRMService;
use App\Services\EcommerceService;
use App\Services\ProjectService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;

class BaseService
{

   
    protected $account;
    protected readonly User $user;
 
    public function __construct() {
        // Initialization code if needed
        if (Auth::check()) {
                       $accountHelper   = new Account(Auth::user());

                         $this->account = $accountHelper->account;
        }

     }

   
    public function user()
    {

        $user = Auth::user();
        return $user;
    }

//     public function ecommerce(Request $request)
//     {

//         $vendorId = $request->route('vendor_id'); // Get vendor_id from the route

//         if (! $vendorId) {
//             abort(403, 'Unauthorized or vendor not specified.');
//         }

// // Optionally: check if the vendor exists and belongs to the authenticated user
//         $vendor = Ecommerce::where('vendor_id', $vendorId)->first();

//         return $vendor;

//     }

}
