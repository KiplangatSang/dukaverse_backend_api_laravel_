# Firebase Configuration for Video Call Functionality

This document outlines the steps required to configure Firebase services for the video call functionality in the Dukaverse Backend.

## Prerequisites

- Google Cloud Platform account
- Firebase project
- Laravel application with Firebase SDK installed

## Step 1: Create a Firebase Project

1. Go to the [Firebase Console](https://console.firebase.google.com/)
2. Click "Create a project" or select an existing project
3. Enter your project name (e.g., "dukaverse-backend")
4. Enable Google Analytics if desired
5. Click "Create project"

## Step 2: Enable Firestore Database

1. In your Firebase project console, go to "Firestore Database"
2. Click "Create database"
3. Choose "Start in test mode" for development (you can change security rules later)
4. Select a location for your database (choose the region closest to your users)
5. Click "Done"

## Step 3: Enable Cloud Messaging (FCM)

1. In your Firebase project console, go to "Cloud Messaging"
2. If not already enabled, Firebase will prompt you to upgrade your project
3. Follow the prompts to enable Cloud Messaging

## Step 4: Generate Service Account Key

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Select your Firebase project
3. Navigate to "IAM & Admin" > "Service accounts"
4. Click "Create service account"
5. Enter service account details:
   - Name: `firebase-service-account`
   - Description: `Service account for Firebase operations`
6. Grant the following roles:
   - `Firebase Admin SDK Administrator Service Agent`
   - `Cloud Datastore User` (for Firestore)
   - `Firebase Cloud Messaging Admin` (for FCM)
7. Click "Create key" and select JSON format
8. Download the JSON key file
9. Save this file as `firebase_credentials.json` in your Laravel project's `firebase/` directory

## Step 5: Configure Firestore Security Rules

1. In Firebase Console, go to "Firestore Database" > "Rules"
2. Update the rules to allow authenticated access for video call operations:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    // Allow read/write access to video call collections for authenticated users
    match /video_calls/{roomId} {
      allow read, write: if request.auth != null;
    }

    match /video_calls/{roomId}/messages/{messageId} {
      allow read, write: if request.auth != null &&
        exists(/databases/$(database)/documents/video_calls/$(roomId)/participants/$(request.auth.uid));
    }

    match /video_calls/{roomId}/participants/{participantId} {
      allow read, write: if request.auth != null;
    }
  }
}
```

## Step 6: Configure Firebase in Laravel

1. Ensure your `config/firebase.php` file contains:

```php
<?php

return [
    'credentials' => storage_path('firebase/firebase_credentials.json'),
    'database_url' => 'https://your-project-id.firebaseio.com',
];
```

Replace `your-project-id` with your actual Firebase project ID.

2. Place the downloaded `firebase_credentials.json` file in `storage/firebase/firebase_credentials.json`

## Step 7: Test Firebase Connection

1. Run your Laravel application
2. Check the logs for any Firebase initialization errors
3. Test a video call creation to ensure Firestore connectivity
4. Verify that messages are being stored and retrieved

## Step 8: Configure FCM for Push Notifications (Optional)

If you want to send push notifications to users:

1. In Firebase Console, go to "Project settings" > "Cloud Messaging"
2. Note down the "Server key" and "Sender ID"
3. Configure these in your Laravel config if needed for additional FCM operations

## Troubleshooting

### Common Issues:

1. **Firebase credentials not found**: Ensure the JSON file is in the correct path and has proper permissions
2. **Firestore permission denied**: Check your security rules
3. **Timeout errors**: Verify your network connectivity and Firebase project settings
4. **Service account permissions**: Ensure the service account has the required roles

### Debug Commands:

```bash
# Test Firebase connection in Laravel tinker
php artisan tinker
>>> $firebase = app('firebase.firestore');
>>> $firebase->database()->collection('test')->documents();
```

## Security Considerations

- **Production Rules**: Change Firestore rules from "test mode" to production rules that properly authenticate users
- **Service Account Key**: Never commit the `firebase_credentials.json` file to version control
- **Environment Variables**: Consider using environment variables for sensitive Firebase configuration

## Additional Resources

- [Firebase Documentation](https://firebase.google.com/docs)
- [Laravel Firebase SDK](https://github.com/kreait/laravel-firebase)
- [Firestore Security Rules](https://firebase.google.com/docs/firestore/security/get-started)

---

**Note**: This configuration enables the backend video call functionality. Frontend integration will require additional setup for WebRTC and real-time communication.
