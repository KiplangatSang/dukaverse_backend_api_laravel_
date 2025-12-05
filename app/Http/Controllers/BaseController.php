<?php
namespace App\Http\Controllers;

use App\Helpers\Accounts\Account;
use App\Helpers\Locations\UserLocationWithIPAddress;
use App\Http\Resources\ApiResource;
use App\Models\Ecommerce;
use App\Models\Retail;
use App\Models\SessionRetail;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

/**
 * @OA\Info(
 *     title="Ceroisoft API",
 *     version="1.0.0",
 *     description="API documentation for Ceroisoft application.",
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use a valid JWT token to access this endpoint"
 * )
 */

class BaseController extends Controller
{

    protected ApiResource $apiResource;
    private $account_manager;

    public function __construct(ApiResource $apiResource)
    {
        $this->apiResource = $apiResource;

        if (Auth::check()) {
            $this->account_manager = new Account(Auth::user());
        }
    }

    public function hasSession()
    {
        return $this->checkUserHasSession();
    }

    private function checkUserHasSession()
    {

        $user = User::where('id', Auth::id())->first();
        if ($user->sessionAccount) {
            return true;
        }
        return false;

    }

    public function logPageView(Request $request)
    {
        $account = $this->user();
        if ($this->hasSession()) {
            $account = $this->account();
        }

        $activity = $account->activities()->create([
            'user_id'          => Auth::id(),
            'activity_type'    => 'page_view',
            'page'             => $request->path(),
            'operating_system' => $request->header('User-Agent'),
            'ip_addresses'     => $request->ip(),
            'session_id'       => $this->user() ? $this->user()->token : Session::getId(),
            'is_success'       => true,
        ]);

        return $activity;

    }

