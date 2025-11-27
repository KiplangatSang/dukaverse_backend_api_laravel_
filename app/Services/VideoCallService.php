<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\VideoCall;
use App\Models\VideoCallParticipant;
use App\Models\VideoCallPermission;
use function Laravel\Prompts\info;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class VideoCallService extends BaseService
{
    protected $firestore;
    protected $messaging;

    public function __construct(
        StoreFileResource $storeFileResource,
        private readonly ResponseHelper $responseHelper,
        private readonly FirebaseService $firebaseService
    ) {
        putenv('GOOGLE_CLOUD_DISABLE_GRPC=true');
        putenv('GOOGLE_CLOUD_PHP_HTTP_TIMEOUT=3'); // 3 second request timeout
        putenv('GOOGLE_CLOUD_DEBUG_LOG_LEVEL=debug');
        Log::info('Firestore transport forced to REST mode');
        putenv('GOOGLE_CLOUD_DISABLE_GRPC=true');
        putenv('GOOGLE_CLOUD_PHP_HTTP_TIMEOUT=3');
        putenv('GOOGLE_CLOUD_PROJECT='. config('videocall_firebase.project_id'));

        parent::__construct($storeFileResource, $responseHelper);

        try {
            // ✅ Initialize Firebase with Firestore and Messaging only
            $firebase = (new Factory)
                ->withServiceAccount(config('videocall_firebase.credentials'));

            // ✅ Firestore (default instance)
            $this->firestore = $firebase->createFirestore();
            Log::info('Firestore project ID', ['project' => $this->firestore->database()::DEFAULT_DATABASE]);

            // ✅ Cloud Messaging
            $this->messaging = $firebase->createMessaging();

            Log::info('Firebase initialized successfully for Firestore + Messaging');
        } catch (\Throwable $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
            $this->firestore = null;
            $this->messaging = null;
        }
    }

    public function createRoom(array $data)
    {
        try {
            $roomId = $this->generateRoomId();
            $user   = $this->user();

            $videoCall = VideoCall::create([
                'room_id'      => $roomId,
                'initiator_id' => $user->id,
                'participants' => [$user->id],
                'status'       => 'waiting',
                'settings'     => $data['settings'] ?? [],
            ]);

            // Add initiator as participant
            VideoCallParticipant::create([
                'video_call_id' => $videoCall->id,
                'user_id'       => $user->id,
                'role'          => 'host',
                'joined_at'     => now(),
            ]);

            // Initialize Firestore chat collection if needed (now handled by FirebaseService)

            return $this->responseHelper->respond([
                'room_id'    => $roomId,
                'video_call' => $videoCall,
            ], 'Video call room created successfully');
        } catch (\Exception $e) {
            Log::error('Error in createRoom: ' . $e->getMessage());
            return $this->responseHelper->error('Failed to create video call room', 500);
        }
    }

    public function joinRoom(string $roomId)
    {
        try {
            $videoCall = VideoCall::where('room_id', $roomId)->first();

            if (! $videoCall) {
                return $this->responseHelper->error('Video call room not found', 404);
            }

            if ($videoCall->isEnded()) {
                return $this->responseHelper->error('Video call has ended', 400);
            }

            $user = $this->user();

            $existingParticipant = $videoCall->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if ($existingParticipant) {
                return $this->responseHelper->error('Already joined this call', 400);
            }

            $participant = VideoCallParticipant::create([
                'video_call_id' => $videoCall->id,
                'user_id'       => $user->id,
                'role'          => 'participant',
                'joined_at'     => now(),
            ]);

            if ($videoCall->status === 'waiting') {
                $videoCall->update([
                    'status'     => 'active',
                    'started_at' => now(),
                ]);
            }

            $this->notifyParticipants($videoCall, $user, 'joined');

            return $this->responseHelper->respond([
                'participant' => $participant,
                'video_call'  => $videoCall->load('participants'),
            ], 'Joined video call successfully');
        } catch (\Exception $e) {
            Log::error('Error in joinRoom: ' . $e->getMessage());
            return $this->responseHelper->error('Failed to join video call', 500);
        }
    }

    public function leaveRoom(string $roomId)
    {
        try {
            $videoCall = VideoCall::where('room_id', $roomId)->first();
            $user      = $this->user();

            if (! $videoCall) {
                return $this->responseHelper->error('Video call room not found', 404);
            }

            $participant = $videoCall->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if (! $participant) {
                return $this->responseHelper->error('Not a participant in this call', 400);
            }

            $participant->update(['left_at' => now()]);

            $this->notifyParticipants($videoCall, $user, 'left');

            $activeParticipants = $videoCall->participants()
                ->whereNull('left_at')
                ->where('user_id', '!=', $user->id)
                ->get();

            if ($activeParticipants->isEmpty()) {
                $videoCall->update([
                    'status'   => 'ended',
                    'ended_at' => now(),
                ]);
            }

            return $this->responseHelper->respond([], 'Left video call successfully');
        } catch (\Exception $e) {
            Log::error('Error in leaveRoom: ' . $e->getMessage());
            return $this->responseHelper->error('Failed to leave video call', 500);
        }
    }

    public function sendMessage(string $roomId, string $message, string $type = 'text')
    {
        try {
            $videoCall = VideoCall::where('room_id', $roomId)->first();
            $user      = $this->user();

            if (! $videoCall) {
                return $this->responseHelper->error('Video call room not found', 404);
            }

            $participant = $videoCall->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if (! $participant) {
                return $this->responseHelper->error('Not a participant in this call', 403);
            }

            $messageData = [
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'message'   => $message,
                'type'      => $type,
                'timestamp' => now()->toIso8601String(),
            ];

            // Store message using FirebaseService
            $stored = $this->firebaseService->storeMessage($roomId, $messageData);
            if (!$stored) {
                Log::warning('Failed to store message in Firestore');
                return $this->responseHelper->error('Failed to store message', 500);
            }

            // Send FCM notification to other participants
            $this->notifyParticipants($videoCall, $user, 'sent a message', ['message' => $message]);

            return $this->responseHelper->respond($messageData, 'Message sent successfully');
        } catch (\Throwable $e) {
            Log::error('sendMessage exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->responseHelper->error('Failed to send message', 500);
        }
    }

    // public function sendMessage(string $roomId, string $message, string $type = 'text')
    // {
    //     try {
    //         $videoCall = VideoCall::where('room_id', $roomId)->first();
    //         $user      = $this->user();

    //         if (! $videoCall) {
    //             return $this->responseHelper->error('Video call room not found', 404);
    //         }

    //         $participant = $videoCall->participants()
    //             ->where('user_id', $user->id)
    //             ->whereNull('left_at')
    //             ->first();

    //         if (! $participant) {
    //             return $this->responseHelper->error('Not a participant in this call', 403);
    //         }

    //         $messageData = [
    //             'user_id'   => $user->id,
    //             'user_name' => $user->name,
    //             'message'   => $message,
    //             'type'      => $type,
    //             'timestamp' => now()->toIso8601String(),
    //         ];

    //         if (! $this->firestore) {
    //             Log::warning('Firestore unavailable, skipping message storage');
    //             return $this->responseHelper->respond($messageData, 'Message sent (not stored)');
    //         }

    //         $db = $this->firestore->database();

    //         $videoCallDoc = $db->collection('video_calls')->document($roomId);
    //         $messagesCol  = $videoCallDoc->collection('messages');

    //         $messagesCol->add($messageData);

    //         Log::info("Message written to Firestore", ['room_id' => $roomId]);

    //         return $this->responseHelper->respond($messageData, 'Message sent successfully');
    //     } catch (FirebaseException $e) {
    //         Log::error('Firebase API error in sendMessage: ' . $e->getMessage());
    //         return $this->responseHelper->error('Message service temporarily unavailable', 503);
    //     } catch (\Exception $e) {
    //         Log::error('Error in sendMessage: ' . $e->getMessage());
    //         return $this->responseHelper->error('Failed to send message', 500);
    //     }
    // }

    public function getMessages(string $roomId, int $limit = 50, ?string $lastTimestamp = null)
    {
        try {
            $videoCall = VideoCall::where('room_id', $roomId)->first();

            if (! $videoCall) {
                return $this->responseHelper->error('Video call room not found', 404);
            }

            $user = $this->user();

            $participant = $videoCall->participants()
                ->where('user_id', $user->id)
                ->first();

            if (! $participant) {
                return $this->responseHelper->error('Not authorized to view messages', 403);
            }

            // Fetch messages using FirebaseService
            $messages = $this->firebaseService->getMessages($roomId, $limit, $lastTimestamp);

            return $this->responseHelper->respond($messages, 'Messages retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error in getMessages: ' . $e->getMessage());
            return $this->responseHelper->error('Failed to retrieve messages', 500);
        }
    }

    protected function generateRoomId(): string
    {
        do {
            $roomId = Str::random(10);
        } while (VideoCall::where('room_id', $roomId)->exists());

        return $roomId;
    }



    protected function notifyParticipants(VideoCall $videoCall, $sender, string $action, array $data = [])
    {
        try {
            $participants = $videoCall->getActiveParticipants()
                ->where('user_id', '!=', $sender->id);

            foreach ($participants as $participant) {
                if (! $participant->user || ! $participant->user->fcm_token) {
                    continue;
                }

                $title = "Video Call Update";
                $body = "{$sender->name} {$action}";

                $this->firebaseService->sendNotification($participant->user->fcm_token, $title, $body);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification: ' . $e->getMessage());
        }
    }

    protected function getUserPermission(int $userId): ?VideoCallPermission
    {
        return VideoCallPermission::where('user_id', $userId)->first();
    }
}
