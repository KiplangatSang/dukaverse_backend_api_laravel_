<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\AdminService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends BaseController
{
    public function __construct(
        private readonly AdminService $adminService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     summary="Get all users (admin only)",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully"
     *     )
     * )
     */
    public function users(Request $request)
    {
        $result = $this->adminService->getUsers($request->query());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/deliveries",
     *     summary="Get all deliveries (admin only)",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Deliveries retrieved successfully"
     *     )
     * )
     */
    public function deliveries(Request $request)
    {
        $result = $this->adminService->getDeliveries($request->query());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/user/{userId}/status",
     *     summary="Update user status (admin only)",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="verified", type="boolean"),
     *             @OA\Property(property="accredited", type="boolean"),
     *             @OA\Property(property="subscribed", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User status updated successfully"
     *     )
     * )
     */
    public function updateUserStatus(Request $request, $userId)
    {
        try{
        $validator = Validator::make($request->all(), [
            'verified' => 'boolean',
            'accredited' => 'boolean',
            'subscribed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->adminService->updateUserStatus($userId, $request->all());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success(null, $result['message'], $result['httpCode']);
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
