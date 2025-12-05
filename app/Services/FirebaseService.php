<?php
namespace App\Services;

use App\Helpers\Accounts\Account;
use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\User;
use Exception;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FirebaseService extends BaseService
{
    private string $projectId;

    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
         $this->projectId = config('videocall_firebase.project_id');
        parent::__construct($storeFileResource, $responseHelper);
    }


    

    public function updateFCMToken(int $userId, string $fcmToken): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->fcm_token = $fcmToken;
            $user->save();
        }
    }

    public function getAccessToken(): ?string
    {
        $credentialsFilePath = config('videocall_firebase.credentials');
        $client              = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope([
            'https://www.googleapis.com/auth/firebase.messaging',
            'https://www.googleapis.com/auth/datastore',
            'https://www.googleapis.com/auth/cloud-platform',
        ]);
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        return $token['access_token'] ?? null;
    }

    public function sendNotification(string $fcm, string $title, string $description)
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return;
        }
        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        $data = [
            "message" => [
                "token"        => $fcm,
                "notification" => [
                    "title" => $title,
                    "body"  => $description,
                ],
            ],
        ];
        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, false); // Rem: set to false after test
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);
    }

    public function storeNotification(int $userId, string $title, string $message, string $profilePhotoUrl = null)
    {
        $accessToken = $this->getAccessToken();
        $headers     = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];
        $fetchUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/Notifications/{$userId}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fetchUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        $result = json_decode($response);

        if ($result->error->code == 404) {
            $subCollectionUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/Notifications/{$userId}/in-app";

            $body = [
                "fields" => [
                    "userId"          => ["integerValue" => $userId],
                    "title"           => ["stringValue" => $title],
                    "message"         => ["stringValue" => $message],
                    "readStatus"      => ["booleanValue" => false],
                    "profilePhotoUrl" => ["stringValue" => $profilePhotoUrl],
                    "createdAt"       => ["timestampValue" => now()],
                ],
            ];

            $payload = json_encode($body);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $subCollectionUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_VERBOSE, false); // Rem: set to false after test
            $response = curl_exec($ch);
            $err      = curl_error($ch);
            curl_close($ch);
        }
    }

    /**
     * Send notification using AppNotification model
     */
    public function sendAppNotification($notification): bool
    {
        try {
            $user = $notification->user;

            if (! $user || ! $user->fcm_token) {
                Log::warning('User FCM token not found for notification', [
                    'user_id'         => $notification->user_id,
                    'notification_id' => $notification->id,
                ]);
                return false;
            }

            // Send push notification via FCM
            $this->sendNotification($user->fcm_token, $notification->title, $notification->message);

            // Store in Firebase Firestore
            $this->storeNotification(
                $notification->user_id,
                $notification->title,
                $notification->message,
                $user->profile_photo_url ?? null
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification via Firebase', [
                'notification_id' => $notification->id,
                'error'           => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function storeMessage(string $roomId, array $messageData): bool
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            Log::error('Failed to get access token for storing message');
            return false;
        }

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/video_calls/{$roomId}/messages";

        // Ensure timestamp is in RFC 3339 / ISO 8601 format
        $timestamp = $messageData['timestamp'];
        if (is_numeric($timestamp)) {
            $timestamp = gmdate('Y-m-d\TH:i:s\Z', (int) $timestamp);
        }

        $body = [
            "fields" => [
                "user_id"   => ["integerValue" => $messageData['user_id']],
                "user_name" => ["stringValue" => $messageData['user_name']],
                "message"   => ["stringValue" => $messageData['message']],
                "type"      => ["stringValue" => $messageData['type']],
                "timestamp" => ["timestampValue" => $timestamp],
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Curl error in storeMessage: ' . $err);
            return false;
        }

        $result = json_decode($response, true);
        if (isset($result['error'])) {
            Log::error('Firestore error in storeMessage: ' . json_encode($result['error']));
            return false;
        }

        return true;
    }

    public function getMessages(string $roomId, int $limit = 50, ?string $lastTimestamp = null): array
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            Log::error('Failed to get access token for getting messages');
            return [];
        }

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/video_calls/{$roomId}/messages";

        $queryParams = [
            'orderBy' => 'timestamp desc',
            // 'limit' => $limit
        ];

        if ($lastTimestamp) {
            $queryParams['startAfter'] = json_encode(['timestamp' => $lastTimestamp]);
        }

        $url .= '?' . http_build_query($queryParams);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Curl error in getMessages: ' . $err);
            return [];
        }

        $result = json_decode($response, true);
        if (isset($result['error'])) {
            Log::error('Firestore error in getMessages: ' . json_encode($result['error']));
            return [];
        }

        $messages = [];
        if (isset($result['documents'])) {
            foreach ($result['documents'] as $doc) {
                $data = [];
                foreach ($doc['fields'] as $key => $value) {
                    $data[$key] = $value[array_key_first($value)];
                }
                $messages[] = $data;
            }
        }

        // Reverse to ascending order
        $messages = array_reverse($messages);

        return $messages;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * allow write: if "auth.token.email === 'kiplangatsang425@gmail.com'";
     *
     */

    public function saveFile($folder, $file)
    {
        # code...

        try {
            $user = $this->user();

            $fileNameToStore = "";

            if (! $this->account) {
                 $fileNameToStore = $this->store($user, $folder, $file);
            } else {
                 $fileNameToStore = $this->store($user, $folder, $file);
            }

            return $fileNameToStore;
        } catch (Exception $e) {
            info($e->getMessage());
            return false;
        }
    }
    public function store($user, $folder, $file)
    {
        //
        try {
            $storage       = app('firebase.storage'); // This is an instance of Google\Cloud\Storage\StorageClient from kreait/firebase-php library
            $defaultBucket = $storage->getBucket();
            $image         = $file;
            $name          = (string) Str::uuid() . "." . $image->getClientOriginalExtension(); // use Illuminate\Support\Str;

            $pathName = $image->getPathName();

            $package  = $this->firebaseRepositories($this->account, $user);
            $filename = $package . $folder . "/" . $name;
            // $file = fopen($pathName, 'r');
            $file   = fopen($pathName, 'r');
            $object = $defaultBucket->upload($file, [
                'name'          => $filename,
                'predefinedAcl' => 'publicRead',
            ]);
            $image_url = 'https://storage.googleapis.com/' . env('FIREBASE_PROJECT_ID') . '.appspot.com/' . $filename;
            //dd($file);

            //https://storage.googleapis.com/dukaverse-e4f47.appspot.com/1/bf8af7e2-275d-4327-93a5-144fe2f42e24
            return $image_url;
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }
    }

    public function firebaseRepositories($account = null, User $user)
    {
        # code...
        $package = "";
        if ($account) {
            $package = "client/accounts/" . $account->name . $account->id . "/";
        } else {
            $package = "client/user/" . $user->id . "/";
        }
        return $package;
    }

}
