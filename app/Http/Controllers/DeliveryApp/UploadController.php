<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\UploadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UploadController extends BaseController
{
    public function __construct(
        private readonly UploadService $uploadService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/upload/document",
     *     summary="Upload verification documents",
     *     tags={"Upload"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     @OA\Property(property="file", type="string", format="binary"),
     *                     @OA\Property(property="type", type="string", enum={"id_card","license","insurance"})
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document uploaded successfully"
     *     )
     * )
     */
    public function document(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'type' => 'required|string|in:id_card,license,insurance',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->uploadService->uploadDocument($request->user(), $request->file('file'), $request->type);

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
     *     path="/api/v1/upload/vehicle-photo",
     *     summary="Upload vehicle photos",
     *     tags={"Upload"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     @OA\Property(property="file", type="string", format="binary"),
     *                     @OA\Property(property="type", type="string", enum={"front","side","back"})
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo uploaded successfully"
     *     )
     * )
     */
    public function vehiclePhoto(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png|max:5120', // 5MB max
            'type' => 'required|string|in:front,side,back',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->uploadService->uploadVehiclePhoto($request->user(), $request->file('file'), $request->type);

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
