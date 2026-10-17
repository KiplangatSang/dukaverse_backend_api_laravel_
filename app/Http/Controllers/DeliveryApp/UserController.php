<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    public function __construct(
        private readonly UserService $userService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery/user/profile",
     *     summary="Get user profile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully"
     *     )
     * )
     */
    public function profile(Request $request)
    {
        try {
            $result = $this->userService->getProfile($request->user());

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (\Exception $e) {
            return $this->apiResource->error("An error occurred", 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/delivery/user/profile",
     *     summary="Update user profile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->apiResource->error("Validation Error", 422, $validator->errors());
            }

            $result = $this->userService->updateProfile($request->user(), $request->all());

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (\Exception $e) {
            return $this->apiResource->error("An error occurred", 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user/vehicle",
     *     summary="Update vehicle information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"make","model","year","license_plate","color","vin"},
     *             @OA\Property(property="make", type="string"),
     *             @OA\Property(property="model", type="string"),
     *             @OA\Property(property="year", type="integer"),
     *             @OA\Property(property="license_plate", type="string"),
     *             @OA\Property(property="color", type="string"),
     *             @OA\Property(property="vin", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vehicle information updated successfully"
     *     )
     * )
     */
    public function updateVehicle(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'make' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'license_plate' => 'required|string|max:20',
                'color' => 'required|string|max:50',
                'vin' => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return $this->apiResource->error("Validation Error", 422, $validator->errors());
            }

            $result = $this->userService->updateVehicle($request->user(), $request->all());

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success(null, $result['message'], $result['httpCode']);
        } catch (\Exception $e) {
            return $this->apiResource->error("An error occurred", 500);
        }
    }
}
