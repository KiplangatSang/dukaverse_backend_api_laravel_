<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Http\Resources\StoreFileResource; // Added use statement
use App\Http\Resources\ResponseHelper; // Added use statement
use App\Services\AccountService;
use App\Services\AuthService;
use App\Services\AccountsService;
use Exception;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Accounts",
 *     description="API Endpoints for managing Accounts"
 * )
 */
class AccountController extends BaseController
{

    use AuthorizesRequests;
    use ValidatesRequests;

    public function __construct(
        private readonly StoreFileResource $storeFileResource,
        private readonly ResponseHelper $responseHelper,
        private readonly AuthService $authService,
        ApiResource $apiResource,
        private readonly AccountService $accountService
    ) {
        parent::__construct($apiResource);
    }

    // private function getAccountService()
    // {
    //     return new AccountService(new AccountsService(), $this->storeFileResource, $this->responseHelper);
    // }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of accounts",
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index(Request $request)
    {

        try {
            $accounts = $this->accountService->getIndexData($request->type);
            return $this->sendResponse($accounts['data'], "Accounts fetched successfully");
        } catch (Exception $e) {
            return $this->sendError('Error fetching accounts', ["errors" => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts/create",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Show form for creating an account",
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function create()
    {
        try {
            // For now, return empty data or form data
            $data = [];
            return $this->sendResponse($data, "Create data fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching create data', $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{type}",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get a specific account",
     *     @OA\Parameter(name="account", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($account)
    {

        try {
            $accountModel = $this->accountService->getShowData($account);
            if (isset($accountModel['error'])) {
                return $this->sendError('Error fetching account', $accountModel['error'], 400);
            }

            return $this->sendResponse($accountModel, "Account fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching account', $e->getMessage());
        }
    }
}
