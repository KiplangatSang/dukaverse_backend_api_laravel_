# Comprehensive Restaurant & Delivery Management API Specification

## Overview

This document provides a complete, enterprise-grade API specification for Restaurant Terminal and Delivery Management systems. It covers all necessary endpoints for a production-ready platform including multi-tenancy, real-time features, integrations, and advanced business logic.

## Table of Contents

1. [System Overview](#system-overview)
2. [Authentication & Authorization](#authentication--authorization)
3. [Multi-Tenancy](#multi-tenancy)
4. [Restaurant Management APIs](#restaurant-management-apis)
5. [Menu & Inventory Management](#menu--inventory-management)
6. [Order Management System](#order-management-system)
7. [Table & Reservation Management](#table--reservation-management)
8. [Staff & Shift Management](#staff--shift-management)
9. [Kitchen Display System](#kitchen-display-system)
10. [Payment & Financial Management](#payment--financial-management)
11. [Customer Relationship Management](#customer-relationship-management)
12. [Delivery Management System](#delivery-management-system)
13. [Analytics & Reporting](#analytics--reporting)
14. [Notification & Communication](#notification--communication)
15. [File Management & Media](#file-management--media)
16. [System Administration](#system-administration)
17. [Integration APIs](#integration-apis)
18. [Real-time WebSocket APIs](#real-time-websocket-apis)
19. [Error Handling & Response Codes](#error-handling--response-codes)

---

## System Overview

### System Components

- **Restaurant Terminal**: POS system for restaurants
- **Delivery Management**: Rider coordination and tracking
- **Admin Portal**: Multi-tenant management
- **Customer App**: Order placement and tracking
- **Rider App**: Delivery management
- **Kitchen Display**: Order preparation tracking

### Technology Stack Recommendations

- **Backend**: Node.js/Express or Python/Django
- **Database**: PostgreSQL with TimescaleDB for time-series data
- **Cache**: Redis for sessions and real-time data
- **Queue**: Redis/Bull for background jobs
- **Real-time**: Socket.io or WebSocket
- **File Storage**: AWS S3 or similar
- **Email/SMS**: SendGrid/Twilio integration

---

## Authentication & Authorization

### JWT Token Authentication

All API endpoints require JWT token authentication except public endpoints.

**Headers:**

```
Authorization: Bearer <jwt_token>
X-API-Key: <api_key>  // For service-to-service calls
Content-Type: application/json
X-Tenant-ID: <tenant_id>  // For multi-tenant requests
```

### Authentication Endpoints

#### Register Restaurant Owner

```http
POST /api/v1/auth/register/restaurant-owner
```

**Request Body:**

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@restaurant.com",
  "phone": "+254700000000",
  "password": "SecurePass123!",
  "restaurant_name": "DukaVerse Restaurant",
  "business_type": "restaurant|cafe|bar|food_truck",
  "location": {
    "address": "123 Main St, Nairobi, Kenya",
    "latitude": -1.2864,
    "longitude": 36.8172,
    "city": "Nairobi",
    "country": "Kenya"
  },
  "tax_id": "P051234567Q",
  "license_number": "LIC2024001",
  "operating_hours": {
    "monday": { "open": "08:00", "close": "22:00" },
    "tuesday": { "open": "08:00", "close": "22:00" },
    "wednesday": { "open": "08:00", "close": "22:00" },
    "thursday": { "open": "08:00", "close": "22:00" },
    "friday": { "open": "08:00", "close": "23:00" },
    "saturday": { "open": "09:00", "close": "23:00" },
    "sunday": { "open": "10:00", "close": "21:00" }
  }
}
```

#### Register Staff Member

```http
POST /api/v1/auth/register/staff
```

**Request Body:**

```json
{
  "restaurant_id": "uuid",
  "first_name": "Alice",
  "last_name": "Johnson",
  "email": "alice@restaurant.com",
  "phone": "+254700000000",
  "password": "TempPass123!",
  "role": "waiter|manager|chef|cashier|delivery_driver",
  "permissions": ["orders.create", "orders.update", "menu.view"],
  "pin_code": "1234",
  "shift_preferences": {
    "preferred_days": ["monday", "tuesday", "wednesday"],
    "preferred_times": { "start": "09:00", "end": "17:00" }
  },
  "hourly_rate": 250.0,
  "emergency_contact": {
    "name": "Bob Johnson",
    "phone": "+254711111111",
    "relationship": "brother"
  }
}
```

#### Login

```http
POST /api/v1/auth/login
```

**Request Body:**

```json
{
  "email": "john@restaurant.com",
  "password": "SecurePass123!",
  "device_info": {
    "device_id": "device-uuid",
    "device_type": "terminal|mobile|web",
    "app_version": "1.0.0"
  }
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "refresh_token_here",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": "uuid",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@restaurant.com",
      "role": "restaurant_owner",
      "restaurant_id": "uuid",
      "permissions": ["*"],
      "last_login": "2024-01-15T10:30:00Z"
    },
    "restaurant": {
      "id": "uuid",
      "name": "DukaVerse Restaurant",
      "status": "active",
      "subscription_plan": "premium"
    }
  }
}
```

#### Refresh Token

```http
POST /api/v1/auth/refresh
```

**Request Body:**

```json
{
  "refresh_token": "refresh_token_here"
}
```

#### Logout

```http
POST /api/v1/auth/logout
```

#### Password Reset

```http
POST /api/v1/auth/forgot-password
```

**Request Body:**

```json
{
  "email": "john@restaurant.com"
}
```

```http
POST /api/v1/auth/reset-password
```

**Request Body:**

```json
{
  "token": "reset_token_from_email",
  "password": "NewSecurePass123!",
  "confirm_password": "NewSecurePass123!"
}
```

### Role-Based Access Control

#### Get User Permissions

```http
GET /api/v1/auth/permissions
```

**Response:**

```json
{
  "success": true,
  "data": {
    "user_id": "uuid",
    "role": "manager",
    "permissions": ["orders.*", "menu.*", "staff.view", "reports.view", "settings.update"],
    "restrictions": {
      "max_discount_percentage": 15,
      "can_void_orders": true,
      "can_modify_menu": false
    }
  }
}
```

#### Update User Permissions

```http
PUT /api/v1/auth/users/{user_id}/permissions
```

**Request Body:**

```json
{
  "permissions": ["orders.create", "orders.update", "menu.view"],
  "restrictions": {
    "max_discount_percentage": 10,
    "can_void_orders": false
  }
}
```

---

## Multi-Tenancy

### Tenant Management

#### Create Restaurant/Tenant

```http
POST /api/v1/admin/restaurants
```

**Request Body:**

```json
{
  "name": "DukaVerse Restaurant",
  "description": "Modern fusion cuisine",
  "owner_name": "John Doe",
  "email": "owner@dukaverse.com",
  "phone": "+254700000000",
  "business_type": "restaurant|cafe|fast_food",
  "location": {
    "address": "123 Main St, Nairobi",
    "latitude": -1.2864,
    "longitude": 36.8172,
    "city": "Nairobi",
    "country": "Kenya"
  },
  "settings": {
    "currency": "KES",
    "timezone": "Africa/Nairobi",
    "tax_rate": 16.0,
    "service_charge_rate": 2.0,
    "features": ["delivery", "takeaway", "dine_in"],
    "operating_hours": {
      "monday": { "open": "08:00", "close": "22:00" },
      "tuesday": { "open": "08:00", "close": "22:00" }
    }
  },
  "subscription_plan": "basic|premium|enterprise"
}
```

#### Get Restaurant Details

```http
GET /api/v1/restaurants/{restaurant_id}
```

#### Update Restaurant Settings

```http
PUT /api/v1/restaurants/{restaurant_id}/settings
```

---

## Restaurant Management APIs

### Restaurant Profile Management

#### Get Restaurant Profile

```http
GET /api/v1/restaurants/{restaurant_id}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "DukaVerse Restaurant",
    "description": "Modern fusion cuisine",
    "logo_url": "https://cdn.dukaverse.com/restaurants/uuid/logo.jpg",
    "cover_image_url": "https://cdn.dukaverse.com/restaurants/uuid/cover.jpg",
    "contact": {
      "phone": "+254700000000",
      "email": "info@restaurant.com",
      "website": "https://restaurant.com"
    },
    "location": {
      "address": "123 Main St, Nairobi, Kenya",
      "latitude": -1.2864,
      "longitude": 36.8172,
      "city": "Nairobi",
      "postal_code": "00100"
    },
    "operating_hours": {
      "monday": { "open": "08:00", "close": "22:00", "closed": false },
      "tuesday": { "open": "08:00", "close": "22:00", "closed": false }
    },
    "settings": {
      "currency": "KES",
      "timezone": "Africa/Nairobi",
      "tax_rate": 16.0,
      "service_charge_rate": 2.0,
      "auto_print_receipts": true,
      "enable_online_orders": true,
      "enable_delivery": true,
      "enable_reservations": true
    },
    "status": "active",
    "subscription_plan": "premium",
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

#### Update Restaurant Profile

```http
PUT /api/v1/restaurants/{restaurant_id}
```

**Request Body:**

```json
{
  "name": "Updated Restaurant Name",
  "description": "Updated description",
  "contact": {
    "phone": "+254711111111",
    "email": "newemail@restaurant.com"
  },
  "settings": {
    "tax_rate": 18.0,
    "auto_print_receipts": false
  }
}
```

### Restaurant Settings

#### Get Restaurant Settings

```http
GET /api/v1/restaurants/{restaurant_id}/settings
```

**Response:**

```json
{
  "success": true,
  "data": {
    "general": {
      "currency": "KES",
      "timezone": "Africa/Nairobi",
      "language": "en",
      "date_format": "DD/MM/YYYY",
      "time_format": "24h"
    },
    "operations": {
      "auto_accept_orders": false,
      "preparation_time_buffer": 5,
      "max_advance_booking_days": 30,
      "min_order_value": 200.0,
      "max_order_value": 50000.0
    },
    "financial": {
      "tax_rate": 16.0,
      "service_charge_rate": 2.0,
      "payment_methods": ["cash", "card", "mpesa"],
      "auto_calculate_tips": true,
      "tip_suggestions": [5, 10, 15]
    },
    "notifications": {
      "email_notifications": true,
      "sms_notifications": true,
      "push_notifications": true,
      "order_confirmations": true,
      "payment_reminders": true
    },
    "integrations": {
      "payment_gateway": "pesapal",
      "sms_provider": "africastalking",
      "email_provider": "sendgrid",
      "pos_system": "square"
    }
  }
}
```

#### Update Restaurant Settings

```http
PUT /api/v1/restaurants/{restaurant_id}/settings
```

---

## Menu & Inventory Management

### Menu Categories

#### Get Menu Categories

```http
GET /api/v1/restaurants/{restaurant_id}/menu/categories
```

**Query Parameters:**

- `include_items`: boolean
- `status`: active|inactive|all
- `sort_by`: sort_order|name|created_at
- `sort_order`: asc|desc

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Main Courses",
      "description": "Primary dishes",
      "image_url": "https://cdn.dukaverse.com/categories/uuid/image.jpg",
      "sort_order": 1,
      "is_active": true,
      "item_count": 15,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 8,
    "pages": 1
  }
}
```

#### Create Menu Category

```http
POST /api/v1/restaurants/{restaurant_id}/menu/categories
```

**Request Body:**

```json
{
  "name": "Beverages",
  "description": "Drinks and refreshments",
  "image_url": "https://cdn.dukaverse.com/categories/beverages.jpg",
  "sort_order": 5,
  "is_active": true
}
```

#### Update Menu Category

```http
PUT /api/v1/restaurants/{restaurant_id}/menu/categories/{category_id}
```

#### Delete Menu Category

```http
DELETE /api/v1/restaurants/{restaurant_id}/menu/categories/{category_id}
```

### Menu Items

#### Get Menu Items

```http
GET /api/v1/restaurants/{restaurant_id}/menu/items
```

**Query Parameters:**

- `category_id`: uuid
- `search`: string
- `status`: available|unavailable|all
- `price_min`: number
- `price_max`: number
- `sort_by`: name|price|popularity|created_at
- `sort_order`: asc|desc
- `page`: number
- `limit`: number

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Chicken Burger",
      "description": "Grilled chicken breast with lettuce, tomato, and mayo",
      "category_id": "uuid",
      "category_name": "Main Courses",
      "base_price": 450.0,
      "image_urls": [
        "https://cdn.dukaverse.com/items/uuid/main.jpg",
        "https://cdn.dukaverse.com/items/uuid/side.jpg"
      ],
      "is_available": true,
      "is_featured": false,
      "preparation_time": 15,
      "calories": 650,
      "ingredients": [
        {
          "id": "uuid",
          "name": "Chicken Breast",
          "quantity": 150,
          "unit": "grams",
          "is_allergen": false
        }
      ],
      "allergens": ["gluten", "eggs"],
      "nutritional_info": {
        "protein": 35,
        "carbs": 45,
        "fat": 28,
        "fiber": 3
      },
      "customizations": [
        {
          "id": "uuid",
          "name": "Extra Cheese",
          "type": "add_on",
          "price": 50.0,
          "max_quantity": 2,
          "is_available": true
        },
        {
          "id": "uuid",
          "name": "Spice Level",
          "type": "modifier",
          "options": [
            { "name": "Mild", "price": 0 },
            { "name": "Medium", "price": 0 },
            { "name": "Hot", "price": 0 }
          ],
          "required": false
        }
      ],
      "tags": ["popular", "spicy", "chicken"],
      "popularity_score": 8.5,
      "total_orders": 1250,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "pages": 8
  }
}
```

#### Create Menu Item

```http
POST /api/v1/restaurants/{restaurant_id}/menu/items
```

**Request Body:**

```json
{
  "name": "Chicken Burger",
  "description": "Grilled chicken breast with lettuce, tomato, and mayo",
  "category_id": "uuid",
  "base_price": 450.0,
  "image_urls": ["https://cdn.dukaverse.com/items/chicken-burger.jpg"],
  "preparation_time": 15,
  "calories": 650,
  "ingredients": [
    {
      "name": "Chicken Breast",
      "quantity": 150,
      "unit": "grams",
      "cost_per_unit": 180.0
    }
  ],
  "allergens": ["gluten", "eggs"],
  "customizations": [
    {
      "name": "Extra Cheese",
      "type": "add_on",
      "price": 50.0,
      "max_quantity": 2
    }
  ],
  "tags": ["chicken", "burger"],
  "is_available": true,
  "is_featured": true
}
```

#### Update Menu Item

```http
PUT /api/v1/restaurants/{restaurant_id}/menu/items/{item_id}
```

#### Bulk Update Menu Items

```http
PUT /api/v1/restaurants/{restaurant_id}/menu/items/bulk
```

**Request Body:**

```json
{
  "updates": [
    {
      "id": "uuid",
      "price": 480.0,
      "is_available": false
    }
  ]
}
```

#### Delete Menu Item

```http
DELETE /api/v1/restaurants/{restaurant_id}/menu/items/{item_id}
```

### Inventory Management

#### Get Inventory Items

```http
GET /api/v1/restaurants/{restaurant_id}/inventory/items
```

**Query Parameters:**

- `category`: meat|vegetables|dairy|spices|beverages
- `status`: in_stock|low_stock|out_of_stock
- `supplier_id`: uuid
- `search`: string

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Chicken Breast",
      "category": "meat",
      "unit": "kg",
      "current_stock": 25.5,
      "min_stock_level": 10.0,
      "max_stock_level": 50.0,
      "cost_per_unit": 180.0,
      "supplier_id": "uuid",
      "supplier_name": "Fresh Farms Ltd",
      "expiry_date": "2024-01-25",
      "location": "Walk-in Freezer",
      "status": "in_stock",
      "last_updated": "2024-01-15T08:30:00Z"
    }
  ]
}
```

#### Update Inventory Stock

```http
PUT /api/v1/restaurants/{restaurant_id}/inventory/items/{item_id}/stock
```

**Request Body:**

```json
{
  "quantity": 15.0,
  "operation": "add|subtract|set",
  "reason": "daily_restock|order_usage|adjustment",
  "notes": "Restocked from supplier delivery"
}
```

#### Get Low Stock Alerts

```http
GET /api/v1/restaurants/{restaurant_id}/inventory/alerts
```

**Response:**

```json
{
  "success": true,
  "data": {
    "low_stock": [
      {
        "item_id": "uuid",
        "item_name": "Tomatoes",
        "current_stock": 3.5,
        "min_stock_level": 10.0,
        "supplier_name": "Fresh Produce Ltd",
        "last_restocked": "2024-01-10T09:00:00Z"
      }
    ],
    "expiring_soon": [
      {
        "item_id": "uuid",
        "item_name": "Milk",
        "expiry_date": "2024-01-18",
        "days_until_expiry": 3
      }
    ]
  }
}
```

#### Create Purchase Order

```http
POST /api/v1/restaurants/{restaurant_id}/inventory/purchase-orders
```

**Request Body:**

```json
{
  "supplier_id": "uuid",
  "items": [
    {
      "inventory_item_id": "uuid",
      "quantity": 20.0,
      "expected_cost": 3600.0
    }
  ],
  "expected_delivery_date": "2024-01-18",
  "notes": "Weekly restock order"
}
```

---

## Order Management System

### Order Creation & Processing

#### Create Order

```http
POST /api/v1/restaurants/{restaurant_id}/orders
```

**Request Body:**

```json
{
  "order_type": "dine_in|takeaway|delivery|online",
  "table_id": "uuid",
  "customer": {
    "name": "Jane Doe",
    "phone": "+254700000000",
    "email": "jane@example.com"
  },
  "items": [
    {
      "menu_item_id": "uuid",
      "quantity": 2,
      "customizations": [
        {
          "customization_id": "uuid",
          "option_name": "Extra Cheese",
          "quantity": 1
        }
      ],
      "special_instructions": "No onions please",
      "assigned_to": 0
    }
  ],
  "discounts": [
    {
      "type": "percentage|fixed",
      "value": 10.0,
      "reason": "Loyal customer discount",
      "applied_by": "uuid"
    }
  ],
  "scheduled_for": "2024-01-15T19:30:00Z",
  "delivery_address": {
    "street": "456 Oak Avenue",
    "city": "Nairobi",
    "coordinates": {
      "latitude": -1.2864,
      "longitude": 36.8172
    }
  },
  "created_by": "uuid",
  "source": "terminal|mobile_app|website|phone"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "order_id": "uuid",
    "order_number": "ORD-2024-001",
    "status": "pending",
    "subtotal": 900.0,
    "tax_amount": 144.0,
    "service_charge": 18.0,
    "discount_amount": 90.0,
    "delivery_fee": 150.0,
    "total_amount": 1122.0,
    "estimated_preparation_time": 20,
    "estimated_delivery_time": "2024-01-15T11:20:00Z",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

#### Get Orders

```http
GET /api/v1/restaurants/{restaurant_id}/orders
```

**Query Parameters:**

- `status`: pending|confirmed|preparing|ready|served|completed|cancelled
- `order_type`: dine_in|takeaway|delivery|online
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31
- `table_id`: uuid
- `customer_phone`: +254700000000
- `staff_id`: uuid
- `page`: 1
- `limit`: 20
- `sort_by`: created_at|total_amount|status
- `sort_order`: desc|asc

#### Get Order Details

```http
GET /api/v1/restaurants/{restaurant_id}/orders/{order_id}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "order_number": "ORD-2024-001",
    "status": "confirmed",
    "order_type": "dine-in",
    "table_id": "uuid",
    "table_number": 5,
    "customer": {
      "name": "Jane Doe",
      "phone": "+254700000000",
      "email": "jane@example.com"
    },
    "items": [
      {
        "id": "uuid",
        "menu_item_id": "uuid",
        "name": "Chicken Burger",
        "quantity": 2,
        "unit_price": 450.0,
        "customizations": [
          {
            "name": "Extra Cheese",
            "price": 50.0,
            "quantity": 1
          }
        ],
        "total_price": 950.0,
        "special_instructions": "No onions please",
        "status": "pending",
        "assigned_to": 0,
        "prepared_by": null,
        "prepared_at": null
      }
    ],
    "pricing": {
      "subtotal": 900.0,
      "tax_rate": 16.0,
      "tax_amount": 144.0,
      "service_charge_rate": 2.0,
      "service_charge": 18.0,
      "discounts": [
        {
          "type": "percentage",
          "value": 10.0,
          "amount": 90.0,
          "reason": "Loyal customer"
        }
      ],
      "delivery_fee": 0.0,
      "total_amount": 972.0
    },
    "timing": {
      "created_at": "2024-01-15T10:30:00Z",
      "confirmed_at": "2024-01-15T10:31:00Z",
      "estimated_preparation_time": 20,
      "estimated_ready_time": "2024-01-15T10:51:00Z",
      "actual_ready_time": null,
      "served_at": null
    },
    "staff": {
      "created_by": {
        "id": "uuid",
        "name": "Alice Johnson",
        "role": "waiter"
      },
      "assigned_waiter": {
        "id": "uuid",
        "name": "Bob Smith",
        "role": "waiter"
      },
      "assigned_chef": {
        "id": "uuid",
        "name": "Chef Maria",
        "role": "chef"
      }
    },
    "payments": [],
    "notes": "Customer requested window seat",
    "source": "terminal"
  }
}
```

#### Update Order Status

```http
PUT /api/v1/restaurants/{restaurant_id}/orders/{order_id}/status
```

**Request Body:**

```json
{
  "status": "confirmed",
  "notes": "Order confirmed and sent to kitchen",
  "updated_by": "uuid"
}
```

#### Update Order Items

```http
PUT /api/v1/restaurants/{restaurant_id}/orders/{order_id}/items
```

**Request Body:**

```json
{
  "items": [
    {
      "id": "uuid",
      "quantity": 3,
      "customizations": [
        {
          "customization_id": "uuid",
          "quantity": 2
        }
      ]
    }
  ],
  "updated_by": "uuid"
}
```

#### Cancel Order

```http
PUT /api/v1/restaurants/{restaurant_id}/orders/{order_id}/cancel
```

**Request Body:**

```json
{
  "reason": "customer_request|food_unavailable|restaurant_closed",
  "notes": "Customer changed their mind",
  "refund_amount": 972.0,
  "cancelled_by": "uuid"
}
```

#### Split Bill

```http
POST /api/v1/restaurants/{restaurant_id}/orders/{order_id}/split
```

**Request Body:**

```json
{
  "split_type": "equal|by_items|custom",
  "number_of_people": 3,
  "splits": [
    {
      "person_index": 0,
      "person_name": "Person 1",
      "item_ids": ["uuid1", "uuid2"],
      "custom_amount": null,
      "payment_method": "cash"
    }
  ],
  "created_by": "uuid"
}
```

### Order History & Search

#### Search Orders

```http
GET /api/v1/restaurants/{restaurant_id}/orders/search
```

**Query Parameters:**

- `query`: order number, customer name, phone, or item name
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31
- `status`: completed|cancelled
- `min_amount`: 500.00
- `max_amount`: 5000.00

#### Get Order Statistics

```http
GET /api/v1/restaurants/{restaurant_id}/orders/statistics
```

**Query Parameters:**

- `period`: today|week|month|year
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31

**Response:**

```json
{
  "success": true,
  "data": {
    "total_orders": 450,
    "total_revenue": 125000.0,
    "average_order_value": 277.78,
    "orders_by_type": {
      "dine_in": 280,
      "takeaway": 120,
      "delivery": 50
    },
    "orders_by_status": {
      "completed": 430,
      "cancelled": 20
    },
    "peak_hours": [
      { "hour": "12", "orders": 45 },
      { "hour": "13", "orders": 52 }
    ],
    "popular_items": [
      {
        "item_id": "uuid",
        "item_name": "Chicken Burger",
        "quantity_sold": 180,
        "revenue": 81000.0
      }
    ]
  }
}
```

---

## Table & Reservation Management

### Table Management

#### Get Tables

```http
GET /api/v1/restaurants/{restaurant_id}/tables
```

**Query Parameters:**

- `status`: available|occupied|reserved|cleaning|out_of_order
- `capacity_min`: 2
- `capacity_max`: 8
- `location`: main_floor|private_room|outdoor
- `features`: window_view,high_chair,wheelchair_accessible

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "number": 5,
      "name": "Table 5",
      "capacity": 4,
      "status": "available",
      "location": "main_floor",
      "features": ["window_view"],
      "current_order_id": null,
      "current_reservation_id": null,
      "last_cleaned_at": "2024-01-15T09:00:00Z",
      "coordinates": {
        "x": 150,
        "y": 200
      }
    }
  ]
}
```

#### Update Table Status

```http
PUT /api/v1/restaurants/{restaurant_id}/tables/{table_id}/status
```

**Request Body:**

```json
{
  "status": "occupied",
  "order_id": "uuid",
  "updated_by": "uuid"
}
```

#### Create Table

```http
POST /api/v1/restaurants/{restaurant_id}/tables
```

**Request Body:**

```json
{
  "number": 15,
  "name": "VIP Table 1",
  "capacity": 6,
  "location": "private_room",
  "features": ["window_view", "high_chair"],
  "coordinates": {
    "x": 300,
    "y": 150
  }
}
```

### Reservation Management

#### Create Reservation

```http
POST /api/v1/restaurants/{restaurant_id}/reservations
```

**Request Body:**

```json
{
  "customer_name": "John Doe",
  "customer_phone": "+254700000000",
  "customer_email": "john@example.com",
  "party_size": 4,
  "reservation_date": "2024-01-20",
  "reservation_time": "19:30",
  "duration_minutes": 120,
  "table_preference": "window_view",
  "special_requests": "Birthday celebration",
  "deposit_required": true,
  "deposit_amount": 1000.0,
  "created_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "reservation_id": "uuid",
    "reservation_number": "RSV-2024-001",
    "status": "confirmed",
    "table_id": "uuid",
    "table_number": 5,
    "confirmation_code": "ABC123",
    "deposit_paid": false,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

#### Get Reservations

```http
GET /api/v1/restaurants/{restaurant_id}/reservations
```

**Query Parameters:**

- `date`: 2024-01-20
- `status`: pending|confirmed|seated|cancelled|no_show
- `table_id`: uuid
- `customer_phone`: +254700000000

#### Update Reservation

```http
PUT /api/v1/restaurants/{restaurant_id}/reservations/{reservation_id}
```

**Request Body:**

```json
{
  "party_size": 6,
  "special_requests": "Updated special requests",
  "updated_by": "uuid"
}
```

#### Cancel Reservation

```http
PUT /api/v1/restaurants/{restaurant_id}/reservations/{reservation_id}/cancel
```

**Request Body:**

```json
{
  "reason": "customer_request|no_show",
  "notes": "Customer called to cancel",
  "refund_deposit": true,
  "cancelled_by": "uuid"
}
```

---

## Staff & Shift Management

### Staff Management

#### Get Staff

```http
GET /api/v1/restaurants/{restaurant_id}/staff
```

**Query Parameters:**

- `role`: waiter|manager|chef|cashier|delivery_driver
- `status`: active|inactive|suspended
- `search`: name or email
- `page`: 1
- `limit`: 20

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "first_name": "Alice",
      "last_name": "Johnson",
      "email": "alice@restaurant.com",
      "phone": "+254700000000",
      "role": "waiter",
      "status": "active",
      "pin_code": "1234",
      "permissions": ["orders.create", "orders.update"],
      "hourly_rate": 250.0,
      "avatar_url": "https://cdn.dukaverse.com/staff/uuid/avatar.jpg",
      "last_login": "2024-01-15T09:00:00Z",
      "created_at": "2024-01-01T00:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 25,
    "pages": 2
  }
}
```

#### Create Staff Member

```http
POST /api/v1/restaurants/{restaurant_id}/staff
```

**Request Body:**

```json
{
  "first_name": "Bob",
  "last_name": "Smith",
  "email": "bob@restaurant.com",
  "phone": "+254711111111",
  "role": "chef",
  "permissions": ["orders.view", "menu.view", "kitchen.update"],
  "hourly_rate": 400.0,
  "emergency_contact": {
    "name": "Jane Smith",
    "phone": "+254722222222",
    "relationship": "wife"
  }
}
```

#### Update Staff Member

```http
PUT /api/v1/restaurants/{restaurant_id}/staff/{staff_id}
```

#### Deactivate Staff Member

```http
PUT /api/v1/restaurants/{restaurant_id}/staff/{staff_id}/deactivate
```

**Request Body:**

```json
{
  "reason": "resignation|end_of_contract|termination",
  "notes
```
