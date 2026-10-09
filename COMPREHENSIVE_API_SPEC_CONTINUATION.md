{
"reason": "resignation|end_of_contract|termination",
"notes": "Staff member resigned to pursue other opportunities",
"final_date": "2024-01-31",
"deactivated_by": "uuid"
}

````

### Shift Management

#### Get Shifts
```http
GET /api/v1/restaurants/{restaurant_id}/shifts
````

**Query Parameters:**

- `staff_id`: uuid
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31
- `status`: scheduled|completed|cancelled

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "staff_id": "uuid",
      "staff_name": "Alice Johnson",
      "date": "2024-01-15",
      "start_time": "09:00",
      "end_time": "17:00",
      "break_start": "12:00",
      "break_end": "13:00",
      "role": "waiter",
      "location": "main_restaurant",
      "status": "scheduled",
      "notes": "Training new staff member",
      "created_by": "uuid",
      "created_at": "2024-01-10T10:00:00Z"
    }
  ]
}
```

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

#### Update Shift

```http
PUT /api/v1/restaurants/{restaurant_id}/shifts/{shift_id}
```

#### Delete Shift

```http
DELETE /api/v1/restaurants/{restaurant_id}/shifts/{shift_id}
```

#### Clock In/Out

```http
POST /api/v1/restaurants/{restaurant_id}/shifts/{shift_id}/clock-in
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

```http
POST /api/v1/restaurants/{restaurant_id}/shifts/{shift_id}/clock-out
```

**Request Body:**

```json
{
  "notes": "Completed shift successfully",
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

**Query Parameters:**

- `period`: week|month|quarter|year
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31

**Response:**

```json
{
  "success": true,
  "data": {
    "staff_id": "uuid",
    "staff_name": "Alice Johnson",
    "period": "month",
    "metrics": {
      "total_shifts": 20,
      "total_hours": 160,
      "average_rating": 4.8,
      "orders_served": 450,
      "average_order_value": 285.0,
      "tips_earned": 13500.0,
      "punctuality_score": 95,
      "customer_complaints": 2
    },
    "performance_trends": [
      {
        "week": "2024-W03",
        "rating": 4.7,
        "orders_served": 110,
        "tips": 3300.0
      }
    ]
  }
}
```

#### Get Attendance Records

```http
GET /api/v1/restaurants/{restaurant_id}/staff/{staff_id}/attendance
```

**Query Parameters:**

- `date_from`: 2024-01-01
- `date_to`: 2024-01-31

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "shift_id": "uuid",
      "date": "2024-01-15",
      "scheduled_start": "09:00",
      "scheduled_end": "17:00",
      "actual_start": "09:02",
      "actual_end": "17:05",
      "break_duration": 60,
      "status": "completed",
      "notes": "Arrived 2 minutes late due to traffic"
    }
  ]
}
```

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

## Payment & Financial Management

### Payment Processing

#### Process Payment

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
  "reference_number": "TXN-2024-001",
  "processed_by": "uuid",
  "notes": "Payment received successfully"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "payment_id": "uuid",
    "transaction_id": "TXN-2024-001",
    "status": "completed",
    "amount": 1122.0,
    "tip_amount": 50.0,
    "total_amount": 1172.0,
    "payment_method": "split",
    "processed_at": "2024-01-15T10:35:00Z",
    "receipt_url": "https://api.dukaverse.com/receipts/uuid.pdf"
  }
}
```

#### Get Payment Details

```http
GET /api/v1/restaurants/{restaurant_id}/payments/{payment_id}
```

#### Refund Payment

```http
POST /api/v1/restaurants/{restaurant_id}/payments/{payment_id}/refund
```

**Request Body:**

```json
{
  "amount": 450.0,
  "reason": "food_not_satisfactory|wrong_order|customer_request",
  "notes": "Customer complained about cold food",
  "refund_method": "original|cash|credit_note",
  "processed_by": "uuid"
}
```

### Payment Methods Configuration

#### Get Payment Methods

