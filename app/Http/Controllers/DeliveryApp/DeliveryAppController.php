<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\DeliveryAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryAppController extends BaseController
{
    public function __construct(
        private readonly DeliveryAppService $deliveryAppService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/deliveries/available",
     *     summary="Get available delivery jobs",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         description="Latitude",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="lng",
     *         in="query",
     *         description="Longitude",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Search radius in km",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available jobs retrieved successfully"
     *     )
     * )
     */
    public function available(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->deliveryAppService->getAvailableJobs($request->user(), $request->query());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/deliveries/{jobId}/bid",
     *     summary="Place a bid on a delivery job",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="jobId",
     *         in="path",
     *         required=true,
     *         description="Job ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bid_amount","estimated_time"},
     *             @OA\Property(property="bid_amount", type="number", minimum=0),
     *             @OA\Property(property="estimated_time", type="integer", minimum=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bid placed successfully"
     *     )
     * )
     */
    public function bid(Request $request, $jobId)
    {
        $validator = Validator::make($request->all(), [
            'bid_amount' => 'required|numeric|min:0',
            'estimated_time' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->deliveryAppService->placeBid($request->user(), $jobId, $request->all());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/deliveries/my-bids",
     *     summary="Get user's active bids",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Active bids retrieved successfully"
     *     )
     * )
     */
    public function myBids(Request $request)
    {
        $result = $this->deliveryAppService->getMyBids($request->user());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/deliveries/{jobId}/accept",
     *     summary="Accept a delivery job",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="jobId",
     *         in="path",
     *         required=true,
     *         description="Job ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job accepted successfully"
     *     )
     * )
     */
    public function accept(Request $request, $jobId)
    {
        $result = $this->deliveryAppService->acceptJob($request->user(), $jobId);

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success(null, $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/deliveries/{jobId}/status",
     *     summary="Update delivery status",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="jobId",
     *         in="path",
     *         required=true,
     *         description="Job ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"picked_up","in_transit","delivered"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully"
     *     )
     * )
     */
    public function updateStatus(Request $request, $jobId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:picked_up,in_transit,delivered',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->deliveryAppService->updateStatus($request->user(), $jobId, $request->status);

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success(null, $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/deliveries/history",
     *     summary="Get delivery history",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Delivery history retrieved successfully"
     *     )
     * )
     */
    public function history(Request $request)
    {
        $result = $this->deliveryAppService->getHistory($request->user());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }
}
