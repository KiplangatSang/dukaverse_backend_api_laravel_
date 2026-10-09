<?php
namespace App\Http\Controllers;
use App\Http\Resources\ApiResource;

use App\Models\AppNotification;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\StoreFileResource; // Added use statement
use App\Http\Resources\ResponseHelper; // Added use statement

/**
 * @OA\Tag(
 *     name="App Notifications",
 *     description="API Endpoints for managing app notifications with Firebase integration"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class AppNotificationController extends BaseController
{
    protected FirebaseService $firebaseService;


    public function __construct(
        FirebaseService $firebaseService,
        private readonly StoreFileResource $storeFileResource,
        protected ApiResource $apiResource,
    ) {

        $this->firebaseService = $firebaseService;
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/app-notifications",
     *     tags={"App Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all app notifications for authenticated user",
     *     description="Fetch all app notifications for the authenticated user with optional filtering",
     *     @OA\Parameter(
     *         name="read",
     *         in="query",
     *         description="Filter by read status (true/false)",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by notification type",
     *         @OA\Schema(type="string", enum={"info", "success", "warning", "error"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of notifications per page",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success, list of notifications",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="read", type="boolean"),
     *                 @OA\Property(property="read_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="unread_count", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AppNotification::where('user_id', Auth::id())
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc');

            if ($request->has('read')) {
                $query->where('read', $request->boolean('read'));
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            $notifications = $query->paginate($request->get('per_page', 15));

            $unreadCount = AppNotification::where('user_id', Auth::id())
                ->where('read', false)
                ->count();

            $response = [
                'data' => $notifications->items(),
                'meta' => [
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'unread_count' => $unreadCount,
                ]
            ];


            return $this->sendResponse($response, 'Notifications retrieved successfully');
        } catch (Exception $ex) {
            return $this->sendError("Server error", $ex->getMessage, 500);

        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/app-notifications",
     *     tags={"App Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new app notification",
     *     description="Create a new notification for the authenticated user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "message"},
     *             @OA\Property(property="title", type="string", example="New Message"),
     *             @OA\Property(property="message", type="string", example="You have received a new message"),
     *             @OA\Property(property="type", type="string", enum={"info", "success", "warning", "error"}, default="info"),
     *             @OA\Property(property="data", type="object", description="Additional notification data")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Notification created successfully")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Send to Firebase
        // Send to Firebase
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'in:info,success,warning,error',
                'data' => 'nullable|array',
            ]);

            $notification = AppNotification::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'] ?? 'info',
                'data' => $validated['data'] ?? null,
            ]);

            // Send to Firebase
            try {
                $this->firebaseService->sendAppNotification($notification);
                $notification->update(['sent_to_firebase' => true]);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                Log::error('Failed to send notification to Firebase: ' . $e->getMessage());
            }


            return $this->sendResponse($notification, 'Notification created successfully', 201);
        } catch (Exception $ex) {
            return $this->sendError("Server error", $ex->getMessage, 500);

        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/app-notifications/{id}",
     *     tags={"App Notifications"},
     * }
     * @OA\Post(
     *     path="/api/v1/app-notifications/mark-all-read",
     *     tags={"App Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Mark all notifications as read",
     *     description="Mark all unread notifications for the authenticated user as read",
     *     @OA\Response(response=200, description="All notifications marked as read")
     * )
     */
    public function markAllRead(): JsonResponse
    {
        try {
            $count = AppNotification::where('user_id', Auth::id())
                ->where('read', false)
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);


            return $this->sendResponse(['marked_count' => $count], 'All notifications marked as read');
        } catch (Exception $ex) {
            return $this->sendError("Server error", $ex->getMessage, 500);

        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/app-notifications/delete-read",
     *     tags={"App Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete all read notifications",
     *     description="Delete all read notifications for the authenticated user",
     *     @OA\Response(response=200, description="Read notifications deleted")
     * )
     */
    public function deleteRead(): JsonResponse
    {
        try {
            $count = AppNotification::where('user_id', Auth::id())
                ->where('read', true)
                ->delete();


            return $this->sendResponse(['deleted_count' => $count], 'Read notifications deleted successfully');
        } catch (Exception $ex) {
            return $this->sendError("Server error", $ex->getMessage, 500);

        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/app-notifications/stats",
     *     tags={"App Notifications"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get notification statistics",
     *     description="Get statistics about user's notifications",
     *     @OA\Response(
     *         response=200,
     *         description="Notification statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="unread", type="integer"),
     *             @OA\Property(property="read", type="integer"),
     *             @OA\Property(property="today", type="integer")
     *         )
     *     )
     * )
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total' => AppNotification::where('user_id', Auth::id())->count(),
                'unread' => AppNotification::where('user_id', Auth::id())->where('read', false)->count(),
                'read' => AppNotification::where('user_id', Auth::id())->where('read', true)->count(),
                'today' => AppNotification::where('user_id', Auth::id())->whereDate('created_at', today())->count(),
            ];


            return $this->sendResponse($stats, 'Notification statistics retrieved successfully');
        } catch (Exception $ex) {
            return $this->sendError("Server error", $ex->getMessage, 500);

        }
    }
}
