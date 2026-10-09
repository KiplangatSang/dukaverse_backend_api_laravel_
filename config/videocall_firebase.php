<?php

return [
    'credentials' => base_path(env('VIDEOCALL_FIREBASE_CREDENTIALS', 'firebase/ceroisoft_firebase_credentials.json')),
    'database_url' => env('VIDEOCALL_FIREBASE_DATABASE_URL', 'https://ceroisoft-e7c81.firebaseio.com'),
    'storage_bucket' => env('VIDEOCALL_FIREBASE_STORAGE_BUCKET', 'ceroisoft-e7c81.firebasestorage.app'),
    'messaging_sender_id' => env('VIDEOCALL_FIREBASE_MESSAGING_SENDER_ID', '524096297365'),
    'app_id' => env('VIDEOCALL_FIREBASE_APP_ID', '1:524096297365:web:1d1ce70395a2c7631c7e9c'),
    'project_id' => env('VIDEOCALL_FIREBASE_PROJECT_ID', 'ceroisoft-e7c81'),
];
