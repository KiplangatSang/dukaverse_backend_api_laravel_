<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\VideoCall;
use App\Models\VideoCallParticipant;
use App\Models\VideoCallPermission;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class SendVideoCallNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */


    private $messaging;

    public function __construct(private VideoCall $videoCall, private $sender, private string $action, private array $data = [])
    {
        //
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->messaging = $firebase->createMessaging();
    }

    /**
     * Execute the job.
     */
    public function handle($videoCall, $sender, $action, $data): void
    {
        //
        $participants = $videoCall->getActiveParticipants()
            ->where('user_id', '!=', $sender->id);
        foreach ($participants as $participant) {
            if (!$participant->user || !$participant->user->fcm_token) {
                continue;
            }
            $message = CloudMessage::withTarget('token', $participant->user->fcm_token)
                ->withNotification(Notification::create(
                    "Video Call Update",
                    "{$sender->name} {$action} the call"
                ))
                ->withData([
                    'room_id' => $videoCall->room_id,
                    'action' => $action,
                    'sender_id' => $sender->id,
                    'data' => json_encode($data)
                ]);
            try {
                $this->messaging->send($message);
            } catch (\Exception $e) {
                // Log notification failure
                Log::error('Failed to send FCM notification: ' . $e->getMessage());
            }
        }
    }
}
