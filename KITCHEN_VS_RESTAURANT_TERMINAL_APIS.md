# Kitchen APIs vs Restaurant Terminal APIs

## Overview

This document separates the comprehensive restaurant and delivery API specifications into two distinct categories:

1. **Kitchen APIs** - Used by kitchen staff and cooking stations for order preparation and kitchen management
2. **Restaurant Terminal APIs** - Used by restaurant staff (waiters, managers, cashiers) for order management, customer service, and business operations

## Kitchen APIs

### Purpose
Kitchen APIs are designed for kitchen display systems (KDS), cooking stations, and kitchen staff to manage food preparation workflows, track order status, and optimize kitchen operations.

### Target Users
- Chefs and kitchen staff
- Kitchen display systems
- Food preparation stations
- Kitchen managers

### Key Features
- Real-time order display
- Item status updates
- Station management
- Performance tracking
- Preparation time monitoring

---

## Kitchen Display System

### Kitchen Orders

#### Get Kitchen Orders

```http
GET /api/v1/restaurants/{restaurant_id}/kitchen/orders
```

**Query Parameters:**
- `status`: pending|preparing|ready|completed
- `station`: grill|salad|dessert|beverages
- `priority`: normal|urgent|rush
- `page`: 1
- `limit`: 50

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "order_id": "uuid",
      "order_number": "ORD-2024-001",
      "order_type": "dine_in",
      "table_number": 5,
      "customer_name": "Jane Doe",
      "items": [
        {
          "id": "uuid",
          "name": "Chicken Burger",
          "quantity": 2,
          "customizations": ["Extra Cheese", "No Onions"],
          "special_instructions": "Well done",
          "status": "pending",
          "station": "grill",
          "estimated_time": 15,
          "priority": "normal",
          "assigned_to": "uuid",
          "started_at": null,
          "completed_at": null
        }
      ],
      "priority": "normal",
      "created_at": "2024-01-15T10:30:00Z",
      "estimated_completion": "2024-01-15T10:45:00Z"
    }
  ]
}
```

#### Update Item Status

```http
PUT /api/v1/restaurants/{restaurant_id}/kitchen/orders/{order_id}/items/{item_id}/status
```

**Request Body:**
```json
{
  "status": "preparing|ready|completed",
  "station": "grill",
  "assigned_to": "uuid",
  "notes": "Cooking at medium heat"
}
```

#### Get Kitchen Stations

```http
GET /api/v1/restaurants/{restaurant_id}/kitchen/stations
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Grill Station",
      "code": "grill",
      "capacity": 10,
      "current_load": 7,
      "status": "active",
      "assigned_staff": ["uuid1", "uuid2"],
      "equipment": ["grill", "fryer", "oven"],
      "specializations": ["burgers", "steaks", "chicken"]
    }
  ]
}
```

#### Kitchen Performance Metrics

```http
GET /api/v1/restaurants/{restaurant_id}/kitchen/performance
```

**Query Parameters:**
- `period`: today|week|month
- `station`: grill|salad|all

**Response:**
```json
{
  "success": true,
  "data": {
    "average_preparation_time": 18.5,
    "on_time_completion_rate": 92.5,
    "rush_order_completion_rate": 88.0,
    "peak_hour_performance": {
      "12:00-13:00": {
        "orders_completed": 25,
        "average_time": 22.0,
        "on_time_rate": 88.0
      }
    },
    "station_performance": [
      {
        "station": "grill",
        "items_completed": 145,
        "average_time": 16.5,
        "efficiency_score": 94.0
      }
    ]
  }
}
```

---

## Restaurant Terminal APIs

### Purpose
Restaurant Terminal APIs are designed for restaurant staff using POS terminals, tablets, and management systems to handle customer orders, payments, reservations, and daily operations.

### Target Users
- Waiters and servers
- Restaurant managers
- Cashiers
- Hostess staff
- Bartenders

### Key Features
- Order creation and management
- Table and reservation management
- Payment processing
- Customer management
- Staff scheduling
- Sales reporting

---

## Staff Management

### Shift Management

#### Get Shifts
```http
GET /api/v1/restaurants/{restaurant_id}/shifts
```

**Query Parameters:**
- `staff_id`: uuid
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31
- `status`: scheduled|completed|cancelled

#### Create Shift
```http
POST /api/v1/restaurants/{restaurant_id}/shifts
```

**Request Body:**
```json
{
  "staff_id": "uuid",
  "date": "2024-01-20",
  "start_time": "14:00",
  "end_time": "22:00",
  "break_start": "18:00",
  "break_end": "19:00",
  "role": "waiter",
  "location": "main_restaurant",
  "notes": "Evening shift coverage"
}
```

#### Clock In/Out
```http
POST /api/v1/restaurants/{restaurant_id}/shifts/{shift_id}/clock-in
POST /api/v1/restaurants/{restaurant_id}/shifts/{shift_id}/clock-out
```

**Request Body:**
```json
{
  "pin_code": "1234",
  "location": {
    "latitude": -1.2864,
    "longitude": 36.8172
  }
}
```

### Staff Performance & Attendance

#### Get Staff Performance
```http
GET /api/v1/restaurants/{restaurant_id}/staff/{staff_id}/performance
```

#### Get Attendance Records
```http
GET /api/v1/restaurants/{restaurant_id}/staff/{staff_id}/attendance
```

---

## Order Management

### Create Order
```http
POST /api/v1/restaurants/{restaurant_id}/orders
```

**Request Body:**
```json
{
  "order_type": "dine_in|takeaway|delivery",
  "table_id": "uuid",
  "customer_info": {
    "name": "Jane Doe",
    "phone": "+254700000000"
  },
  "items": [
    {
      "menu_item_id": "uuid",
      "quantity": 2,
      "customizations": [
        {"name": "Extra Cheese", "price": 50.0}
      ],
      "special_instructions": "No onions"
    }
  ],
  "special_instructions": "Window seat preferred"
}
```

### Get Orders
```http
GET /api/v1/restaurants/{restaurant_id}/orders
```

**Query Parameters:**
- `status`: pending|confirmed|preparing|ready|served|completed|cancelled
- `order_type`: dine_in|takeaway|delivery
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31
- `page`: 1
- `limit`: 20

### Update Order Status
```http
PUT /api/v1/restaurants/{restaurant_id}/orders/{order_id}/status
```

**Request Body:**
```json
{
  "status": "confirmed",
  "notes": "Order confirmed and sent to kitchen"
}
```

---

## Table & Reservation Management

### Get Tables
```http
GET /api/v1/restaurants/{restaurant_id}/tables
```

**Query Parameters:**
- `status`: available|occupied|reserved
- `capacity_min`: 2
- `capacity_max`: 8

### Update Table Status
```http
PUT /api/v1/restaurants/{restaurant_id}/tables/{table_id}/status
```

**Request Body:**
```json
{
  "status": "occupied",
  "order_id": "uuid"
}
```

### Create Reservation
```http
POST /api/v1/restaurants/{restaurant_id}/reservations
```

**Request Body:**
```json
{
  "customer_name": "John Doe",
  "customer_phone": "+254700000000",
  "party_size": 4,
  "reservation_date": "2024-01-20",
  "reservation_time": "19:30",
  "duration_minutes": 120,
  "special_requests": "Birthday celebration"
}
```

### Get Reservations
```http
GET /api/v1/restaurants/{restaurant_id}/reservations
```

**Query Parameters:**
- `date`: 2024-01-20
- `status`: pending|confirmed|seated|completed

---

## Payment Processing

### Process Payment
```http
POST /api/v1/restaurants/{restaurant_id}/payments
```

**Request Body:**
```json
{
  "order_id": "uuid",
  "amount": 1122.0,
  "payment_method": "cash|card|mpesa|bank_transfer|split",
  "split_details": [
    {
      "method": "cash",
      "amount": 600.0,
      "received_by": "uuid"
    },
    {
      "method": "mpesa",
      "amount": 522.0,
      "phone_number": "+254700000000",
      "transaction_id": "NFY8K3N4T5"
    }
  ],
  "tip_amount": 50.0,
  "reference_number": "TXN-2024-001"
}
```

### Get Payment Details
```http
GET /api/v1/restaurants/{restaurant_id}/payments/{payment_id}
```

### Refund Payment
```http
POST /api/v1/restaurants/{restaurant_id}/payments/{payment_id}/refund
```

---

## Customer Management

### Get Customers
```http
GET /api/v1/restaurants/{restaurant_id}/customers
```

**Query Parameters:**
- `search`: name, phone, or email
- `segment`: new|returning|vip|high_value|at_risk
- `page`: 1
- `limit`: 20

### Create/Update Customer
```http
POST /api/v1/restaurants/{restaurant_id}/customers
PUT /api/v1/restaurants/{restaurant_id}/customers/{customer_id}
```

### Loyalty Program
```http
GET /api/v1/restaurants/{restaurant_id}/loyalty/tiers
POST /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/points
POST /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/points/redeem
```

### Customer Feedback
```http
POST /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/feedback
GET /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/feedback
```

---

## Delivery Management (Restaurant Terminal)

### Create Delivery Order
```http
POST /api/v1/delivery/orders
```

**Request Body:**
```json
{
  "restaurant_id": "uuid",
  "customer": {
    "name": "Jane Doe",
    "phone": "+254700000000"
  },
  "delivery_location": {
    "address": "456 Oak Avenue, Nairobi",
    "latitude": -1.2864,
    "longitude": 36.8172
  },
  "items": [
    {
      "name": "Chicken Burger",
      "quantity": 2,
      "price": 450.0
    }
  ],
  "pricing": {
    "subtotal": 900.0,
    "delivery_fee": 150.0,
    "total_amount": 1050.0
  }
}
```

### Get Delivery Orders
```http
GET /api/v1/delivery/orders
```

**Query Parameters:**
- `restaurant_id`: uuid
- `status`: pending|assigned|picked_up|in_transit|delivered
- `page`: 1
- `limit`: 20

### Update Delivery Status
```http
PUT /api/v1/delivery/orders/{order_id}/status
```

---

## Analytics & Reporting

### Sales Reports
```http
GET /api/v1/restaurants/{restaurant_id}/reports/sales/comprehensive
```

**Query Parameters:**
- `start_date`: 2024-01-01
- `end_date`: 2024-01-31
- `group_by`: day|week|month

### Customer Analytics
```http
GET /api/v1/restaurants/{restaurant_id}/reports/customers/segmentation
```

### Financial Reports
```http
GET /api/v1/restaurants/{restaurant_id}/reports/financial/daily
GET /api/v1/restaurants/{restaurant_id}/reports/financial/pnl
```

---

## Implementation Considerations

### Kitchen APIs
- **Real-time Requirements**: WebSocket connections for live order updates
- **Performance Critical**: Must handle high-frequency status updates
- **Station-Specific**: Different endpoints for different kitchen stations
- **Mobile Friendly**: Optimized for kitchen tablets and displays

### Restaurant Terminal APIs
- **User-Friendly**: Designed for quick order entry and management
- **Payment Integration**: Secure payment processing capabilities
- **Customer Focus**: Customer profile and loyalty management
- **Reporting**: Business intelligence and analytics access

### Shared Infrastructure
- **Authentication**: JWT tokens with role-based access
- **Database**: Shared PostgreSQL with multi-tenant support
- **Caching**: Redis for performance optimization
- **Logging**: Comprehensive audit trails

### Security Considerations
- **Kitchen Access**: Restricted to kitchen staff only
- **Terminal Access**: Role-based permissions for restaurant staff
- **Payment Security**: PCI compliance for payment processing
- **Data Privacy**: Customer data protection and GDPR compliance

---

## Development Priorities

### Phase 1: Core Functionality
1. **Restaurant Terminal**: Order creation, table management, basic payments
2. **Kitchen Basic**: Order display, status updates
3. **Authentication**: Multi-role access control

### Phase 2: Advanced Features
1. **Kitchen Advanced**: Station management, performance metrics
2. **Restaurant Advanced**: Reservations, customer management, loyalty
3. **Delivery Integration**: Order coordination between systems

### Phase 3: Analytics & Optimization
1. **Reporting**: Comprehensive analytics for both systems
2. **Real-time**: WebSocket integration and live updates
3. **Performance**: Optimization and monitoring

This separation ensures that kitchen operations remain focused on food preparation while restaurant terminals handle customer service and business operations, creating a streamlined workflow for restaurant management.