```http
GET /api/v1/restaurants/{restaurant_id}/payment-methods
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "method": "cash",
      "name": "Cash",
      "is_active": true,
      "requires_confirmation": false,
      "processing_fee": 0.0
    },
    {
      "method": "mpesa",
      "name": "M-PESA",
      "is_active": true,
      "requires_confirmation": true,
      "processing_fee": 0.5,
      "config": {
        "shortcode": "123456",
        "passkey": "your_passkey",
        "callback_url": "https://api.dukaverse.com/payments/mpesa/callback"
      }
    },
    {
      "method": "card",
      "name": "Credit/Debit Card",
      "is_active": true,
      "requires_confirmation": false,
      "processing_fee": 2.5,
      "config": {
        "gateway": "pesapal",
        "merchant_id": "your_merchant_id",
        "api_key": "your_api_key"
      }
    }
  ]
}
```

#### Update Payment Method

```http
PUT /api/v1/restaurants/{restaurant_id}/payment-methods/{method}
```

### Financial Reports

#### Daily Sales Summary

```http
GET /api/v1/restaurants/{restaurant_id}/reports/financial/daily
```

**Query Parameters:**

- `date`: 2024-01-15

**Response:**

```json
{
  "success": true,
  "data": {
    "date": "2024-01-15",
    "summary": {
      "total_sales": 125000.0,
      "total_orders": 450,
      "total_payments": 118000.0,
      "total_tips": 7000.0,
      "total_tax": 15000.0,
      "total_service_charge": 2500.0,
      "total_discounts": 3750.0,
      "net_revenue": 109750.0
    },
    "payment_methods": {
      "cash": 45000.0,
      "mpesa": 35000.0,
      "card": 45000.0
    },
    "hourly_breakdown": [
      { "hour": "10", "sales": 8500.0, "orders": 32 },
      { "hour": "11", "sales": 9200.0, "orders": 35 }
    ]
  }
}
```

#### Profit & Loss Report

```http
GET /api/v1/restaurants/{restaurant_id}/reports/financial/pnl
```

**Query Parameters:**

- `start_date`: 2024-01-01
- `end_date`: 2024-01-31

**Response:**

```json
{
  "success": true,
  "data": {
    "revenue": {
      "food_sales": 1250000.0,
      "beverage_sales": 250000.0,
      "delivery_fees": 75000.0,
      "total_revenue": 1525000.0
    },
    "cost_of_goods_sold": {
      "food_cost": 625000.0,
      "beverage_cost": 125000.0,
      "total_cogs": 750000.0
    },
    "operating_expenses": {
      "staff_salaries": 300000.0,
      "rent": 150000.0,
      "utilities": 50000.0,
      "marketing": 25000.0,
      "total_opex": 525000.0
    },
    "gross_profit": 775000.0,
    "net_profit": 250000.0,
    "profit_margin": 16.4
  }
}
```

---

## Customer Relationship Management

### Customer Profiles

#### Get Customers

```http
GET /api/v1/restaurants/{restaurant_id}/customers
```

**Query Parameters:**

- `search`: name, phone, or email
- `segment`: new|returning|vip|high_value|at_risk
- `last_visit_from`: 2024-01-01
- `last_visit_to`: 2024-01-31
- `page`: 1
- `limit`: 20

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Jane Doe",
      "email": "jane@example.com",
      "phone": "+254700000000",
      "date_of_birth": "1990-05-15",
      "gender": "female",
      "loyalty_points": 1250,
      "loyalty_tier": "gold",
      "total_orders": 45,
      "total_spent": 22500.0,
      "average_order_value": 500.0,
      "last_visit": "2024-01-10T19:30:00Z",
      "preferred_order_type": "dine_in",
      "favorite_items": ["Chicken Burger", "Caesar Salad"],
      "dietary_restrictions": ["vegetarian"],
      "allergies": ["nuts"],
      "special_occasions": [
        {
          "date": "1990-05-15",
          "type": "birthday",
          "reminder_sent": false
        }
      ],
      "tags": ["regular", "high_value", "birthday_month"],
      "created_at": "2023-06-01T00:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 1250,
    "pages": 63
  }
}
```

#### Create/Update Customer

```http
POST /api/v1/restaurants/{restaurant_id}/customers
PUT /api/v1/restaurants/{restaurant_id}/customers/{customer_id}
```

**Request Body:**

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "+254700000000",
  "date_of_birth": "1990-05-15",
  "gender": "female",
  "dietary_restrictions": ["vegetarian"],
  "allergies": ["nuts"],
  "tags": ["regular", "high_value"]
}
```

