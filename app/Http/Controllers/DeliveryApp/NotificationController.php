<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends BaseController
{
    public function __construct(
        private readonly NotificationService $notificationService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery-app/notifications",
     *     summary="Get user notifications",
     *     tags={"Notification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try{
        $result = $this->notificationService->getNotifications($request->user());

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
     * @OA\Put(
     *     path="/api/v1/delivery-app/notifications/{notificationId}/read",
     *     summary="Mark notification as read",
     *     tags={"Notification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="notificationId",
     *         in="path",
     *         required=true,
     *         description="Notification ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read"
     *     )
     * )
     */
    public function markAsRead(Request $request, $notificationId)
    {

    try{
        $result = $this->notificationService->markAsRead($request->user(), $notificationId);

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
