# User Verification System Documentation

## Overview
The Dukaverse Backend provides a comprehensive user verification system supporting both email and phone number verification methods. This system ensures user authenticity and provides multiple verification options for better user experience.

## Features
- Email verification via clickable links
- Phone verification via SMS OTP
- Resend verification capabilities
- Swagger API documentation
- Frontend integration support

## Email Verification

### How It Works
1. User registers and receives an email with both a verification link and a 6-character verification code
2. User can verify their email using either method:
   - **Link Method**: Click the link, which redirects to the frontend, then frontend calls API
   - **Code Method**: Enter the code directly in the frontend form
3. User account is marked as email verified

### API Endpoints

#### Send/Resend Email Verification
```http
POST /api/v1/email/resend
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Verification email has been sent!",
  "data": {
    "user": {...},
    "message": "Verification email has been sent!"
  }
}
```

#### Verify Email (Link Method)
```http
GET /api/v1/email/verify/{id}/{hash}
```

**Example Link:**
```
http://localhost:9000/email/verify/123/abc123def456
```

**Success Response:**
```json
{
  "message": "Email verified successfully."
}
```

**Error Response:**
```json
{
  "message": "Invalid verification token."
}
```

#### Verify Email (Code Method)
```http
POST /api/v1/email/verify-code
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "ABC123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Email verified successfully",
  "data": {
    "message": "Email verified successfully"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Invalid or expired verification code.",
  "data": []
}
```

## Phone Verification

### How It Works
1. User requests OTP via API
2. System generates 6-digit OTP and sends via SMS
3. User enters OTP in frontend
4. Frontend submits OTP for verification
5. User account is marked as phone verified

### API Endpoints

#### Send OTP
```http
POST /api/v1/phone/send-otp
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent successfully",
  "data": {
    "message": "OTP sent successfully"
  }
}
```

#### Verify OTP
```http
POST /api/v1/phone/verify-otp
Authorization: Bearer {token}
Content-Type: application/json

{
  "otp": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Phone number verified successfully",
  "data": {
    "message": "Phone number verified successfully"
  }
}
```

## Frontend Integration

### Email Verification Flow

#### Link Method
1. User clicks verification link from email: `http://localhost:9000/email/verify/{id}/{hash}`
2. Frontend extracts `id` and `hash` from URL
3. Frontend makes API call to: `GET /api/v1/email/verify/{id}/{hash}`
4. Handle success/error responses and update UI accordingly

#### Code Method
1. User enters the 6-character code from email into frontend form
2. Frontend makes API call to: `POST /api/v1/email/verify-code` with code in request body
3. Handle success/error responses and update UI accordingly

### Phone Verification Flow
1. User enters phone number and requests OTP
2. Frontend calls: `POST /api/v1/phone/send-otp`
3. User enters received OTP
4. Frontend calls: `POST /api/v1/phone/verify-otp` with OTP
5. Handle verification result

### Example Frontend Code (JavaScript/React)

```javascript
// Email verification - Link method
const verifyEmail = async (id, hash) => {
  try {
    const response = await fetch(`/api/v1/email/verify/${id}/${hash}`);
    const data = await response.json();

    if (response.ok) {
      // Email verified successfully
      showSuccessMessage(data.message);
      redirectToDashboard();
    } else {
      // Verification failed
      showErrorMessage(data.message);
    }
  } catch (error) {
    showErrorMessage('Verification failed. Please try again.');
  }
};

// Email verification - Code method
const verifyEmailWithCode = async (code) => {
  try {
    const response = await fetch('/api/v1/email/verify-code', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ code })
    });
    const data = await response.json();

    if (response.ok) {
      showSuccessMessage('Email verified successfully');
      redirectToDashboard();
    } else {
      showErrorMessage(data.message);
    }
  } catch (error) {
    showErrorMessage('Verification failed');
  }
};

// Phone verification
const sendOTP = async () => {
  try {
    const response = await fetch('/api/v1/phone/send-otp', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    const data = await response.json();

    if (response.ok) {
      showSuccessMessage('OTP sent to your phone');
    } else {
      showErrorMessage(data.message);
    }
  } catch (error) {
    showErrorMessage('Failed to send OTP');
  }
};

const verifyOTP = async (otp) => {
  try {
    const response = await fetch('/api/v1/phone/verify-otp', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ otp })
    });
    const data = await response.json();

    if (response.ok) {
      showSuccessMessage('Phone verified successfully');
      redirectToDashboard();
    } else {
      showErrorMessage(data.message);
    }
  } catch (error) {
    showErrorMessage('Verification failed');
  }
};
```

## Configuration

### Mail Configuration
Update `config/mail.php`:
```php
'default' => env('MAIL_MAILER', 'smtp'),
```

Set environment variables in `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Frontend URL Configuration
Update `config/app.php`:
```php
'frontend_url' => env('FRONTEND_URL', 'http://localhost:9000'),
```

Set in `.env`:
```
FRONTEND_URL=http://localhost:9000
```

## Security Considerations
- OTPs expire after 5 minutes
- Invalid OTP attempts are tracked
- Email verification links are time-sensitive
- All verification endpoints require authentication where appropriate

## Error Handling
- Invalid verification tokens return 400 status
- Already verified accounts return appropriate messages
- SMS failures are logged and user notified
- Rate limiting is implemented on verification endpoints

## Testing
Use the following commands to test the verification system:

```bash
# Generate Swagger documentation
php artisan l5-swagger:generate

# Test email verification
curl -X POST http://localhost:8000/api/v1/email/resend \
  -H "Authorization: Bearer {token}"

# Test email verification with code
curl -X POST http://localhost:8000/api/v1/email/verify-code \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"code": "ABC123"}'

# Test phone verification
curl -X POST http://localhost:8000/api/v1/phone/send-otp \
  -H "Authorization: Bearer {token}"

curl -X POST http://localhost:8000/api/v1/phone/verify-otp \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"otp": "123456"}'
```

## Database Schema
The verification system uses the following database fields:
- `email_verified_at` (timestamp) - Email verification timestamp
- `phone_verified_at` (timestamp) - Phone verification timestamp
- `phone_number` (string) - User's phone number

Ensure these fields exist in your users table migration.