#### Get Customer Order History

```http
GET /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/orders
```

**Query Parameters:**

- `page`: 1
- `limit`: 10
- `status`: completed|cancelled

### Loyalty Program

#### Get Loyalty Tiers

```http
GET /api/v1/restaurants/{restaurant_id}/loyalty/tiers
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "tier": "bronze",
      "min_points": 0,
      "max_points": 999,
      "benefits": {
        "discount_percentage": 5,
        "birthday_bonus": 100,
        "points_multiplier": 1.0
      }
    },
    {
      "tier": "gold",
      "min_points": 1000,
      "max_points": 4999,
      "benefits": {
        "discount_percentage": 10,
        "birthday_bonus": 250,
        "points_multiplier": 1.5,
        "free_item_threshold": 2000.0
      }
    }
  ]
}
```

#### Award Loyalty Points

```http
POST /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/points
```

**Request Body:**

```json
{
  "points": 50,
  "reason": "order_completion|birthday|referral|manual",
  "order_id": "uuid",
  "notes": "Bonus points for large order"
}
```

#### Redeem Loyalty Points

```http
POST /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/points/redeem
```

**Request Body:**

```json
{
  "points": 200,
  "discount_amount": 200.0,
  "order_id": "uuid"
}
```

### Customer Feedback

#### Submit Feedback

```http
POST /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/feedback
```

**Request Body:**

```json
{
  "order_id": "uuid",
  "rating": 4,
  "food_rating": 5,
  "service_rating": 4,
  "ambiance_rating": 4,
  "value_rating": 3,
  "comments": "Food was excellent but service was a bit slow",
  "would_recommend": true,
  "tags": ["food_quality", "service_speed"]
}
```

#### Get Customer Feedback

```http
GET /api/v1/restaurants/{restaurant_id}/customers/{customer_id}/feedback
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "order_id": "uuid",
      "order_number": "ORD-2024-001",
      "rating": 4,
      "food_rating": 5,
      "service_rating": 4,
      "comments": "Food was excellent but service was a bit slow",
      "created_at": "2024-01-15T20:30:00Z",
      "response": {
        "message": "Thank you for your feedback. We're working to improve our service speed.",
        "responded_by": "uuid",
        "responded_at": "2024-01-15T21:00:00Z"
      }
    }
  ]
}
```

---

## Delivery Management System

### Delivery Orders Management

#### Create Delivery Order

```http
POST /api/v1/delivery/orders
```

**Request Body:**

```json
{
  "restaurant_id": "uuid",
  "order_type": "restaurant|ecommerce|pickup",
  "priority": "normal|urgent|express",
  "customer": {
    "name": "Jane Doe",
    "phone": "+254700000000",
    "email": "jane@example.com"
  },
  "pickup_location": {
    "name": "DukaVerse Restaurant",
    "address": "123 Main St, Nairobi",
    "latitude": -1.2864,
    "longitude": 36.8172,
    "contact_person": "John Doe",
    "contact_phone": "+254700000000",
    "instructions": "Ring bell twice"
  },
  "delivery_location": {
    "address": "456 Oak Avenue, Nairobi",
    "latitude": -1.2864,
    "longitude": 36.8172,
    "building": "Apartment 5B",
    "landmark": "Near shopping center",
    "contact_person": "Jane Doe",
    "contact_phone": "+254700000000",
    "instructions": "Call upon arrival"
  },
  "items": [
    {
      "name": "Chicken Burger",
      "quantity": 2,
      "price": 450.0,
      "description": "With extra cheese",
      "category": "main_course",
      "requires_cooling": false,
      "fragile": false
    }
  ],
  "pricing": {
    "subtotal": 900.0,
    "tax_amount": 144.0,
    "delivery_fee": 150.0,
    "service_fee": 20.0,
    "discount_amount": 0.0,
    "total_amount": 1214.0
  },
  "payment": {
    "method": "cash|mpesa|card|pay_on_delivery",
    "status": "pending|paid|failed",
    "transaction_id": "MPESA-123456"
  },
  "special_instructions": "Handle with care - hot food",
  "estimated_preparation_time": 20,
  "estimated_delivery_time": 35,
  "scheduled_delivery_time": "2024-01-15T12:00:00Z"
}
```

