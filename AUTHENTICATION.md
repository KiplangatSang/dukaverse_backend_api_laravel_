# Dukaverse Backend Authentication Guide

This document provides comprehensive information about authentication methods available in the Dukaverse Backend API, including traditional email/password authentication and social media authentication.

## Table of Contents

1. [Traditional Authentication](#traditional-authentication)
2. [Social Media Authentication](#social-media-authentication)
3. [API Token Usage](#api-token-usage)
4. [Error Handling](#error-handling)

## Traditional Authentication

### Login

**Endpoint:** `POST /api/v1/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "username": "johndoe"
  },
  "token": "1|abc123def456..."
}
```

### Register

**Endpoint:** `POST /api/v1/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "username": "johndoe",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Logout

**Endpoint:** `POST /api/v1/logout`

**Headers:**
```
Authorization: Bearer {token}
```

## Social Media Authentication

The Dukaverse Backend supports OAuth authentication with the following providers:
- Google
- Facebook
- Twitter
- GitHub
- GitLab

### Authentication Flow

Social authentication follows a two-step process:

1. **Get Authorization URL** - Frontend requests the OAuth URL from our API
2. **Handle Callback** - Provider redirects back to our API with authorization code

### Step 1: Get Authorization URL

**Endpoint:** `GET /api/v1/auth/{provider}`

**Supported Providers:** `google`, `facebook`, `twitter`, `github`, `gitlab`

**Example Request:**
```
GET /api/v1/auth/google
```

**Response:**
```json
{
  "authorization_url": "https://accounts.google.com/oauth/authorize?...",
  "provider": "google"
}
```

**Frontend Implementation:**
```javascript
// React/Vue example
const initiateSocialLogin = async (provider) => {
  try {
    const response = await fetch(`/api/v1/auth/${provider}`);
    const data = await response.json();

    // Redirect user to the authorization URL
    window.location.href = data.authorization_url;
  } catch (error) {
    console.error('Failed to get authorization URL:', error);
  }
};
```

### Step 2: Handle OAuth Callback

After the user authorizes your app with the social provider, they are redirected back to:

**Callback URL:** `GET /api/v1/auth/{provider}/callback`

This endpoint:
1. Exchanges the authorization code for an access token
2. Creates or finds the user account
3. Generates a Sanctum API token
4. Redirects to your frontend with the token

**Redirect URL Format:**
```
{your_frontend_url}/auth/callback?token={sanctum_token}&provider={provider}
```

**Frontend Implementation:**
```javascript
// Handle the callback in your frontend
const handleAuthCallback = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get('token');
  const provider = urlParams.get('provider');
  const error = urlParams.get('error');

  if (error) {
    console.error('Authentication failed:', error);
    // Handle error (redirect to login page with error message)
  } else if (token) {
    // Store the token (localStorage, cookies, etc.)
    localStorage.setItem('auth_token', token);

    // Redirect to dashboard or home page
    window.location.href = '/dashboard';
  }
};
```

### Linking Social Accounts

Authenticated users can link additional social accounts to their existing account.

**Endpoint:** `POST /api/v1/auth/{provider}/link`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "code": "oauth_authorization_code"
}
```

**Response:**
```json
{
  "message": "Google account linked successfully",
  "linked_accounts": [
    {
      "provider": "google",
      "provider_id": "123456789",
      "avatar": "https://lh3.googleusercontent.com/...",
      "linked_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Unlinking Social Accounts

**Endpoint:** `DELETE /api/v1/auth/{provider}/unlink`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Google account unlinked successfully",
  "linked_accounts": []
}
```

### Getting Linked Accounts

**Endpoint:** `GET /api/v1/auth/linked-accounts`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "linked_accounts": [
    {
      "provider": "google",
      "provider_id": "123456789",
      "avatar": "https://lh3.googleusercontent.com/...",
      "linked_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

## API Token Usage

All authenticated endpoints require the Sanctum token in the Authorization header:

```
Authorization: Bearer {your_sanctum_token}
```

**Example cURL:**
```bash
curl -X GET \
  'https://api.dukaverse.com/api/v1/user/data' \
  -H 'Authorization: Bearer 1|abc123def456...' \
  -H 'Content-Type: application/json'
```

## Error Handling

### Common Error Responses

**Invalid Provider:**
```json
{
  "error": "Invalid provider"
}
```

**Authentication Failed:**
```json
{
  "error": "Invalid social account data received. Please try again."
}
```

**Account Already Linked:**
```json
{
  "error": "This social account is already linked to your account."
}
```

**Unauthorized Access:**
```json
{
  "message": "Unauthenticated."
}
```

## Security Considerations

1. **Rate Limiting:** Social auth endpoints are rate-limited to 10 requests per minute per IP
2. **State Validation:** OAuth flows include state parameters for CSRF protection
3. **Token Security:** Sanctum tokens are securely generated and should be stored safely
4. **HTTPS Required:** All OAuth callbacks must use HTTPS in production
5. **Provider Validation:** Only configured providers are accepted

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Frontend URL for OAuth redirects
FRONTEND_URL=https://yourapp.com

# OAuth Provider Credentials
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://api.yourapp.com/api/v1/auth/google/callback

FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret
FACEBOOK_REDIRECT_URI=https://api.yourapp.com/api/v1/auth/facebook/callback

# ... other providers
```

### Provider Setup

1. **Google:** Create OAuth 2.0 credentials in Google Cloud Console
2. **Facebook:** Create an app in Facebook Developers
3. **Twitter:** Create an app in Twitter Developer Portal
4. **GitHub:** Create OAuth App in GitHub Settings
5. **GitLab:** Create an application in GitLab User Settings

Set redirect URIs to: `https://yourapi.com/api/v1/auth/{provider}/callback`

## Testing

Use the following commands to test social authentication:

```bash
# Run social auth tests
php artisan test --filter=SocialAuthTest

# Generate API documentation
php artisan l5-swagger:generate
```

## Support

For issues with social authentication:
1. Check the Laravel logs in `storage/logs/laravel.log`
2. Verify OAuth credentials are correctly set
3. Ensure redirect URIs match provider configuration
4. Test with different browsers/devices

## API Documentation

Complete API documentation is available via Swagger UI at `/api/documentation` when L5-Swagger is properly configured.

The social authentication endpoints are documented under the "Social Authentication" tag in the API documentation.
