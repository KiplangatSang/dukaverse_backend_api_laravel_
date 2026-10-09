# Delivery App API Documentation

This document outlines all the APIs required for the delivery app functionality. The app uses a RESTful API architecture with JSON request/response format.

## Base URL
```
http://api.dukaverse.co.ke/api/v1
```

## Authentication
All API endpoints except authentication endpoints require Bearer token authentication:
```
Authorization: Bearer {jwt_token}
```

## Authentication APIs

### POST /login
Login user with email and password.

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
  "success": true,
  "token": "jwt_token_here",
  "user": {
    "id": "user_id",
    "name": "John Doe",
    "email": "user@example.com",
    "verified": true,
    "accredited": false,
    "subscribed": false
  }
}
```

### POST /register
Register a new user.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "phone": "+1234567890",
  "nationalId": "12345678"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful. Please verify your email.",
  "user": {
    "id": "user_id",
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

### POST /logout
Logout user.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### POST /email/resend
Send email verification code.

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Verification code sent to your email"
}
```

### POST /email/verify-code
Verify email with code.

**Request Body:**
```json
{
  "email": "user@example.com",
  "code": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

### POST /forgot-password
Request password reset.

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```

### POST /reset-password
Reset password with token.

**Request Body:**
```json
{
  "token": "reset_token",
  "password": "new_password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

### GET /auth/google
Google OAuth sign-in (optional implementation).

**Request Body:**
```json
{
  "access_token": "google_oauth_token"
}
```

**Response:**
```json
{
  "success": true,
  "token": "jwt_token_here",
  "user": {
    "id": "user_id",
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

## User Profile APIs

### GET /rider/user/profile
Get user profile information.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "user_id",
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+1234567890",
    "rating": 4.5,
    "verified": true,
    "accredited": false,
    "subscribed": false,
    "subscription_type": "premium",
    "vehicle": {
      "make": "Toyota",
      "model": "Camry",
      "year": 2020,
      "license_plate": "ABC123",
      "color": "Blue",
      "vin": "VIN123456789"
    }
  }
}
```

### PUT /rider/user/profile
Update user profile.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "name": "John Doe",
  "phone": "+1234567890"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": "user_id",
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+1234567890"
  }
}
```

### PUT /rider/user/vehicle
Update vehicle information.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "make": "Toyota",
  "model": "Camry",
  "year": 2020,
  "license_plate": "ABC123",
  "color": "Blue",
  "vin": "VIN123456789"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Vehicle information updated successfully"
}
```

## Subscription APIs

### GET /rider/subscriptions/plans
Get available subscription plans.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "plans": [
    {
      "id": "basic",
      "name": "Basic",
      "price": 9.99,
      "features": ["Basic delivery matching", "Standard support"],
      "billing_period": "monthly"
    },
    {
      "id": "premium",
      "name": "Premium",
      "price": 19.99,
      "features": ["Priority delivery matching", "Premium support", "Analytics dashboard"],
      "billing_period": "monthly"
    }
  ]
}
```

### POST /rider/subscriptions/subscribe
Subscribe to a plan.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "plan_id": "premium",
  "payment_method": "card|paypal|apple_pay|google_pay"
}
```

**Response:**
```json
{
  "success": true,
  "subscription_id": "sub_123",
  "status": "active",
  "current_period_end": "2023-11-01T00:00:00Z"
}
```

### GET /rider/subscriptions/current
Get current subscription.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "subscription": {
    "id": "sub_123",
    "plan_id": "premium",
    "status": "active",
    "current_period_start": "2023-10-01T00:00:00Z",
    "current_period_end": "2023-11-01T00:00:00Z",
    "cancel_at_period_end": false
  }
}
```

### POST /rider/subscriptions/cancel
Cancel subscription.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "cancel_at_period_end": true
}
```

### POST /rider/subscriptions/reactivate
Reactivate cancelled subscription.

**Headers:**
- Authorization: Bearer {token}

## Verification APIs

### POST /rider/verification/initiate
Initiate verification process.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "type": "email|phone|identity"
}
```

**Response:**
```json
{
  "success": true,
  "verification_id": "ver_123",
  "status": "pending",
  "expires_at": "2023-10-01T11:00:00Z"
}
```

### POST /rider/verification/verify
Complete verification.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "verification_id": "ver_123",
  "code": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "verified": true,
  "type": "email"
}
```

### GET /rider/verification/status
Get verification status.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "verifications": {
    "email": {
      "verified": true,
      "verified_at": "2023-10-01T10:00:00Z"
    },
    "phone": {
      "verified": false,
      "pending": true
    },
    "identity": {
      "verified": false,
      "pending": false
    }
  }
}
```

