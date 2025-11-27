<?php
namespace App\Services;

use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private string $projectId;

    public function __construct()
    {
        $this->projectId = config('videocall_firebase.project_id');
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

            if (!$user || !$user->fcm_token) {
                Log::warning('User FCM token not found for notification', [
                    'user_id' => $notification->user_id,
                    'notification_id' => $notification->id
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
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function storeMessage(string $roomId, array $messageData): bool
{
    $accessToken = $this->getAccessToken();
    if (!$accessToken) {
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
        $timestamp = gmdate('Y-m-d\TH:i:s\Z', (int)$timestamp);
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
    $err = curl_error($ch);
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

// public function getMessages(string $roomId, int $limit = 50, ?string $lastTimestamp = null): array
// {
//     $accessToken = $this->getAccessToken();
//     if (!$accessToken) {
//         Log::error('Failed to get access token for getting messages');
//         return [];
//     }

//     $headers = [
//         "Authorization: Bearer $accessToken",
//         'Content-Type: application/json',
//     ];

//     $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents:runQuery";

//     $structuredQuery = [
//         'from' => [
//             ['collectionId' => 'messages']
//         ],
//         'orderBy' => [
//             ['field' => ['fieldPath' => 'timestamp'], 'direction' => 'DESCENDING']
//         ],
//         'limit' => $limit
//     ];

//     if ($lastTimestamp) {
//         // Make sure lastTimestamp is in RFC3339 format
//         if (is_numeric($lastTimestamp)) {
//             $lastTimestamp = gmdate('Y-m-d\TH:i:s\Z', (int)$lastTimestamp);
//         }
//         $structuredQuery['startAt'] = [
//             'values' => [
//                 ['timestampValue' => $lastTimestamp]
//             ],
//             'before' => false
//         ];
//     }

//     $payload = [
//         'parent' => "projects/{$this->projectId}/databases/(default)/documents/video_calls/{$roomId}",
//         'structuredQuery' => $structuredQuery
//     ];

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

//     $response = curl_exec($ch);
//     $err = curl_error($ch);
//     curl_close($ch);

//     if ($err) {
//         Log::error('Curl error in getMessages: ' . $err);
//         return [];
//     }

//     $result = json_decode($response, true);
//     if (!is_array($result)) {
//         Log::error('Invalid JSON response from Firestore: ' . $response);
//         return [];
//     }

//     $messages = [];
//     foreach ($result as $item) {
//         if (isset($item['document'])) {
//             $doc = $item['document'];
//             $data = [];
//             foreach ($doc['fields'] as $key => $value) {
//                 $data[$key] = $value[array_key_first($value)];
//             }
//             $messages[] = $data;
//         }
//     }

//     // Return oldest first
//     return array_reverse($messages);
// }


// public function getMessages(string $roomId, int $limit = 50, ?string $lastTimestamp = null): array
// {
//     $accessToken = $this->getAccessToken();
//     if (!$accessToken) {
//         Log::error('Failed to get access token for getting messages');
//         return [];
//     }

//     $headers = [
//         "Authorization: Bearer $accessToken",
//         'Content-Type: application/json',
//     ];

//     $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents:runQuery";

//     $structuredQuery = [
//         'structuredQuery' => [
//             'from' => [
//                 ['collectionId' => 'messages', 'allDescendants' => false]
//             ],
//             'orderBy' => [
//                 ['field' => ['fieldPath' => 'timestamp'], 'direction' => 'DESCENDING']
//             ],
//             'limit' => $limit,
//         ],
//     ];

//     if ($lastTimestamp) {
//         // Firestore expects timestampValue in ISO 8601
//         if (is_numeric($lastTimestamp)) {
//             $lastTimestamp = gmdate('Y-m-d\TH:i:s\Z', (int)$lastTimestamp);
//         }

//         $structuredQuery['structuredQuery']['startAt'] = [
//             'values' => [
//                 ['timestampValue' => $lastTimestamp]
//             ],
//             'before' => false
//         ];
//     }

//     // Specify the parent document (video_calls/{roomId})
//     $payload = [
//         'parent' => "projects/{$this->projectId}/databases/(default)/documents/video_calls/{$roomId}",
//         'structuredQuery' => $structuredQuery['structuredQuery']
//     ];

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

//     $response = curl_exec($ch);
//     $err = curl_error($ch);
//     curl_close($ch);

//     if ($err) {
//         Log::error('Curl error in getMessages: ' . $err);
//         return [];
//     }

//     $result = json_decode($response, true);
//     if (!is_array($result)) {
//         Log::error('Invalid JSON response from Firestore: ' . $response);
//         return [];
//     }

//     $messages = [];
//     foreach ($result as $item) {
//         if (isset($item['document'])) {
//             $doc = $item['document'];
//             $data = [];
//             foreach ($doc['fields'] as $key => $value) {
//                 $data[$key] = $value[array_key_first($value)];
//             }
//             $messages[] = $data;
//         }
//     }

//     // Reverse to ascending order (oldest first)
//     return array_reverse($messages);
// }


//     public function storeMessage(string $roomId, array $messageData)
//     {
//         $accessToken = $this->getAccessToken();
//         if (! $accessToken) {
//             Log::error('Failed to get access token for storing message');
//             return false;
//         }

//         $headers = [
//             "Authorization: Bearer $accessToken",
//             'Content-Type: application/json',
//         ];

//         $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/video_calls/{$roomId}/messages";

//         $timestamp = $messageData['timestamp'];

// // Convert Unix timestamp to ISO 8601 if needed
//         if (is_numeric($timestamp)) {
//             $timestamp = gmdate('Y-m-d\TH:i:s\Z', (int) $timestamp);
//         }
//         $body = [
//             "fields" => [
//                 "user_id"   => ["integerValue" => $messageData['user_id']],
//                 "user_name" => ["stringValue" => $messageData['user_name']],
//                 "message"   => ["stringValue" => $messageData['message']],
//                 "type"      => ["stringValue" => $messageData['type']],
//                 "timestamp" => ["timestampValue" => $timestamp],
//             ],
//         ];

//         $payload = json_encode($body);

//         $ch = curl_init();
//         curl_setopt($ch, CURLOPT_URL, $url);
//         curl_setopt($ch, CURLOPT_POST, true);
//         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//         $response = curl_exec($ch);
//         $err      = curl_error($ch);
//         curl_close($ch);

//         if ($err) {
//             Log::error('Curl error in storeMessage: ' . $err);
//             return false;
//         }

//         $result = json_decode($response, true);
//         if (isset($result['error'])) {
//             Log::error('Firestore error in storeMessage: ' . json_encode($result['error']));
//             return false;
//         }

//         return true;
//     }

//     public function getMessages(string $roomId, int $limit = 50, ?string $lastTimestamp = null): array
//     {
//         $accessToken = $this->getAccessToken();
//         if (! $accessToken) {
//             Log::error('Failed to get access token for getting messages');
//             return [];
//         }

//         $headers = [
//             "Authorization: Bearer $accessToken",
//             'Content-Type: application/json',
//         ];

//         // Use :runQuery on the subcollection path
//         $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/video_calls/{$roomId}/messages:runQuery";

//         $structuredQuery = [
//             'structuredQuery' => [
//                 'from'    => [
//                     ['collectionId' => 'messages'],
//                 ],
//                 'orderBy' => [
//                     ['field' => ['fieldPath' => 'timestamp'], 'direction' => 'DESCENDING'],
//                 ],
//                 'limit'   => $limit,
//             ],
//         ];

//         // Add startAt if lastTimestamp is provided
//         if ($lastTimestamp) {
//             // Ensure lastTimestamp is ISO 8601 format, e.g., 2025-11-12T11:00:00.000Z
//             $structuredQuery['structuredQuery']['startAt'] = [
//                 'values' => [
//                     ['timestampValue' => $lastTimestamp],
//                 ],
//                 'before' => false,
//             ];
//         }

//         $ch = curl_init();
//         curl_setopt($ch, CURLOPT_URL, $url);
//         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//         curl_setopt($ch, CURLOPT_POST, true);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($structuredQuery));
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

//         $response = curl_exec($ch);
//         $err      = curl_error($ch);
//         curl_close($ch);

//         if ($err) {
//             Log::error('Curl error in getMessages: ' . $err);
//             return [];
//         }

//         $result = json_decode($response, true);
//         if (! $result) {
//             Log::error('Invalid JSON response from Firestore: ' . $response);
//             return [];
//         }

//         $messages = [];
//         foreach ($result as $item) {
//             if (isset($item['document'])) {
//                 $doc  = $item['document'];
//                 $data = [];
//                 foreach ($doc['fields'] as $key => $value) {
//                     // Convert Firestore field types to PHP values
//                     $fieldType = array_key_first($value);
//                     switch ($fieldType) {
//                         case 'stringValue':
//                             $data[$key] = $value['stringValue'];
//                             break;
//                         case 'integerValue':
//                             $data[$key] = (int) $value['integerValue'];
//                             break;
//                         case 'doubleValue':
//                             $data[$key] = (float) $value['doubleValue'];
//                             break;
//                         case 'booleanValue':
//                             $data[$key] = (bool) $value['booleanValue'];
//                             break;
//                         case 'timestampValue':
//                             $data[$key] = $value['timestampValue'];
//                             break;
//                         default:
//                             $data[$key] = $value[$fieldType];
//                     }
//                 }
//                 $messages[] = $data;
//             }
//         }

//         // Reverse to ascending order
//         return array_reverse($messages);
//     }

//     public function getMessages(string $roomId, int $limit = 50, ?string $lastTimestamp = null): array
// {
//     $accessToken = $this->getAccessToken();
//     if (! $accessToken) {
//         Log::error('Failed to get access token for getting messages');
//         return [];
//     }

//     $headers = [
//         "Authorization: Bearer $accessToken",
//         'Content-Type: application/json',
//     ];

//     $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/video_calls/{$roomId}/messages:runQuery";

//     $structuredQuery = [
//         'structuredQuery' => [
//             'from' => [
//                 ['collectionId' => 'messages']
//             ],
//             'orderBy' => [
//                 ['field' => ['fieldPath' => 'timestamp'], 'direction' => 'DESCENDING']
//             ],
//             'limit' => $limit
//         ]
//     ];

//     if ($lastTimestamp) {
//         $structuredQuery['structuredQuery']['startAt'] = [
//             'values' => [
//                 ['timestampValue' => $lastTimestamp]
//             ],
//             'before' => false
//         ];
//     }

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($structuredQuery));
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

//     $response = curl_exec($ch);
//     $err = curl_error($ch);
//     curl_close($ch);

//     if ($err) {
//         Log::error('Curl error in getMessages: ' . $err);
//         return [];
//     }

//     $result = json_decode($response, true);
//     if (! $result) {
//         Log::error('Invalid JSON response from Firestore: ' . $response);
//         return [];
//     }

//     $messages = [];
//     foreach ($result as $item) {
//         if (isset($item['document'])) {
//             $doc = $item['document'];
//             $data = [];
//             foreach ($doc['fields'] as $key => $value) {
//                 $data[$key] = $value[array_key_first($value)];
//             }
//             $messages[] = $data;
//         }
//     }

//     return array_reverse($messages); // ascending order
// }

    public function getMessages(string $roomId, int $limit = 50, ?string $lastTimestamp = null): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('Failed to get access token for getting messages');
            return [];
        }

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json'
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
        $err = curl_error($ch);
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
}
