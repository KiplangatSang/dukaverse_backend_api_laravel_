<?php
namespace App\Http\Controllers;

use App\Helpers\NotificationMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="API Endpoints for managing user and retail notifications"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class NotificationController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/notifications",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all notifications",
     *     description="Fetch all notifications for the authenticated user and retail account",
     *     @OA\Response(
     *         response=200,
     *         description="Success, list of notifications",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user_notifications",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="New Sale!"),
     *                     @OA\Property(property="message", type="string", example="Your product has a 20% discount."),
     *                     @OA\Property(property="read", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T10:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="retail_notifications",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="title", type="string", example="Inventory Low"),
     *                     @OA\Property(property="message", type="string", example="Stock is running low on product X."),
     *                     @OA\Property(property="read", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-24T15:30:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $message['notificationMessage'] = new NotificationMessage("hello", null, null);
        $user_notifications             = $this->user()->notifications()->with('notifiable')->get();
        $retail_notifications           = $this->retail()->notifications()->get();

        $notifications['user_notifications']   = $user_notifications;
        $notifications['retail_notifications'] = $retail_notifications;
        return $this->sendResponse($notifications, 'success, Notifications');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/{id}",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get a single notification",
     *     description="Fetch a notification by its ID for the authenticated user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success, notification found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="New Sale!"),
     *             @OA\Property(property="message", type="string", example="Your product has a 20% discount."),
     *             @OA\Property(property="read", type="boolean", example=false),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found"
     *     )
     * )
     */

    public function show(string $id)
    {
        $notification = $this->user()->notifications()->where('id', $id)->first();
        return $this->sendResponse($notification, 'success, Notifications');
    }

    public function edit(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/v1/notifications/{id}",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update a notification",
     *     description="Mark a notification as read or update its properties",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="mark_as_read", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", description="Optional updated notification data")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success, notification updated"),
     *     @OA\Response(response=400, description="Error, notification not updated")
     * )
     */
    public function update(Request $request, string $id)
    {
        $notification = DatabaseNotification::where('id', $id)->first();

        if ($request->mark_as_read) {
            try {
                $result = $notification->markAsRead();
            } catch (Exception $e) {
                info($e->getMessage());
                return $this->sendError('Error, Notifications not marked as read');
            }
        } else {
            $result = $notification->update($request->all());
            if (! $result) {
                return $this->sendError($notification, 'Error, Notifications not updated');
            }
        }

        $notification = DatabaseNotification::where('id', $id)->first();
        return $this->sendResponse($notification, 'success, Notification updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notifications/{id}",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a notification",
     *     description="Delete a notification by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Success, notification deleted"),
     *     @OA\Response(response=404, description="Notification not found")
     * )
     */
    public function destroy(string $id)
    {
        //
    }
}