#### Get Delivery Orders

```http
GET /api/v1/delivery/orders
```

**Query Parameters:**

- `restaurant_id`: uuid
- `status`: pending|assigned|picked_up|in_transit|delivered|cancelled|failed
- `rider_id`: uuid
- `priority`: normal|urgent|express
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31
- `page`: 1
- `limit`: 20

#### Update Order Status

```http
PUT /api/v1/delivery/orders/{order_id}/status
```

**Request Body:**

```json
{
  "status": "confirmed|picked_up|in_transit|delivered|cancelled|failed",
  "notes": "Order picked up successfully",
  "location": {
    "latitude": -1.2864,
    "longitude": 36.8172
  },
  "updated_by": "uuid"
}
```

### Rider Management

#### Rider Registration & Profiles

```http
POST /api/v1/delivery/riders
GET /api/v1/delivery/riders/{rider_id}
PUT /api/v1/delivery/riders/{rider_id}
DELETE /api/v1/delivery/riders/{rider_id}
```

**Rider Profile Schema:**

```json
{
  "user_id": "uuid",
  "personal_info": {
    "first_name": "Mike",
    "last_name": "Johnson",
    "email": "mike@rider.com",
    "phone": "+254700000000",
    "date_of_birth": "1995-03-15",
    "national_id": "12345678",
    "emergency_contact": {
      "name": "Jane Johnson",
      "phone": "+254711000000",
      "relationship": "sister"
    }
  },
  "vehicle_info": {
    "type": "motorcycle|bicycle|car|scooter",
    "make": "Honda",
    "model": "CB300R",
    "year": 2020,
    "color": "Red",
    "license_plate": "KCB 123A",
    "insurance_expiry": "2024-12-31",
    "registration_expiry": "2024-06-30"
  },
  "documents": {
    "drivers_license": "https://cdn.dukaverse.com/riders/uuid/drivers_license.jpg",
    "national_id": "https://cdn.dukaverse.com/riders/uuid/national_id.jpg",
    "vehicle_registration": "https://cdn.dukaverse.com/riders/uuid/vehicle_reg.jpg",
    "insurance": "https://cdn.dukaverse.com/riders/uuid/insurance.jpg",
    "police_clearance": "https://cdn.dukaverse.com/riders/uuid/police_clearance.jpg"
  },
  "banking_info": {
    "bank_name": "KCB Bank",
    "account_number": "1234567890",
    "account_name": "Mike Johnson",
    "mpesa_number": "+254700000000"
  },
  "working_preferences": {
    "working_hours": {
      "start": "08:00",
      "end": "20:00",
      "days": ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"]
    },
    "max_daily_orders": 25,
    "preferred_zones": ["westlands", "kilimani", "koinange"],
    "vehicle_capacity": "medium",
    "can_carry_fragile": true,
    "can_carry_hot_food": true
  },
  "performance_metrics": {
    "total_deliveries": 1250,
    "success_rate": 96.5,
    "average_rating": 4.8,
    "average_delivery_time": 28.5,
    "total_earnings": 375000.0
  },
  "status": "active|inactive|suspended|pending_verification",
  "verification_status": "pending|verified|rejected",
  "current_location": {
    "latitude": -1.2864,
    "longitude": 36.8172,
    "accuracy": 10.5,
    "updated_at": "2024-01-15T10:25:00Z"
  }
}
```

#### Rider Availability & Status

```http
GET /api/v1/delivery/riders/available
PUT /api/v1/delivery/riders/{rider_id}/status
```

**Query Parameters:**

- `location`: -1.2864,36.8172
- `radius`: 10
- `vehicle_type`: motorcycle|bicycle|car
- `can_carry_hot`: true|false

### Bidding System

#### Get Available Orders for Bidding

```http
GET /api/v1/delivery/orders/available
```

**Query Parameters:**

- `rider_id`: uuid
- `location`: -1.2864,36.8172
- `radius`: 10
- `vehicle_type`: motorcycle