    public function saveUserLocationWithIp(Request $request)
    {

        $userLocationWithIp = new UserLocationWithIPAddress();
        $userLocationWithIp->getUserLocation($request);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */

    //gets retails list sent to home controller for choosing

    public function dukaverse()
    {

        return User::where('id', User::DUKAVERSE_ID)->firstOrFail();
    }

    public function jsonEncodeData($request_meta_data)
    {
        $meta_data = null;

        if (! $request_meta_data) {
            return $meta_data;
        }

        if (is_string($request_meta_data)) {
            // Attempt to decode the JSON string
            json_decode($request_meta_data);
            // Check if decoding resulted in an error
            if (json_last_error() === JSON_ERROR_NONE) {
                // Valid JSON string
                $meta_data = $request_meta_data;
            } else {
                // Invalid JSON string; handle the error or set a default value
                $meta_data = json_encode([]);
            }
        } else {
            // $request->meta_data is not a string; encode it to JSON
            $meta_data = json_encode($request_meta_data);
        }

        return $meta_data;

    }

    public function getAccount()
    {

        /**
         * $sessionRetail = $this->user()->sessionRetail;

         *if (! $sessionRetail) {
         *    return $this->setRetail();
         *}
         *
         *$retail = $sessionRetail->retail;
         *
         *if (! $retail) {
         *    SessionRetail::destroy($sessionRetail->id);
         *}
         *
         *$retail['complete'] = $this->calculate_profile($retail);

         */

        $account = $this->account();

        return $account;
    }

    public function getIPAddress(Request $request)
    {
        $locationHelper = new UserLocationWithIPAddress();
        $location_data  = $locationHelper->getUserLocation($request);

        $ip = $location_data['ip'];

        if (! $ip) {
            return false;
        }

        return $ip;
    }

    public function retail()
    {

        return $this->getAccount();
    }

    public function setManager()
    {
        return $this->account_manager = new Account(Auth::user());
    }

    public function account()
    {
        $this->setManager();
        $account_manager = $this->account_manager;

        if ($account_manager->error) {
            info(["basecontroller account_manager error" => $account_manager->error]);
        }

        $account = $account_manager->account;

        if (! $account) {
            info(["basecontroller account  error" => $account_manager->error]);
            info(["basecontroller account  errors" => $account_manager::$errors]);
            $accounts_list = $account_manager->getAccountList();
            if ($accounts_list && count($accounts_list) >= 1) {
                abort(403, "You need to set the session account");
                // Changing abort message from array to string to fix exception

            } else {
                abort(404, "You need to set the create an account");
            }
            return false;
        }

        info(["basecontroller account" => $account]);
        return $account;

    }

    public function accountMembers()
    {
        $this->setManager();
        $account_manager = $this->account_manager;

        if ($account_manager->error) {
            info(["basecontroller account_manager error" => $account_manager->error]);
        }

        $members = $account_manager->members();

        return $members;

    }

    public function getAccountList()
    {
        $this->setManager();
        $account_manager = $this->account_manager;

        $account_lists = $account_manager->getAccountList();

        return $account_lists;
    }

    public function setAccount($account_type, $account)
    {
        $this->setManager();
        $account_manager = $this->account_manager;
        $account         = $account_manager->setAccount($account_type, $account);

        if ($account_manager->error) {
            info(["basecontroller account_manager error" => $account_manager->error]);
        }
        if (! $account) {
            info(["basecontroller account_manager error" => $account_manager->error]);

            return false;
        }

        return $account;

    }

    public function getEcommerce()
    {

        return $this->account();
    }

    public function ecommerce(Request $request)
    {

        $vendorId = $request->route('vendor_id'); // Get vendor_id from the route

        if (! $vendorId) {
            abort(403, 'Unauthorized or vendor not specified.');
        }

// Optionally: check if the vendor exists and belongs to the authenticated user
        $vendor = Ecommerce::where('vendor_id', $vendorId)->first();

        return $vendor;

    }

    public function formatPhoneNumber($code, $phone_number)
    {
        # code...
        $phoneNo = null;
        if (strlen($phone_number) == 9) {
            $phoneNo = '254' . $phone_number;
        } else if (strlen($phone_number) == 10) {
            $phone_number = trim($phone_number, "0");
            $phoneNo      = $code . $phone_number;
        } else {
            return $phoneNo;
        }
        return $phone_number;
    }
    public function user()
    {
        $user = User::where('id', Auth::id())
            ->first();
        return $user;
    }

    public function admin()
    {
        $admin = User::where('id', 1)
            ->where('is_admin', true)
            ->first();
        return $admin;
    }

    public function individual()
    {
        $individual = $this->user()->individual()->first();
        return $individual;
    }

    public function employee()
    {
        $employee = $this->user()->employee()->first();

        if (! $employee) {
            return false;
        } else {
            return $employee;
        }
    }

    public function savePDFFileRemotely(User $user, $type, $name)
    {
        # code...
        $basePDFUrl = "https://storage.googleapis.com/dukaverse-e4f47.appspot.com/app/" . $user->id . "/" . $type . "/" . $name;
        return $basePDFUrl;
    }

    public function savePDFFileLocally(User $user, $type, $name)
    {
        # code...
        // $directory = "pdfs/" . $user->id . "/" . $name;
        $directory = "pdfs/" . $user->id;

        $fullDirectoryPath = public_path($directory);

        if (! File::exists($fullDirectoryPath)) {
            // Create the directory with 0755 permissions and enable recursive directory creation
            // File::makeDirectory($fullDirectoryPath, 0755, true);
            File::makeDirectory($fullDirectoryPath);

        }

        $pdfPath = $directory . "/" . $name;

        $fullPath = public_path($pdfPath); // Full path to save

        return $fullPath;
    }

    public function calculate_profile($profile)
    {
        if (! $profile) {
            return 0;
        }
        $columns    = preg_grep('/(.+ed_at)|(.*id)/', array_keys($profile->toArray()), PREG_GREP_INVERT);
        $per_column = 100 / count($columns);
        $total      = 0;

        foreach ($profile->toArray() as $key => $value) {
            if ($value !== null && $value !== [] && in_array($key, $columns)) {
                $total += $per_column;
            }
        }
        $total = number_format($total, 2);
        return $total;
    }

    public function sendResponse($result, $message, $code = null)
    {

        return $this->apiResource->success($result, $message, code: $code ?? 200);

        // return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */

    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        // If errorMessages is a string, convert it to an array
        if (! is_array($errorMessages)) {
            $errorMessages = [$errorMessages];
        }

        return $this->apiResource->error($error, $code, $errorMessages);
    }

    public function getTransactionType($gateway)
    {
        # code...
        $transaction_type = null;
        if ($gateway == "DUKAVERSE") {
            $transaction_type = 1;
        } else {
            $transaction_type = 3;
        }

        return $transaction_type;
    }

    // public function saveTransaction(
    //     $gateway,
    //     $sender_account_id,
    //     $receiver_account_id,
    //     $sender_phone_number,
    //     $receiver_phone_number,
    //     $amount,
    //     $transaction_type,
    //     $cost,
    //     $purpose,
    //     $message,
    //     $purpose_id = null,
    //     $currency = "ksh",
    // ) {
    //     # code...

    //     $requestdata = [
    //         "gateway"               => $gateway,
    //         'sender_account_id'     => $sender_account_id,
    //         'receiver_account_id'   => $receiver_account_id,
    //         'sender_phone_number'   => $sender_phone_number,
    //         'receiver_phone_number' => $receiver_phone_number,
    //         "amount"                => $amount,
    //         "transaction_type"      => $transaction_type,
    //         "cost"                  => $cost,
    //         "currency"              => $currency,
    //         "purpose"               => $purpose,
    //         "message"               => $message,
    //         "purpose_id"            => $purpose_id,
    //     ];

    //     $request = new Request();
    //     $request->setMethod('POST');
    //     $request->request->add($requestdata);

    //     $transactionController = new TransactionController();
    //     $result                = $transactionController->store($request);

    //     if (! $result) {
    //         return false;
    //     }

    //     return $result;
    // }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */

    public function mpesaResponse($OriginalString)
    {
        # code...

        // Without optional parameter NoOfElements
        $request_ids = explode("dukaverse", $OriginalString);

        $transaction_id = $request_ids[1];

        $transaction = Transaction::where('transaction_id', $transaction_id)
            ->with('transactionable')
            ->with('senderAccount')
            ->with('receiverAccount')
            ->first();

        if (! $transaction) {
            return false;
        }

        $response['1'] = $transaction;

        return $response;
    }

    public function getRetailEmployeePermissions()
    {
        # code...
        $permissions = Retail::employeePermissions();
        return $permissions;
    }
    public function getUserUnreadNotifications()
    {
        return $this->user()->unreadNotifications()->latest()->get();
    }

    public function getUserNotifications()
    {
        return $this->user()->notifications()->get();
    }
    public function getUserReadNotifications()
    {
        return $this->user()->readNotifications()->get();
    }

    public function getRetailUnreadNotifications()
    {
        return $this->getAccount()->unreadNotifications()->latest()->get();
    }

    public function getRetailNotifications()
    {
        return $this->getAccount()->notifications()->get();
    }
    public function getRetailReadNotifications()
    {
        return $this->getAccount()->readNotifications()->get();
    }
}
