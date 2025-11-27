<?php
namespace App\Services;

use App\Helpers\Accounts\Account;
use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Repositories\AppRepository;
use App\Repositories\CRMRepository;
use App\Repositories\EcommerceRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\Auth;

class BaseService
{

    protected AppRepository $appRepository;
    protected ProjectRepository $project_repository;
    protected EcommerceRepository $ecommerce_repository;
    protected CRMRepository $crm_repository;
    protected WalletRepository $wallet_repository;
    protected readonly Account $account;

    public function __construct() {
        // Initialization code if needed
        $this->appRepository = new AppRepository();
        if (Auth::check()) {
            $this->account              = new Account(Auth::user());
            $this->project_repository   = new ProjectRepository($this->user());
            $this->ecommerce_repository = new EcommerceRepository($this->account->account);
            $this->crm_repository       = new CRMRepository($this->account, $this->user());
            $this->wallet_repository    = new WalletRepository($this->account, $this->user());
        }
    }

    public function getNoProfileImage()
    {
        $baseImages = $this->appRepository->getBaseImages();
        return $baseImages['noprofile'];
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