#### Submit Bid

```http
POST /api/v1/delivery/orders/{order_id}/bids
```

**Request Body:**

```json
{
  "rider_id": "uuid",
  "bid_amount": 140.0,
  "estimated_pickup_time": 15,
  "estimated_delivery_time": 35,
  "notes": "I know this area well and can deliver quickly",
  "vehicle_type": "motorcycle",
  "special_equipment": ["thermal_bag", "phone_mount"]
}
```

#### Get Bids for Order

```http
GET /api/v1/delivery/orders/{order_id}/bids
```

#### Accept/Reject Bid

```http
PUT /api/v1/delivery/orders/{order_id}/bids/{bid_id}
```

**Request Body:**

```json
{
  "action": "accept|reject",
  "reason": "Best bid with good rating",
  "accepted_by": "auto|manual",
  "fee": 140.0
}
```

### GPS Tracking & Route Optimization

#### Update Rider Location

```http
POST /api/v1/delivery/riders/{rider_id}/location
```

**Request Body:**

```json
{
  "latitude": -1.2864,
  "longitude": 36.8172,
  "accuracy": 5.0,
  "speed": 25.0,
  "heading": 90.0,
  "altitude": 1600.0,
  "timestamp": "2024-01-15T10:30:00Z",
  "battery_level": 85,
  "network_type": "4G",
  "device_info": {
    "os": "Android",
    "version": "12.0",
    "app_version": "2.1.0"
  }
}
```

#### Get Delivery Tracking

```http
GET /api/v1/delivery/orders/{order_id}/tracking
```

**Query Parameters:**

- `include_history`: true|false
- `include_route`: true|false

**Response:**

```json
{
  "success": true,
  "data": {
    "order_id": "uuid",
    "rider_id": "uuid",
    "rider_name": "Mike Johnson",
    "rider_phone": "+254700000000",
    "rider_rating": 4.8,
    "vehicle_type": "motorcycle",
    "current_status": "in_transit",
    "current_location": {
      "latitude": -1.2864,
      "longitude": 36.8172,
      "timestamp": "2024-01-15T10:50:00Z",
      "accuracy": 5.0
    },
    "estimated_arrival": "2024-01-15T11:05:00Z",
    "route": {
      "distance_remaining": 2.5,
      "estimated_time": 12,
      "waypoints": [
        { "lat": -1.2864, "lng": 36.8172, "type": "current" },
        { "lat": -1.2864, "lng": 36.8172, "type": "destination" }
      ]
    },
    "timeline": [
      {
        "status": "assigned",
        "timestamp": "2024-01-15T10:35:00Z",
        "location": { "lat": -1.2864, "lng": 36.8172 },
        "notes": "Order assigned to rider"
      },
      {
        "status": "picked_up",
        "timestamp": "2024-01-15T10:45:00Z",
        "location": { "lat": -1.2864, "lng": 36.8172 },
        "notes": "Order picked up from restaurant"
      }
    ]
  }
}
```

### Delivery Analytics

#### Delivery Performance Report

```http
GET /api/v1/delivery/reports/performance
```

**Query Parameters:**

- `start_date`: 2024-01-01
- `end_date`: 2024-01-31
- `rider_id`: uuid
- `restaurant_id`: uuid

**Response:**

```json
{
  "success": true,
  "data": {
    "summary": {
      "total_deliveries": 1250,
      "successful_deliveries": 1180,
      "failed_deliveries": 15,
      "cancelled_deliveries": 55,
      "average_delivery_time": 28.5,
      "success_rate": 94.4,
      "total_revenue": 187500.0,
      "average_fee": 150.0
    },
    "breakdown": {
      "by_hour": [
        { "hour": "10", "orders": 45, "average_time": 25 },
        { "hour": "11", "orders": 52, "average_time": 22 }
      ],
      "by_rider": [
        {
          "rider_id": "uuid",
          "rider_name": "Mike Johnson",
          "deliveries": 180,
          "average_time": 24,
          "rating": 4.8
        }
      ],
      "by_restaurant": [
        {
          "restaurant_id": "uuid",
          "restaurant_name": "DukaVerse Restaurant",
          "deliveries": 320,
          "revenue": 48000.0
        }
      ]
    }
  }
}
```

