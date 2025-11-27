# Password Reset System Documentation

## Overview
The Dukaverse Backend provides a comprehensive password reset system supporting both email link and code-based reset methods. This system ensures secure password recovery with multiple verification options for better user experience.

## Features
- Password reset via clickable links
- Password reset via verification codes
- Secure token-based authentication
- Swagger API documentation
- Frontend integration support

## Password Reset Flow

### How It Works
1. User requests password reset with email
2. System sends email with both reset link and 6-character code
3. User can reset password using either method:
   - **Link Method**: Click link, redirected to frontend reset form
   - **Code Method**: Enter code directly in frontend form
4. User enters new password and confirms
5. Password is updated and user can login

### API Endpoints

#### Request Password Reset
```http
POST /api/v1/forgot-password
Content-Type: application/json

{
  "email": "user@example.com",
  "platform_id": 1
}
```

**Response:**
```json
{
  "message": "Password reset link sent to your email."
}
```

#### Reset Password (Link Method)
```http
POST /api/v1/reset-password
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123",
  "token": "reset-token-here"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Password has been reset successfully.",
  "data": {
    "message": "Password has been reset successfully."
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "This password reset token is invalid.",
  "data": []
}
```

#### Reset Password (Code Method)
```http
POST /api/v1/reset-password-code
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123",
  "code": "ABC123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Password has been reset successfully.",
  "data": {
    "message": "Password has been reset successfully."
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Invalid or expired reset code.",
  "data": []
}
```

## Frontend Integration

### Password Reset Flow

#### Link Method
1. User clicks reset link from email: `http://localhost:9000/reset-password?token={token}&email={email}`
2. Frontend extracts token and email from URL parameters
3. User enters new password and confirmation
4. Frontend makes API call to: `POST /api/v1/reset-password` with token, email, password, and confirmation
5. Handle success/error responses and redirect to login

#### Code Method
1. User enters the 6-character code from email into frontend form
2. User enters new password and confirmation
3. Frontend makes API call to: `POST /api/v1/reset-password-code` with code, email, password, and confirmation
4. Handle success/error responses and redirect to login

### Example Frontend Code (JavaScript/React)

```javascript
// Request password reset
const requestPasswordReset = async (email, platformId) => {
  try {
    const response = await fetch('/api/v1/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email, platform_id: platformId })
    });
    const data = await response.json();

    if (response.ok) {
      showSuccessMessage('Password reset link sent to your email');
    } else {
      showErrorMessage(data.message);
    }
  } catch (error) {
    showErrorMessage('Failed to send reset email');
  }
};

// Reset password with token (link method)
const resetPasswordWithToken = async (email, token, password, passwordConfirmation) => {
  try {
    const response = await fetch('/api/v1/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email,
        password,
        password_confirmation: passwordConfirmation,
        token
      })
    });
    const data = await response.json();

    if (response.ok) {
      showSuccessMessage('Password reset successfully');
      redirectToLogin();
    } else {
      showErrorMessage(data.message);
    }
  } catch (error) {
    showErrorMessage('Password reset failed');
  }
};

// Reset password with code
const resetPasswordWithCode = async (email, code, password, passwordConfirmation) => {
  try {
    const response = await fetch('/api/v1/reset-password-code', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email,
        password,
        password_confirmation: passwordConfirmation,
        code
      })
    });
    const data = await response.json();

    if (response.ok) {
      showSuccessMessage('Password reset successfully');
      redirectToLogin();
    } else {
      showErrorMessage(data.message);
    }
  } catch (error) {
    showErrorMessage('Password reset failed');
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
- Reset codes expire after 30 minutes
- Password reset tokens are time-sensitive
- Passwords must be at least 8 characters with confirmation
- Rate limiting should be implemented on reset endpoints
- Invalid attempts are tracked

## Error Handling
- Invalid reset tokens return 400 status
- Expired codes return appropriate error messages
- User not found returns 404
- Validation errors return 422 with detailed messages

## Testing
Use the following commands to test the password reset system:

```bash
# Request password reset
curl -X POST http://localhost:8000/api/v1/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "platform_id": 1}'

# Reset password with token
curl -X POST http://localhost:8000/api/v1/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123",
    "token": "reset-token-here"
  }'

# Reset password with code
curl -X POST http://localhost:8000/api/v1/reset-password-code \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123",
    "code": "ABC123"
  }'
```

## Database Schema
The password reset system uses the following database table:
- `password_reset_tokens` - Stores reset tokens with email and expiration

Ensure this table exists in your database migrations.

## Platform Support
The forgot password endpoint requires a `platform_id` to determine the correct frontend URL for the reset link. Make sure platforms are properly configured in the database.
