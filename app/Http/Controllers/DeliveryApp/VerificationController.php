<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\VerificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerificationController extends BaseController
{
    public function __construct(
        private readonly VerificationService $verificationService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/verification/initiate",
     *     summary="Initiate verification process",
     *     tags={"Verification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type"},
     *             @OA\Property(property="type", type="string", enum={"email","phone","identity"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification initiated successfully"
     *     )
     * )
     */
    public function initiate(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:email,phone,identity',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->verificationService->initiate($request->user(), $request->type);

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);


         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/verification/verify",
     *     summary="Complete verification",
     *     tags={"Verification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"verification_id","code"},
     *             @OA\Property(property="verification_id", type="string"),
     *             @OA\Property(property="code", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification completed successfully"
     *     )
     * )
     */
    public function verify(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->verificationService->verify($request->user(), $request->all());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);


         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/verification/status",
     *     summary="Get verification status",
     *     tags={"Verification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Verification status retrieved successfully"
     *     )
     * )
     */
    public function status(Request $request)
    {
        try{
        $result = $this->verificationService->getStatus($request->user());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);


         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/verification/details",
     *     summary="Get verification service details",
     *     tags={"Service Details"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Verification details retrieved successfully"
     *     )
     * )
     */
    public function details()
    {
        try{
        $result = $this->verificationService->getDetails();
        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
