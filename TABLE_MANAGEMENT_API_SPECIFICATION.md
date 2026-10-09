# Table Management API Specification

## Overview

This document provides comprehensive API specifications for the Restaurant Table Management system. These APIs support table layout management, real-time status updates, reservations, floor plan visualization, and table operations.

## Base URL

```
https://api.dukaverse.com/api/v1
```

## Authentication

All endpoints require JWT authentication:

```
Authorization: Bearer <jwt_token>
X-Restaurant-ID: <restaurant_id>
Content-Type: application/json
```

---

## Table of Contents

1. [Table Management](#table-management)
2. [Table Status Operations](#table-status-operations)
3. [Floor Management](#floor-management)
4. [Reservation Management](#reservation-management)
5. [Table Analytics](#table-analytics)
6. [Bulk Operations](#bulk-operations)
7. [Real-time Updates](#real-time-updates)

---

## 1. Table Management

### 1.1 Get All Tables

Retrieve all tables for a restaurant with filtering and pagination.

```http
GET /restaurants/{restaurant_id}/tables
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| floor | string | No | Filter by floor (main, upstairs, outdoor) |
| status | string | No | Filter by status (available, occupied, reserved, cleaning) |
| capacity | integer | No | Filter by capacity |
| section | string | No | Filter by section (window, center, corner, bar, private) |
| page | integer | No | Page number (default: 1) |
| limit | integer | No | Items per page (default: 20) |
| sort_by | string | No | Sort field (number, capacity, status) |
| sort_order | string | No | Sort order (asc, desc) |

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "number": 1,
      "capacity": 4,
      "section": "window",
      "floor": "main",
      "status": "available",
      "position": {
        "x": 20,
        "y": 30
      },
      "description": "Near window with city view",
      "shape": "square",
      "features": ["window_view", "power_outlet"],
      "current_order": null,
      "occupied_time": null,
      "last_cleaned_at": "2024-01-15T08:00:00Z",
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    },
    {
      "id": "uuid",
      "number": 2,
      "capacity": 2,
      "section": "bar",
      "floor": "main",
      "status": "occupied",
      "position": {
        "x": 70,
        "y": 20
      },
      "description": "Bar area seating",
      "shape": "round",
      "features": ["bar_seating"],
      "current_order": {
        "id": "uuid",
        "order_number": "ORD-001",
        "total_amount": 1250.00,
        "items_count": 3,
        "started_at": "2024-01-15T10:00:00Z"
      },
      "occupied_time": "45 min",
      "occupied_since": "2024-01-15T10:00:00Z",
      "waiter_assigned": {
        "id": "uuid",
        "name": "Alice Johnson"
      },
      "last_cleaned_at": "2024-01-15T09:30:00Z",
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 45,
    "pages": 3
  },
  "statistics": {
    "total_tables": 45,
    "available": 28,
    "occupied": 12,
    "reserved": 3,
    "cleaning": 2,
    "occupancy_rate": 33.33
  }
}
```

### 1.2 Get Single Table

Retrieve detailed information about a specific table.

```http
GET /restaurants/{restaurant_id}/tables/{table_id}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "number": 5,
    "capacity": 6,
    "section": "center",
    "floor": "main",
    "status": "occupied",
    "position": {
      "x": 50,
      "y": 60
    },
    "description": "Large table in center area",
    "shape": "rectangular",
    "dimensions": {
      "width": 120,
      "height": 80
    },
    "features": ["high_chair_available", "wheelchair_accessible"],
    "current_order": {
      "id": "uuid",
      "order_number": "ORD-125",
      "customer_name": "John Doe",
      "customer_phone": "+254700000000",
      "total_amount": 3450.00,
      "items_count": 8,
      "started_at": "2024-01-15T11:30:00Z",
      "estimated_completion": "2024-01-15T12:45:00Z",
      "items": [
        {
          "id": "uuid",
          "name": "Chicken Burger",
          "quantity": 2,
          "status": "preparing"
        }
      ]
    },
    "occupied_time": "1 hour 15 min",
    "occupied_since": "2024-01-15T10:00:00Z",
    "waiter_assigned": {
      "id": "uuid",
      "name": "Alice Johnson",
      "phone": "+254711111111"
    },
    "reservation": null,
    "maintenance_notes": [],
    "last_cleaned_at": "2024-01-15T09:45:00Z",
    "cleaning_history": [
      {
        "cleaned_at": "2024-01-15T09:45:00Z",
        "cleaned_by": "uuid",
        "cleaner_name": "Mary Kamau"
      }
    ],
    "usage_statistics": {
      "total_orders_today": 5,
      "total_revenue_today": 12500.00,
      "average_occupancy_time": "1 hour 20 min",
      "turnover_rate": 4.2
    },
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z"
  }
}
```

### 1.3 Create Table

Add a new table to the restaurant.

```http
POST /restaurants/{restaurant_id}/tables
```

**Request Body:**

```json
{
  "number": 15,
  "capacity": 4,
  "section": "window",
  "floor": "main",
  "position": {
    "x": 50,
    "y": 50
  },
  "description": "Window table with garden view",
  "shape": "square",
  "dimensions": {
    "width": 100,
    "height": 100
  },
  "features": ["window_view", "power_outlet"],
  "is_active": true
}
```

**Validation Rules:**

- `number`: Required, integer, unique per restaurant
- `capacity`: Required, integer, min: 1, max: 20
- `section`: Required, enum: [window, center, corner, bar, private]
- `floor`: Required, enum: [main, upstairs, outdoor, basement]
- `position.x`: Required, integer, 0-100 (percentage)
- `position.y`: Required, integer, 0-100 (percentage)
- `shape`: Optional, enum: [square, round, rectangular, oval]

**Response:**

```json
{
  "success": true,
  "message": "Table created successfully",
  "data": {
    "id": "uuid",
    "number": 15,
    "capacity": 4,
    "section": "window",
    "floor": "main",
    "status": "available",
    "position": {
      "x": 50,
      "y": 50
    },
    "description": "Window table with garden view",
    "shape": "square",
    "features": ["window_view", "power_outlet"],
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

### 1.4 Update Table

Update table information.

```http
PUT /restaurants/{restaurant_id}/tables/{table_id}
```

**Request Body:**

```json
{
  "number": 15,
  "capacity": 6,
  "section": "window",
  "floor": "main",
  "position": {
    "x": 55,
    "y": 45
  },
  "description": "Updated description",
  "shape": "rectangular",
  "features": ["window_view", "power_outlet", "high_chair_available"]
}
```

**Response:**

```json
{
  "success": true,
  "message": "Table updated successfully",
  "data": {
    "id": "uuid",
    "number": 15,
    "capacity": 6,
    "section": "window",
    "floor": "main",
    "status": "available",
    "position": {
      "x": 55,
      "y": 45
    },
    "description": "Updated description",
    "updated_at": "2024-01-15T11:00:00Z"
  }
}
```

### 1.5 Delete Table

Remove a table from the system.

```http
DELETE /restaurants/{restaurant_id}/tables/{table_id}
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| force | boolean | No | Force delete even if table has history |

**Response:**

```json
{
  "success": true,
  "message": "Table deleted successfully"
}
```

**Error Response (if table is occupied):**

```json
{
  "success": false,
  "error": {
    "code": "TABLE_OCCUPIED",
    "message": "Cannot delete table that is currently occupied",
    "details": {
      "table_id": "uuid",
      "status": "occupied",
      "current_order_id": "uuid"
    }
  }
}
```

---

## 2. Table Status Operations

### 2.1 Update Table Status

Change the status of a table.

```http
PUT /restaurants/{restaurant_id}/tables/{table_id}/status
```

**Request Body:**

```json
{
  "status": "occupied",
  "order_id": "uuid",
  "waiter_id": "uuid",
  "notes": "Party of 4 seated",
  "updated_by": "uuid"
}
```

**Status Values:**

- `available`: Table is ready for customers
- `occupied`: Table has customers
- `reserved`: Table is reserved for future booking
- `cleaning`: Table is being cleaned
- `out_of_order`: Table is unavailable due to maintenance

**Response:**

```json
{
  "success": true,
  "message": "Table status updated successfully",
  "data": {
    "id": "uuid",
    "number": 5,
    "status": "occupied",
    "previous_status": "available",
    "order_id": "uuid",
    "waiter_assigned": {
      "id": "uuid",
      "name": "Alice Johnson"
    },
    "occupied_since": "2024-01-15T12:00:00Z",
    "updated_at": "2024-01-15T12:00:00Z"
  }
}
```

### 2.2 Mark Table as Occupied

Quick endpoint to mark a table as occupied and create an order.

```http
POST /restaurants/{restaurant_id}/tables/{table_id}/occupy
```

**Request Body:**

```json
{
  "customer_name": "John Doe",
  "customer_phone": "+254700000000",
  "party_size": 4,
  "waiter_id": "uuid",
  "notes": "Birthday celebration"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Table marked as occupied",
  "data": {
    "table": {
      "id": "uuid",
      "number": 5,
      "status": "occupied",
      "occupied_since": "2024-01-15T12:00:00Z"
    },
    "order": {
      "id": "uuid",
      "order_number": "ORD-156",
      "status": "pending",
      "created_at": "2024-01-15T12:00:00Z"
    }
  }
}
```

### 2.3 Mark Table as Available

Clear a table and mark it as available.

```http
POST /restaurants/{restaurant_id}/tables/{table_id}/clear
```

**Request Body:**

```json
{
  "order_id": "uuid",
  "payment_completed": true,
  "requires_cleaning": true,
  "notes": "Customer left satisfied",
  "cleared_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Table cleared successfully",
  "data": {
    "id": "uuid",
    "number": 5,
    "status": "cleaning",
    "previous_status": "occupied",
    "occupied_duration": "1 hour 25 min",
    "order_summary": {
      "order_id": "uuid",
      "order_number": "ORD-156",
      "total_amount": 3450.00,
      "payment_status": "completed"
    },
    "cleared_at": "2024-01-15T13:25:00Z"
  }
}
```

### 2.4 Mark Table as Cleaned

Update table status after cleaning.

```http
POST /restaurants/{restaurant_id}/tables/{table_id}/cleaned
```

**Request Body:**

```json
{
  "cleaned_by": "uuid",
  "cleaning_notes": "Deep cleaned and sanitized",
  "issues_found": []
}
```

**Response:**

```json
{
  "success": true,
  "message": "Table marked as cleaned",
  "data": {
    "id": "uuid",
    "number": 5,
    "status": "available",
    "last_cleaned_at": "2024-01-15T13:30:00Z",
    "cleaned_by": {
      "id": "uuid",
      "name": "Mary Kamau"
    }
  }
}
```

### 2.5 Get Table Status History

Retrieve status change history for a table.

```http
GET /restaurants/{restaurant_id}/tables/{table_id}/status-history
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date_from | date | No | Start date (YYYY-MM-DD) |
| date_to | date | No | End date (YYYY-MM-DD) |
| limit | integer | No | Number of records (default: 50) |

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "table_id": "uuid",
      "previous_status": "available",
      "new_status": "occupied",
      "order_id": "uuid",
      "changed_by": {
        "id": "uuid",
        "name": "Alice Johnson"
      },
      "notes": "Party of 4 seated",
      "changed_at": "2024-01-15T12:00:00Z"
    },
    {
      "id": "uuid",
      "table_id": "uuid",
      "previous_status": "occupied",
      "new_status": "cleaning",
      "order_id": "uuid",
      "changed_by": {
        "id": "uuid",
        "name": "Bob Smith"
      },
      "notes": "Order completed, table needs cleaning",
      "changed_at": "2024-01-15T13:25:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 50,
    "total": 125,
    "pages": 3
  }
}
```

---

## 3. Floor Management

### 3.1 Get Floor Layout

Retrieve complete floor layout with all tables.

```http
GET /restaurants/{restaurant_id}/floors/{floor_name}/layout
```

**Path Parameters:**

- `floor_name`: main | upstairs | outdoor | basement

**Response:**

```json
{
  "success": true,
  "data": {
    "floor_name": "main",
    "display_name": "Main Floor",
    "dimensions": {
      "width": 1000,
      "height": 800
    },
    "elements": {
      "entrance": {
        "type": "entrance",
        "position": {
          "x": 50,
          "y": 5
        },
        "label": "Main Entrance"
      },
      "kitchen": {
        "type": "kitchen",
        "position": {
          "x": 90,
          "y": 90
        },
        "label": "Kitchen"
      },
      "bar": {
        "type": "bar",
        "position": {
          "x": 10,
          "y": 50
        },
        "label": "Bar Area"
      },
      "restrooms": {
        "type": "restroom",
        "position": {
          "x": 5,
          "y": 90
        },
        "label": "Restrooms"
      }
    },
    "tables": [
      {
        "id": "uuid",
        "number": 1,
        "capacity": 4,
        "status": "available",
        "position": {
          "x": 20,
          "y": 30
        },
        "shape": "square",
        "rotation": 0
      }
    ],
    "statistics": {
      "total_tables": 25,
      "total_capacity": 98,
      "available_tables": 18,
      "occupied_tables": 5,
      "reserved_tables": 2
    }
  }
}
```

### 3.2 Get All Floors

List all floors in the restaurant.

```http
GET /restaurants/{restaurant_id}/floors
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "name": "main",
      "display_name": "Main Floor",
      "table_count": 25,
      "total_capacity": 98,
      "is_active": true,
      "order": 1
    },
    {
      "name": "upstairs",
      "display_name": "Upstairs",
      "table_count": 15,
      "total_capacity": 60,
      "is_active": true,
      "order": 2
    },
    {
      "name": "outdoor",
      "display_name": "Outdoor Patio",
      "table_count": 10,
      "total_capacity": 40,
      "is_active": true,
      "order": 3
    }
  ]
}
```

### 3.3 Update Floor Layout

Update floor configuration and table positions.

```http
PUT /restaurants/{restaurant_id}/floors/{floor_name}/layout
```

**Request Body:**

```json
{
  "dimensions": {
    "width": 1000,
    "height": 800
  },
  "elements": {
    "entrance": {
      "position": {
        "x": 50,
        "y": 5
      }
    },
    "kitchen": {
      "position": {
        "x": 90,
        "y": 90
      }
    }
  },
  "table_positions": [
    {
      "table_id": "uuid",
      "position": {
        "x": 25,
        "y": 35
      },
      "rotation": 45
    }
  ]
}
```

**Response:**

```json
{
  "success": true,
  "message": "Floor layout updated successfully",
  "data": {
    "floor_name": "main",
    "updated_tables": 25,
    "updated_at": "2024-01-15T14:00:00Z"
  }
}
```

### 3.4 Export Floor Plan

Export floor plan as image or PDF.

```http
GET /restaurants/{restaurant_id}/floors/{floor_name}/export
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| format | string | Yes | Export format (png, pdf, svg) |
| include_status | boolean | No | Include table status colors |
| scale | number | No | Scale factor (default: 1.0) |

**Response:**

```json
{
  "success": true,
  "data": {
    "download_url": "https://cdn.dukaverse.com/exports/floor-plan-uuid.pdf",
    "expires_at": "2024-01-15T15:00:00Z",
    "file_size": 245678,
    "format": "pdf"
  }
}
```

---

## 4. Reservation Management

### 4.1 Create Reservation

Create a table reservation.

```http
POST /restaurants/{restaurant_id}/reservations
```

**Request Body:**

```json
{
  "customer_name": "Jane Doe",
  "customer_phone": "+254700000000",
  "customer_email": "jane@example.com",
  "party_size": 4,
  "reservation_date": "2024-01-20",
  "reservation_time": "19:30",
  "duration_minutes": 120,
  "table_id": "uuid",
  "table_preference": {
    "section": "window",
    "features": ["window_view"]
  },
  "special_requests": "Birthday celebration, need high chair",
  "deposit_required": true,
  "deposit_amount": 1000.00,
  "notification_preferences": {
    "sms": true,
    "email": true,
    "reminder_hours_before": 2
  },
  "created_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Reservation created successfully",
  "data": {
    "id": "uuid",
    "reservation_number": "RSV-2024-001",
    "confirmation_code": "ABC123",
    "status": "confirmed",
    "customer": {
      "name": "Jane Doe",
      "phone": "+254700000000",
      "email": "jane@example.com"
    },
    "party_size": 4,
    "reservation_datetime": "2024-01-20T19:30:00Z",
    "duration_minutes": 120,
    "table": {
      "id": "uuid",
      "number": 5,
      "capacity": 4,
      "section": "window"
    },
    "special_requests": "Birthday celebration, need high chair",
    "deposit": {
      "required": true,
      "amount": 1000.00,
      "paid": false,
      "payment_deadline": "2024-01-19T19:30:00Z"
    },
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### 4.2 Get Reservations

Retrieve reservations with filtering.

```http
GET /restaurants/{restaurant_id}/reservations
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | No | Filter by date (YYYY-MM-DD) |
| date_from | date | No | Start date range |
| date_to | date | No | End date range |
| status | string | No | Filter by status |
| table_id | uuid | No | Filter by table |
| customer_phone | string | No | Search by phone |
| page | integer | No | Page number |
| limit | integer | No | Items per page |

**Status Values:**

- `pending`: Awaiting confirmation
- `confirmed`: Reservation confirmed
- `seated`: Customer has arrived and been seated
- `completed`: Reservation completed
- `cancelled`: Reservation cancelled
- `no_show`: Customer didn't show up

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "reservation_number": "RSV-2024-001",
      "confirmation_code": "ABC123",
      "status": "confirmed",
      "customer": {
        "name": "Jane Doe",
        "phone": "+254700000000",
        "email": "jane@example.com"
      },
      "party_size": 4,
      "reservation_datetime": "2024-01-20T19:30:00Z",
      "duration_minutes": 120,
      "table": {
        "id": "uuid",
        "number": 5,
        "section": "window"
      },
      "special_requests": "Birthday celebration",
      "deposit": {
        "required": true,
        "amount": 1000.00,
        "paid": true,
        "paid_at": "2024-01-15T11:00:00Z"
      },
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 45,
    "pages": 3
  }
}
```

### 4.3 Update Reservation

Update reservation details.

```http
PUT /restaurants/{restaurant_id}/reservations/{reservation_id}
```

**Request Body:**

```json
{
  "party_size": 6,
  "reservation_time": "20:00",
  "table_id": "uuid",
  "special_requests": "Updated special requests",
  "updated_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Reservation updated successfully",
  "data": {
    "id": "uuid",
    "reservation_number": "RSV-2024-001",
    "party_size": 6,
    "reservation_datetime": "2024-01-20T20:00:00Z",
    "table": {
      "id": "uuid",
      "number": 8,
      "capacity": 6
    },
    "updated_at": "2024-01-15T11:30:00Z"
  }
}
```

### 4.4 Cancel Reservation

Cancel a reservation.

```http
PUT /restaurants/{restaurant_id}/reservations/{reservation_id}/cancel
```

**Request Body:**

```json
{
  "reason": "customer_request",
  "notes": "Customer called to cancel due to emergency",
  "refund_deposit": true,
  "cancelled_by": "uuid"
}
```

**Cancellation Reasons:**

- `customer_request`: Customer requested cancellation
- `no_show`: Customer didn't show up
- `restaurant_closed`: Restaurant closed unexpectedly
- `overbooking`: Restaurant overbooked
- `other`: Other reason

**Response:**

```json
{
  "success": true,
  "message": "Reservation cancelled successfully",
  "data": {
    "id": "uuid",
    "reservation_number": "RSV-2024-001",
    "status": "cancelled",
    "cancellation": {
      "reason": "customer_request",
      "notes": "Customer called to cancel due to emergency",
      "refund_issued": true,
      "refund_amount": 1000.00,
      "cancelled_by": {
        "id": "uuid",
        "name": "Alice Johnson"
      },
      "cancelled_at": "2024-01-15T12:00:00Z"
    }
  }
}
```

### 4.5 Check-in Reservation

Mark reservation as seated when customer arrives.

```http
POST /restaurants/{restaurant_id}/reservations/{reservation_id}/checkin
```

**Request Body:**

```json
{
  "actual_party_size": 4,
  "table_id": "uuid",
  "waiter_id": "uuid",
  "notes": "Customer arrived on time",
  "checked_in_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Reservation checked in successfully",
  "data": {
    "reservation": {
      "id": "uuid",
      "status": "seated",
      "checked_in_at": "2024-01-20T19:30:00Z"
    },
    "table": {
      "id": "uuid",
      "number": 5,
      "status": "occupied"
    },
    "order": {
      "id": "uuid",
      "order_number": "ORD-245",
      "created_at": "2024-01-20T19:30:00Z"
    }
  }
}
```

### 4.6 Get Reservation Availability

Check table availability for a specific date and time.

```http
GET /restaurants/{restaurant_id}/reservations/availability
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | Yes | Reservation date (YYYY-MM-DD) |
| time | time | Yes | Reservation time (HH:MM) |
| party_size | integer | Yes | Number of guests |
| duration_minutes | integer | No | Expected duration (default: 120) |

**Response:**

```json
{
  "success": true,
  "data": {
    "is_available": true,
    "available_tables": [
      {
        "id": "uuid",
        "number": 5,
        "capacity": 4,
        "section": "window",
        "features": ["window_view"],
        "next_reservation": "2024-01-20T22:00:00Z"
      },
      {
        "id": "uuid",
        "number": 8,
        "capacity": 6,
        "section": "center",
        "features": [],
        "next_reservation": null
      }
    ],
    "alternative_times": [
      {
        "time": "19:00",
        "available_tables": 3
      },
      {
        "time": "20:00",
        "available_tables": 5
      }
    ]
  }
}
```

---

## 5. Table Analytics

### 5.1 Get Table Statistics

Retrieve comprehensive table statistics.

```http
GET /restaurants/{restaurant_id}/tables/statistics
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| period | string | No | Time period (today, week, month, year) |
| date_from | date | No | Start date (YYYY-MM-DD) |
| date_to | date | No | End date (YYYY-MM-DD) |
| floor | string | No | Filter by floor |

**Response:**

```json
{
  "success": true,
  "data": {
    "overview": {
      "total_tables": 45,
      "total_capacity": 180,
      "current_occupancy": {
        "occupied_tables": 12,
        "occupied_seats": 45,
        "occupancy_rate": 26.67,
        "seat_occupancy_rate": 25.00
      }
    },
    "performance": {
      "total_orders": 450,
      "total_revenue": 125000.00,
      "average_order_value": 277.78,
      "average_table_turnover": 4.2,
      "average_occupancy_time": "1 hour 25 min",
      "peak_occupancy_rate": 85.5
    },
    "by_status": {
      "available": 28,
      "occupied": 12,
      "reserved": 3,
      "cleaning": 2,
      "out_of_order": 0
    },
    "by_floor": [
      {
        "floor": "main",
        "total_tables": 25,
        "occupied": 8,
        "occupancy_rate": 32.0,
        "revenue": 75000.00
      },
      {
        "floor": "upstairs",
        "total_tables": 15,
        "occupied": 3,
        "occupancy_rate": 20.0,
        "revenue": 35000.00
      },
      {
        "floor": "outdoor",
        "total_tables": 5,
        "occupied": 1,
        "occupancy_rate": 20.0,
        "revenue": 15000.00
      }
    ],
    "by_section": [
      {
        "section": "window",
        "table_count": 12,
        "occupancy_rate": 45.5,
        "average_revenue_per_table": 3200.00
      },
      {
        "section": "center",
        "table_count": 18,
        "occupancy_rate": 28.3,
        "average_revenue_per_table": 2800.00
      }
    ],
    "peak_hours": [
      {
        "hour": "12:00",
        "occupancy_rate": 75.5,
        "orders": 45
      },
      {
        "hour": "13:00",
        "occupancy_rate": 82.2,
        "orders": 52
      },
      {
        "hour": "19:00",
        "occupancy_rate": 88.9,
        "orders": 48
      }
    ],
    "top_performing_tables": [
      {
        "table_id": "uuid",
        "table_number": 5,
        "total_orders": 25,
        "total_revenue": 8500.00,
        "turnover_rate": 5.2,
        "average_occupancy_time": "1 hour 15 min"
      }
    ]
  }
}
```

### 5.2 Get Table Performance

Get detailed performance metrics for a specific table.

```http
GET /restaurants/{restaurant_id}/tables/{table_id}/performance
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| period | string | No | Time period (today, week, month, year) |
| date_from | date | No | Start date (YYYY-MM-DD) |
| date_to | date | No | End date (YYYY-MM-DD) |

**Response:**

```json
{
  "success": true,
  "data": {
    "table": {
      "id": "uuid",
      "number": 5,
      "capacity": 4,
      "section": "window"
    },
    "period": {
      "start": "2024-01-01",
      "end": "2024-01-31"
    },
    "metrics": {
      "total_orders": 125,
      "total_revenue": 42500.00,
      "average_order_value": 340.00,
      "total_customers_served": 480,
      "turnover_rate": 4.8,
      "average_occupancy_time": "1 hour 20 min",
      "utilization_rate": 68.5,
      "peak_day": "Saturday",
      "peak_time": "19:00-20:00"
    },
    "daily_breakdown": [
      {
        "date": "2024-01-15",
        "orders": 5,
        "revenue": 1750.00,
        "customers": 18,
        "occupancy_hours": 6.5
      }
    ],
    "popular_items": [
      {
        "item_id": "uuid",
        "item_name": "Chicken Burger",
        "quantity_sold": 45,
        "revenue": 20250.00
      }
    ]
  }
}
```

### 5.3 Get Occupancy Heatmap

Get occupancy data for heatmap visualization.

```http
GET /restaurants/{restaurant_id}/tables/occupancy-heatmap
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | Yes | Date for heatmap (YYYY-MM-DD) |
| floor | string | No | Filter by floor |

**Response:**

```json
{
  "success": true,
  "data": {
    "date": "2024-01-15",
    "hourly_data": [
      {
        "hour": "08:00",
        "occupancy_rate": 15.5,
        "occupied_tables": 7,
        "revenue": 2500.00
      },
      {
        "hour": "12:00",
        "occupancy_rate": 75.5,
        "occupied_tables": 34,
        "revenue": 12500.00
      },
      {
        "hour": "19:00",
        "occupancy_rate": 88.9,
        "occupied_tables": 40,
        "revenue": 18500.00
      }
    ],
    "table_heatmap": [
      {
        "table_id": "uuid",
        "table_number": 5,
        "occupancy_percentage": 85.5,
        "total_hours_occupied": 10.5,
        "number_of_sittings": 6
      }
    ]
  }
}
```

---

## 6. Bulk Operations

### 6.1 Bulk Update Tables

Update multiple tables at once.

```http
PUT /restaurants/{restaurant_id}/tables/bulk-update
```

**Request Body:**

```json
{
  "updates": [
    {
      "table_id": "uuid",
      "capacity": 6,
      "section": "window"
    },
    {
      "table_id": "uuid",
      "status": "out_of_order",
      "notes": "Broken chair, needs repair"
    }
  ],
  "updated_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Bulk update completed",
  "data": {
    "updated_count": 2,
    "failed_count": 0,
    "results": [
      {
        "table_id": "uuid",
        "table_number": 5,
        "status": "success",
        "message": "Table updated successfully"
      },
      {
        "table_id": "uuid",
        "table_number": 8,
        "status": "success",
        "message": "Table updated successfully"
      }
    ]
  }
}
```

### 6.2 Bulk Status Change

Change status of multiple tables.

```http
PUT /restaurants/{restaurant_id}/tables/bulk-status
```

**Request Body:**

```json
{
  "table_ids": ["uuid1", "uuid2", "uuid3"],
  "status": "cleaning",
  "notes": "End of day cleaning",
  "updated_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Status updated for 3 tables",
  "data": {
    "updated_tables": [
      {
        "table_id": "uuid1",
        "table_number": 5,
        "previous_status": "available",
        "new_status": "cleaning"
      },
      {
        "table_id": "uuid2",
        "table_number": 8,
        "previous_status": "available",
        "new_status": "cleaning"
      }
    ]
  }
}
```

### 6.3 Export Tables Data

Export table data in various formats.

```http
GET /restaurants/{restaurant_id}/tables/export
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| format | string | Yes | Export format (csv, excel, pdf) |
| include_statistics | boolean | No | Include performance statistics |
| date_from | date | No | Start date for statistics |
| date_to | date | No | End date for statistics |

**Response:**

```json
{
  "success": true,
  "data": {
    "download_url": "https://cdn.dukaverse.com/exports/tables-uuid.xlsx",
    "expires_at": "2024-01-15T15:00:00Z",
    "file_size": 125678,
    "format": "excel",
    "record_count": 45
  }
}
```

---

## 7. Real-time Updates

### 7.1 WebSocket Connection

Connect to real-time table updates via WebSocket.

**Connection URL:**

```
wss://api.dukaverse.com/ws/restaurants/{restaurant_id}/tables
```

**Authentication:**

```javascript
{
  "type": "auth",
  "token": "jwt_token_here"
}
```

### 7.2 Subscribe to Table Updates

Subscribe to specific table or all tables.

**Subscribe Message:**

```javascript
{
  "type": "subscribe",
  "channel": "tables",
  "filters": {
    "table_ids": ["uuid1", "uuid2"],  // Optional: specific tables
    "floors": ["main", "upstairs"],    // Optional: specific floors
    "statuses": ["occupied", "available"] // Optional: specific statuses
  }
}
```

### 7.3 Real-time Events

**Table Status Changed:**

```javascript
{
  "type": "table.status_changed",
  "timestamp": "2024-01-15T12:00:00Z",
  "data": {
    "table_id": "uuid",
    "table_number": 5,
    "previous_status": "available",
    "new_status": "occupied",
    "order_id": "uuid",
    "waiter": {
      "id": "uuid",
      "name": "Alice Johnson"
    }
  }
}
```

**Table Occupied:**

```javascript
{
  "type": "table.occupied",
  "timestamp": "2024-01-15T12:00:00Z",
  "data": {
    "table_id": "uuid",
    "table_number": 5,
    "order_id": "uuid",
    "order_number": "ORD-156",
    "party_size": 4,
    "waiter": {
      "id": "uuid",
      "name": "Alice Johnson"
    }
  }
}
```

**Table Cleared:**

```javascript
{
  "type": "table.cleared",
  "timestamp": "2024-01-15T13:25:00Z",
  "data": {
    "table_id": "uuid",
    "table_number": 5,
    "order_id": "uuid",
    "occupied_duration": "1 hour 25 min",
    "total_amount": 3450.00,
    "requires_cleaning": true
  }
}
```

**Reservation Created:**

```javascript
{
  "type": "reservation.created",
  "timestamp": "2024-01-15T10:30:00Z",
  "data": {
    "reservation_id": "uuid",
    "reservation_number": "RSV-2024-001",
    "table_id": "uuid",
    "table_number": 5,
    "customer_name": "Jane Doe",
    "party_size": 4,
    "reservation_datetime": "2024-01-20T19:30:00Z"
  }
}
```

**Reservation Checked In:**

```javascript
{
  "type": "reservation.checked_in",
  "timestamp": "2024-01-20T19:30:00Z",
  "data": {
    "reservation_id": "uuid",
    "table_id": "uuid",
    "table_number": 5,
    "order_id": "uuid",
    "customer_name": "Jane Doe"
  }
}
```

---

## 8. Additional Endpoints

### 8.1 Quick Actions

#### Quick Reserve

Create a quick reservation for walk-in customers.

```http
POST /restaurants/{restaurant_id}/tables/quick-reserve
```

**Request Body:**

```json
{
  "customer_name": "John Doe",
  "customer_phone": "+254700000000",
  "party_size": 4,
  "preferred_section": "window",
  "notes": "Walk-in customer",
  "created_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Table reserved successfully",
  "data": {
    "reservation_id": "uuid",
    "table": {
      "id": "uuid",
      "number": 5,
      "section": "window"
    },
    "estimated_wait_time": 15
  }
}
```

#### Maintenance Mode

Mark table for maintenance.

```http
POST /restaurants/{restaurant_id}/tables/{table_id}/maintenance
```

**Request Body:**

```json
{
  "issue_type": "broken_furniture|electrical|plumbing|cleaning|other",
  "description": "Broken chair needs replacement",
  "priority": "low|medium|high|urgent",
  "estimated_repair_time": 60,
  "reported_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Table marked for maintenance",
  "data": {
    "table_id": "uuid",
    "table_number": 5,
    "status": "out_of_order",
    "maintenance_ticket": {
      "id": "uuid",
      "ticket_number": "MAINT-001",
      "issue_type": "broken_furniture",
      "priority": "high",
      "created_at": "2024-01-15T14:00:00Z"
    }
  }
}
```

### 8.2 Table Combinations

#### Combine Tables

Combine multiple tables for large parties.

```http
POST /restaurants/{restaurant_id}/tables/combine
```

**Request Body:**

```json
{
  "table_ids": ["uuid1", "uuid2", "uuid3"],
  "combined_name": "Large Party Table",
  "party_size": 12,
  "order_id": "uuid",
  "notes": "Wedding party",
  "created_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Tables combined successfully",
  "data": {
    "combination_id": "uuid",
    "combined_name": "Large Party Table",
    "tables": [
      {
        "id": "uuid1",
        "number": 5
      },
      {
        "id": "uuid2",
        "number": 6
      },
      {
        "id": "uuid3",
        "number": 7
      }
    ],
    "total_capacity": 12,
    "status": "occupied"
  }
}
```

#### Split Combined Tables

Split previously combined tables.

```http
POST /restaurants/{restaurant_id}/tables/combinations/{combination_id}/split
```

**Request Body:**

```json
{
  "split_by": "uuid",
  "notes": "Party finished, splitting tables"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Tables split successfully",
  "data": {
    "tables": [
      {
        "id": "uuid1",
        "number": 5,
        "status": "cleaning"
      },
      {
        "id": "uuid2",
        "number": 6,
        "status": "cleaning"
      }
    ]
  }
}
```

### 8.3 Waitlist Management

#### Add to Waitlist

Add customer to waiting list when no tables available.

```http
POST /restaurants/{restaurant_id}/tables/waitlist
```

**Request Body:**

```json
{
  "customer_name": "Jane Doe",
  "customer_phone": "+254700000000",
  "party_size": 4,
  "preferred_section": "window",
  "notes": "Celebrating anniversary",
  "notify_via": ["sms", "call"]
}
```

**Response:**

```json
{
  "success": true,
  "message": "Added to waitlist",
  "data": {
    "waitlist_id": "uuid",
    "position": 3,
    "estimated_wait_time": 25,
    "party_size": 4,
    "created_at": "2024-01-15T12:30:00Z"
  }
}
```

#### Get Waitlist

Retrieve current waitlist.

```http
GET /restaurants/{restaurant_id}/tables/waitlist
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "position": 1,
      "customer_name": "John Doe",
      "customer_phone": "+254700000000",
      "party_size": 2,
      "estimated_wait_time": 10,
      "added_at": "2024-01-15T12:15:00Z",
      "status": "waiting"
    },
    {
      "id": "uuid",
      "position": 2,
      "customer_name": "Jane Smith",
      "customer_phone": "+254711111111",
      "party_size": 4,
      "estimated_wait_time": 20,
      "added_at": "2024-01-15T12:20:00Z",
      "status": "waiting"
    }
  ],
  "total_waiting": 5
}
```

#### Notify Waitlist Customer

Notify customer when table is ready.

```http
POST /restaurants/{restaurant_id}/tables/waitlist/{waitlist_id}/notify
```

**Request Body:**

```json
{
  "table_id": "uuid",
  "notification_method": "sms|call|both",
  "message": "Your table is ready. Please proceed to the host stand.",
  "notified_by": "uuid"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Customer notified successfully",
  "data": {
    "waitlist_id": "uuid",
    "customer_name": "John Doe",
    "table_number": 5,
    "notification_sent": true,
    "notification_method": "sms",
    "notified_at": "2024-01-15T12:45:00Z"
  }
}
```

---

## 9. Error Handling

### Error Response Format

All error responses follow this format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message",
    "details": {
      "field": "Additional error details"
    },
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `TABLE_NOT_FOUND` | 404 | Table does not exist |
| `TABLE_OCCUPIED` | 409 | Table is currently occupied |
| `TABLE_RESERVED` | 409 | Table is reserved |
| `INVALID_STATUS_TRANSITION` | 400 | Invalid status change |
| `RESERVATION_NOT_FOUND` | 404 | Reservation does not exist |
| `RESERVATION_CONFLICT` | 409 | Time slot already booked |
| `INVALID_PARTY_SIZE` | 400 | Party size exceeds table capacity |
| `INVALID_DATE_TIME` | 400 | Invalid date or time format |
| `UNAUTHORIZED` | 401 | Authentication required |
| `FORBIDDEN` | 403 | Insufficient permissions |
| `VALIDATION_ERROR` | 422 | Request validation failed |
| `RATE_LIMIT_EXCEEDED` | 429 | Too many requests |
| `INTERNAL_SERVER_ERROR` | 500 | Server error |

### Example Error Responses

**Table Not Found:**

```json
{
  "success": false,
  "error": {
    "code": "TABLE_NOT_FOUND",
    "message": "Table with ID 'uuid' does not exist",
    "details": {
      "table_id": "uuid",
      "restaurant_id": "uuid"
    },
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

**Table Occupied:**

```json
{
  "success": false,
  "error": {
    "code": "TABLE_OCCUPIED",
    "message": "Table 5 is currently occupied and cannot be modified",
    "details": {
      "table_id": "uuid",
      "table_number": 5,
      "current_status": "occupied",
      "order_id": "uuid",
      "occupied_since": "2024-01-15T10:00:00Z"
    },
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

**Validation Error:**

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Request validation failed",
    "details": {
      "capacity": ["Capacity must be between 1 and 20"],
      "section": ["Section must be one of: window, center, corner, bar, private"]
    },
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

---

## 10. Rate Limiting

API requests are rate limited to ensure fair usage:

- **Standard tier**: 1000 requests per hour
- **Premium tier**: 5000 requests per hour
- **Enterprise tier**: Unlimited

Rate limit headers are included in all responses:

```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 950
X-RateLimit-Reset: 1642248000
```

---

## 11. Pagination

List endpoints support pagination with the following parameters:

- `page`: Page number (default: 1)
- `limit`: Items per page (default: 20, max: 100)

Pagination metadata is included in responses:

```json
{
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "pages": 8,
    "has_next": true,
    "has_prev": false
  }
}
```

---

## 12. Webhooks

Configure webhooks to receive real-time notifications about table events.

### Webhook Events

- `table.status_changed`: Table status updated
- `table.occupied`: Table marked as occupied
- `table.cleared`: Table cleared and available
- `reservation.created`: New reservation created
- `reservation.updated`: Reservation modified
- `reservation.cancelled`: Reservation cancelled
- `reservation.checked_in`: Customer checked in
- `waitlist.added`: Customer added to waitlist
- `waitlist.notified`: Waitlist customer notified

### Webhook Payload Example

```json
{
  "event": "table.occupied",
  "timestamp": "2024-01-15T12:00:00Z",
  "restaurant_id": "uuid",
  "data": {
    "table_id": "uuid",
    "table_number": 5,
    "order_id": "uuid",
    "order_number": "ORD-156",
    "party_size": 4,
    "waiter": {
      "id": "uuid",
      "name": "Alice Johnson"
    }
  }
}
```

---

## 13. Best Practices

### 1. Status Management

- Always check table status before attempting to modify
- Use appropriate status transitions (available → occupied → cleaning → available)
- Handle concurrent updates with optimistic locking

### 2. Reservations

- Validate party size against table capacity
- Check availability before confirming reservations
- Send confirmation notifications immediately
- Set up reminder notifications

### 3. Real-time Updates

- Use WebSocket connections for live table status
- Implement reconnection logic for dropped connections
- Handle offline scenarios gracefully

### 4. Performance

- Cache frequently accessed data (floor layouts, table lists)
- Use pagination for large datasets
- Implement debouncing for status updates
- Optimize floor plan rendering

### 5. Error Handling

- Implement retry logic for transient failures
- Provide meaningful error messages to users
- Log errors for debugging
- Handle network failures gracefully

---

## 14. Testing

### Test Credentials

**Sandbox Environment:**

```
Base URL: https://sandbox-api.dukaverse.com/api/v1
Restaurant ID: test-restaurant-uuid
API Key: test_api_key_12345
```

### Sample Test Scenarios

1. **Create and occupy table**
2. **Create reservation and check-in**
3. **Combine tables for large party**
4. **Handle waitlist flow**
5. **Update floor layout**
6. **Export table statistics**

---

## 15. Support

For API support and questions:

- **Email**: api-support@dukaverse.com
- **Documentation**: https://docs.dukaverse.com
- **Status Page**: https://status.dukaverse.com
- **Developer Portal**: https://developers.dukaverse.com

---

## Changelog

### Version 1.0.0 (2024-01-15)

- Initial release
- Complete table management endpoints
- Reservation system
- Floor plan management
- Real-time WebSocket support
- Analytics and reporting
- Waitlist management

---

## Appendix

### A. Table Status Flow

```
available → occupied → cleaning → available
available → reserved → occupied → cleaning → available
available → out_of_order → available
```

### B. Reservation Status Flow

```
pending → confirmed → seated → completed
pending → cancelled
confirmed → no_show
```

### C. Supported Time Zones

All timestamps are in UTC. The API supports the following time zones for display:

- Africa/Nairobi (EAT)
- Africa/Lagos (WAT)
- Africa/Johannesburg (SAST)
- UTC

### D. Supported Currencies

- KES (Kenyan Shilling)
- USD (US Dollar)
- EUR (Euro)
- GBP (British Pound)

---

**End of Documentation**