## Accreditation APIs

### POST /rider/accreditation/apply
Apply for accreditation.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "business_type": "individual|business",
  "years_experience": 2,
  "specializations": ["food_delivery", "package_delivery"],
  "references": [
    {
      "name": "John Smith",
      "company": "ABC Logistics",
      "email": "john@abc.com",
      "phone": "+1234567890"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "application_id": "acc_123",
  "status": "under_review",
  "estimated_completion": "2023-10-15T00:00:00Z"
}
```

### GET /rider/accreditation/status
Get accreditation status.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "accreditation": {
    "status": "approved|rejected|under_review|pending_documents",
    "application_id": "acc_123",
    "applied_at": "2023-10-01T10:00:00Z",
    "approved_at": "2023-10-05T10:00:00Z",
    "expires_at": "2024-10-05T10:00:00Z",
    "level": "standard|premium",
    "specializations": ["food_delivery", "package_delivery"]
  }
}
```

### POST /rider/accreditation/upload-document
Upload accreditation documents.

**Headers:**
- Authorization: Bearer {token}
- Content-Type: multipart/form-data

**Form Data:**
- file: Document file
- type: "business_license|insurance|background_check|training_certificate"

**Response:**
```json
{
  "success": true,
  "message": "Document uploaded successfully",
  "document_id": "doc_123"
}
```

### GET /rider/accreditation/requirements
Get accreditation requirements.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "requirements": {
    "documents_required": [
      {
        "type": "business_license",
        "name": "Business License",
        "description": "Valid business license for your area",
        "required": true
      },
      {
        "type": "insurance",
        "name": "Insurance Certificate",
        "description": "Commercial auto insurance",
        "required": true
      }
    ],
    "minimum_experience": 1,
    "background_check_required": true
  }
}
```

### POST /rider/accreditation/renew
Renew accreditation.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "renewal_id": "ren_123",
  "status": "processing"
}
```

## Payment APIs

### POST /rider/payments/process
Process a payment.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "type": "verification|accreditation|subscription",
  "payment_method": "card|paypal|apple_pay|google_pay",
  "amount": 19.99
}
```

**Response:**
```json
{
  "success": true,
  "payment_id": "payment_123",
  "status": "processing"
}
```

### GET /rider/payments/status/{paymentId}
Check payment status.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "payment_id": "payment_123",
  "status": "completed|failed|processing",
  "amount": 19.99,
  "type": "verification"
}
```

### POST /rider/payments/webhook
Stripe webhook for payment confirmations.

**Headers:**
- Stripe-Signature: {signature}

**Request Body:** (Stripe webhook payload)

### GET /rider/payments/history
Get payment history.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "payments": [
    {
      "id": "payment_123",
      "type": "verification",
      "amount": 19.99,
      "status": "completed",
      "date": "2023-10-01T10:00:00Z"
    }
  ]
}
```

## Delivery APIs

### GET /rider/deliveries/available
Get available delivery jobs.

**Headers:**
- Authorization: Bearer {token}

**Query Parameters:**
- lat: latitude
- lng: longitude
- radius: search radius in km

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "job_123",
      "title": "Deliver package to downtown",
      "description": "Package delivery from store to customer",
      "pickup_location": {
        "lat": 40.7128,
        "lng": -74.0060,
        "address": "123 Main St"
      },
      "delivery_location": {
        "lat": 40.7589,
        "lng": -73.9851,
        "address": "456 Broadway"
      },
      "estimated_distance": 5.2,
      "estimated_time": 30,
      "base_pay": 15.00,
      "created_at": "2023-10-01T10:00:00Z"
    }
  ]
}
```

### POST /rider/deliveries/{jobId}/bid
Place a bid on a delivery job.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "bid_amount": 16.50,
  "estimated_time": 25
}
```

**Response:**
```json
{
  "success": true,
  "bid_id": "bid_123",
  "message": "Bid placed successfully"
}
```

### GET /rider/deliveries/my-bids
Get user's active bids.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "bid_123",
      "job_id": "job_123",
      "bid_amount": 16.50,
      "status": "pending|accepted|rejected",
      "created_at": "2023-10-01T10:05:00Z"
    }
  ]
}
```

### POST /rider/deliveries/{jobId}/accept
Accept a delivery job (for awarded bidders).

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "message": "Job accepted successfully"
}
```

### PUT /rider/deliveries/{jobId}/status
Update delivery status.

**Headers:**
- Authorization: Bearer {token}

**Request Body:**
```json
{
  "status": "picked_up|in_transit|delivered"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Status updated successfully"
}
```

### GET /rider/deliveries/history
Get delivery history.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "deliveries": [
    {
      "id": "delivery_123",
      "job_title": "Deliver documents",
      "status": "completed",
      "earnings": 20.00,
      "rating": 5,
      "completed_at": "2023-10-01T12:00:00Z"
    }
  ]
}
```

