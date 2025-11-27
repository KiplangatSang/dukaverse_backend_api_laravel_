<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\ApiResource;
use App\Models\Account;
use App\Http\Resources\StoreFileResource; // Added use statement
use App\Http\Resources\ResponseHelper; // Added use statement
use App\Services\AccountService;
use App\Services\AuthService;
use App\Repositories\AccountsRepository;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @OA\Tag(
 *     name="Accounts",
 *     description="API Endpoints for managing Accounts"
 * )
 */
class AccountsController extends BaseController
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
    //     return new AccountService(new AccountsRepository(), $this->storeFileResource, $this->responseHelper);
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
    public function index()
    {
        $this->authorize('viewAny', Account::class);

        try {
            $accounts = $this->accountService->getIndexData();
            return $this->sendResponse($accounts, "Accounts fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching accounts', $e->getMessage());
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
     * @OA\Post(
     *     path="/api/v1/accounts",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new account",
     *     @OA\Response(response=201, description="Account created")
     * )
     */
    public function store(StoreAccountRequest $request)
    {
         return $this->sendResponse($request->all(), "Account created successfully", 201);
        $this->authorize('create', Account::class);

        try {
            $account = $this->accountService->createAccount($request->validated());
            return $this->sendResponse($account, "Account created successfully", 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating account', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{account}",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get a specific account",
     *     @OA\Parameter(name="account", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($account)
    {
        $accountModel = $this->accountService->getShowData($account);
        if(isset($accountModel['error'])) {
            return $this->sendError('Error fetching account', $accountModel['error'], $accountModel['code'] ?? 400);
        }
        $this->authorize('view', $accountModel);

        try {
            return $this->sendResponse($accountModel, "Account fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching account', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{account}/edit",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Show form for editing an account",
     *     @OA\Parameter(name="account", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function edit($account)
    {
        try {
            $data = $this->accountService->getShowData($account);
            return $this->sendResponse($data, "Edit data fetched successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error fetching edit data', $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/accounts/{account}",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update an account",
     *     @OA\Parameter(name="account", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Account updated")
     * )
     */
    public function update(UpdateAccountRequest $request, $account)
    {
        $accountModel = $this->accountService->getShowData($account);
        $this->authorize('update', $accountModel);

        try {
            $updatedAccount = $this->accountService->updateAccount($account, $request->validated());
            return $this->sendResponse($updatedAccount, "Account updated successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error updating account', $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/accounts/{account}",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete an account",
     *     @OA\Parameter(name="account", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Account deleted")
     * )
     */
    public function destroy($account)
    {
        $accountModel = $this->accountService->getShowData($account);
        $this->authorize('delete', $accountModel);

        try {
            $result = $this->accountService->deleteAccount($account);
            return $this->sendResponse($result, "Account deleted successfully");
        } catch (\Exception $e) {
            return $this->sendError('Error deleting account', $e->getMessage());
        }
    }
}
