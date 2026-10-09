<?php

return [
    'credentials' => base_path(env('FIREBASE_CREDENTIALS', 'firebase/firebase_credentials.json')),
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://dukaverse-e4f47-default-rtdb.firebaseio.com'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', 'dukaverse-e4f47.appspot.com'),
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://dukaverse-e4f47-default-rtdb.firebaseio.com'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', 'YOUR_MESSAGING_SENDER_ID'),
    'app_id' => env('FIREBASE_APP_ID', 'YOUR_APP_ID'),
    'project_id' => env('FIREBASE_PROJECT_ID', 'dukaverse-e4f47'),
];
