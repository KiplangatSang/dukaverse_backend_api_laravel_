<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\VideoCallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Video Calls",
 *     description="Video call management endpoints"
 * )
 */
class VideoCallController extends BaseController
{
    public function __construct(
        private readonly VideoCallService $videoCallService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/video-calls",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new video call room",
     *     description="Creates a new video call room and returns the room ID",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="settings", type="object",
     *                 @OA\Property(property="recording", type="boolean", example=false),
     *                 @OA\Property(property="screen_share", type="boolean", example=true),
     *                 @OA\Property(property="max_participants", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Video call room created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="room_id", type="string", example="ABC123DEF4"),
     *                 @OA\Property(property="video_call", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function createRoom(Request $request): JsonResponse
    {
        $result = $this->videoCallService->createRoom($request->all());

        if (isset($result['error'])) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/video-calls/{roomId}/join",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth":{}}},
     *     summary="Join a video call room",
     *     description="Joins an existing video call room as a participant",
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Joined successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Joined video call successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Room not found"),
     *     @OA\Response(response=400, description="Already joined or call ended")
     * )
     */
    public function joinRoom(string $roomId): JsonResponse
    {
        $result = $this->videoCallService->joinRoom($roomId);

        if (isset($result['error'])) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/video-calls/{roomId}/leave",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth":{}}},
     *     summary="Leave a video call room",
     *     description="Leaves the current video call room",
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Left successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Left video call successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Room not found"),
     *     @OA\Response(response=400, description="Not a participant")
     * )
     */
    public function leaveRoom(string $roomId): JsonResponse
    {
        $result = $this->videoCallService->leaveRoom($roomId);

        if (isset($result['error'])) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/video-calls/{roomId}",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get video call details",
     *     description="Retrieves details of a specific video call room",
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Call details retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Room not found")
     * )
     */
    public function getRoom(string $roomId): JsonResponse
    {
        $videoCall = \App\Models\VideoCall::where('room_id', $roomId)
            ->with(['participants.user', 'initiator'])
            ->first()->toArray();

        if (!$videoCall) {
            return $this->apiResource->error('Video call room not found', 404);
        }

        return $this->apiResource->success($videoCall, 'Video call details retrieved successfully', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/video-calls/{roomId}/participants",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get room participants",
     *     description="Retrieves list of participants in a video call room",
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participants retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getParticipants(string $roomId): JsonResponse
    {
        $videoCall = \App\Models\VideoCall::where('room_id', $roomId)->first();

        if (!$videoCall) {
            return $this->apiResource->error('Video call room not found', 404);
        }

        $participants = $videoCall->participants()->with('user')->get();
        $participants = $participants->toArray();

        return $this->apiResource->success($participants, 'Participants retrieved successfully', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/video-calls/{roomId}/messages",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth":{}}},
     *     summary="Send a chat message",
     *     description="Sends a message in the video call chat",
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Hello everyone!"),
     *             @OA\Property(property="type", type="string", enum={"text", "file"}, example="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Message sent successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Not authorized to send messages")
     * )
     */
    public function sendMessage(Request $request, string $roomId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string',
                'type' => 'in:text,file'
            ]);

            if ($validator->fails()) {
                return $this->apiResource->error($validator->errors()->first(), 422);
            }
            $result = $this->videoCallService->sendMessage(
                $roomId,
                $request->message,
                $request->type ?? 'text'
            );

            if (isset($result['error'])) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResource->error($e->getMessage(), 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/video-calls/{roomId}/messages",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get chat messages",
     *     description="Retrieves chat messages for a video call room",
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getMessages(string $roomId): JsonResponse
    {
        $result = $this->videoCallService->getMessages($roomId);

        if (isset($result['error'])) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
    }
}