## Service Details APIs

### GET /rider/subscription/details
Get subscription service details.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "subscription-basic",
    "name": "Basic Subscription",
    "description": "Essential delivery services with basic features",
    "price": 1000,
    "currency": "KES",
    "features": [
      "Basic delivery tracking",
      "Standard support",
      "Mobile app access",
      "Basic analytics"
    ],
    "duration": "Monthly",
    "isPopular": false
  }
}
```

### GET /rider/verification/details
Get verification service details.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "verification-basic",
    "name": "Identity Verification",
    "description": "Complete identity and credential verification",
    "price": 2000,
    "currency": "KES",
    "features": [
      "ID verification",
      "Address confirmation",
      "Criminal background check",
      "Reference checks",
      "Digital badge"
    ],
    "duration": "One-time",
    "isPopular": true
  }
}
```

### GET /rider/accreditation/details
Get accreditation service details.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "accreditation-basic",
    "name": "Delivery Accreditation",
    "description": "Become a certified delivery partner with full accreditation",
    "price": 5000,
    "currency": "KES",
    "features": [
      "Official accreditation certificate",
      "Priority job assignments",
      "Higher earning potential",
      "Access to premium clients",
      "Training and support",
      "Quality assurance badge"
    ],
    "duration": "One-time",
    "isPopular": true
  }
}
```

## Notification APIs

### GET /rider/notifications
Get user notifications.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "notifications": [
    {
      "id": "notif_123",
      "type": "bid_accepted|job_available|payment_received",
      "title": "Bid Accepted",
      "message": "Your bid for 'Deliver package' has been accepted",
      "read": false,
      "created_at": "2023-10-01T10:00:00Z"
    }
  ]
}
```

### PUT /rider/notifications/{notificationId}/read
Mark notification as read.

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

## Admin APIs (if applicable)

### GET /rider/admin/users
Get all users (admin only).

**Headers:**
- Authorization: Bearer {admin_token}

**Response:**
```json
{
  "success": true,
  "users": [
    {
      "id": "user_123",
      "name": "John Doe",
      "email": "john@example.com",
      "verified": true,
      "accredited": false,
      "subscribed": true,
      "created_at": "2023-10-01T10:00:00Z"
    }
  ]
}
```

### GET /rider/admin/deliveries
Get all deliveries (admin only).

**Headers:**
- Authorization: Bearer {admin_token}

**Response:**
```json
{
  "success": true,
  "deliveries": [
    {
      "id": "delivery_123",
      "driver_id": "user_123",
      "status": "completed",
      "earnings": 25.00,
      "created_at": "2023-10-01T10:00:00Z"
    }
  ]
}
```

### PUT /rider/admin/user/{userId}/status
Update user status (admin only).

**Headers:**
- Authorization: Bearer {admin_token}

**Request Body:**
```json
{
  "verified": true,
  "accredited": true,
  "subscribed": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "User status updated successfully"
}
```

## Error Response Format

All APIs return errors in this format:

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR|AUTHENTICATION_ERROR|NOT_FOUND",
    "message": "Human readable error message"
  }
}
```

## Rate Limiting

- Authentication endpoints: 5 requests per minute
- Payment endpoints: 10 requests per minute
- Other endpoints: 100 requests per minute

## WebSocket Events (Real-time Updates)

- `bid_accepted`: When a user's bid is accepted
- `new_job`: When a new delivery job is posted
- `payment_completed`: When a payment is processed
- `notification`: For general notifications

## File Upload APIs

### POST /rider/upload/document
Upload verification documents.

**Headers:**
- Authorization: Bearer {token}
- Content-Type: multipart/form-data

**Form Data:**
- file: Document file (PDF, JPG, PNG)
- type: "id_card|license|insurance"

**Response:**
```json
{
  "success": true,
  "message": "Document uploaded successfully",
  "file_url": "https://storage.example.com/documents/doc_123.pdf"
}
```

### POST /rider/upload/vehicle-photo
Upload vehicle photos.

**Headers:**
- Authorization: Bearer {token}
- Content-Type: multipart/form-data

**Form Data:**
- file: Photo file
- type: "front|side|back"

**Response:**
```json
{
  "success": true,
  "message": "Photo uploaded successfully",
  "file_url": "https://storage.example.com/vehicles/photo_123.jpg"
}
```