### Customer Ratings & Reviews

#### Submit Delivery Rating

```http
POST /api/v1/delivery/orders/{order_id}/rating
```

**Request Body:**

```json
{
  "rating": 5,
  "rider_rating": 4,
  "food_rating": 5,
  "packaging_rating": 5,
  "comment": "Excellent service! Food arrived hot and on time.",
  "would_recommend": true,
  "tip_amount": 50.0,
  "tags": ["fast_delivery", "good_packaging", "courteous_rider"]
}
```

#### Get Rider Ratings

```http
GET /api/v1/delivery/riders/{rider_id}/ratings
```

**Query Parameters:**

- `page`: 1
- `limit`: 20
- `rating_min`: 1
- `rating_max`: 5

---

## Analytics & Reporting

### Sales Analytics

#### Comprehensive Sales Report

```http
GET /api/v1/restaurants/{restaurant_id}/reports/sales/comprehensive
```

**Query Parameters:**

- `start_date`: 2024-01-01
- `end_date`: 2024-01-31
- `group_by`: day|week|month
- `include_tax`: true|false
- `include_service_charge`: true|false
- `include_delivery`: true|false

**Response:**

```json
{
  "success": true,
  "data": {
    "period": {
      "start_date": "2024-01-01",
      "end_date": "2024-01-31",
      "days": 31
    },
    "summary": {
      "total_revenue": 1525000.0,
      "total_orders": 5420,
      "average_order_value": 281.37,
      "total_guests": 8750,
      "average_guests_per_order": 1.61
    },
    "revenue_breakdown": {
      "food_sales": 1250000.0,
      "beverage_sales": 250000.0,
      "delivery_fees": 25000.0,
      "total_revenue": 1525000.0
    },
    "order_breakdown": {
      "dine_in": { "orders": 3200, "revenue": 950000.0, "percentage": 59.0 },
      "takeaway": { "orders": 1520, "revenue": 425000.0, "percentage": 28.0 },
      "delivery": { "orders": 700, "revenue": 150000.0, "percentage": 13.0 }
    },
    "payment_methods": {
      "cash": { "amount": 650000.0, "transactions": 2800, "percentage": 42.6 },
      "mpesa": { "amount": 525000.0, "transactions": 1950, "percentage": 34.4 },
      "card": { "amount": 350000.0, "transactions": 670, "percentage": 23.0 }
    },
    "popular_items": [
      {
        "item_id": "uuid",
        "name": "Chicken Burger",
        "category": "Main Courses",
        "quantity_sold": 1250,
        "revenue": 562500.0,
        "popularity_rank": 1,
        "average_rating": 4.6
      }
    ],
    "peak_hours": [
      {
        "hour": "12:00-13:00",
        "orders": 450,
        "revenue": 125000.0,
        "average_order_value": 277.78
      }
    ],
    "trends": {
      "daily_comparison": {
        "current_period": 1525000.0,
        "previous_period": 1450000.0,
        "growth_percentage": 5.17
      },
      "monthly_trend": [
        { "month": "2024-01", "revenue": 1525000.0 },
        { "month": "2024-02", "revenue": 1580000.0 }
      ]
    }
  }
}
```

### Customer Analytics

#### Customer Segmentation Report

```http
GET /api/v1/restaurants/{restaurant_id}/reports/customers/segmentation
```

**Response:**

```json
{
  "success": true,
  "data": {
    "total_customers": 1250,
    "segments": {
      "new_customers": {
        "count": 180,
        "percentage": 14.4,
        "average_order_value": 320.00,
        "total_spent": 57600.00
      },
      "returning_customers": {
        "count": 850,
        "percentage": 68.0,
        "average_order_value": 450.00,
        "total_spent": 382500.00,
        "average_visits_per_month": 3.2
      },
      "vip_customers": {
        "count": 220,
        "percentage": 17.6,
        "average_order_value": 850.00,
        "total_spent": 187000.00,
        "loyalty_tier": "gold"
      }
    },
    "customer_lifetime_value": {
      "average_clv": 1250.00,
      "cl
```
