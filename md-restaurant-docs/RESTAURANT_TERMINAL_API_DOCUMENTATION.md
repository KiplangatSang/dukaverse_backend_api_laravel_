# Restaurant Terminal - API Documentation

## Overview

This document outlines all the backend APIs required for the Restaurant POS Terminal to function properly. The frontend is fully functional with mock data, but these APIs need to be implemented on the backend.

---

## Base URL

```
/api/v1/restaurant
```

---

## 1. Orders Management

### 1.1 Send Order to Kitchen (KOT - Kitchen Order Ticket)

**Endpoint:** `POST /orders/send-to-kitchen`

**Description:** Sends the current order to the kitchen for preparation.

**Request Body:**

```json
{
  "order": {
    "orderId": "string|null",
    "orderType": "dine-in|takeaway|delivery",
    "tableId": "number|null",
    "tableNumber": "string|null",
    "items": [
      {
        "id": "number",
        "name": "string",
        "price": "number",
        "quantity": "number",
        "specialInstructions": "string",
        "status": "pending"
      }
    ],
    "waiterId": "number"
  },
  "waiterId": "number"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "orderId": "ORD-12345",
    "status": "sent-to-kitchen",
    "kotNumber": "KOT-001",
    "estimatedTime": 25,
    "message": "Order sent to kitchen successfully"
  }
}
```

**Status Codes:**

- `200`: Success
- `400`: Invalid order data
- `404`: Table or waiter not found
- `500`: Server error

---

### 1.2 Process Payment

**Endpoint:** `POST /orders/process-payment`

**Description:** Processes payment for an order.

**Request Body:**

```json
{
  "orderId": "string",
  "paymentMethod": "cash|card|mpesa",
  "amount": "number",
  "amountReceived": "number",
  "change": "number"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "paymentId": "PAY-12345",
    "orderId": "ORD-12345",
    "status": "paid",
    "receiptNumber": "RCP-12345",
    "timestamp": "2024-01-15T10:30:00Z",
    "message": "Payment processed successfully"
  }
}
```

**Status Codes:**

- `200`: Success
- `400`: Invalid payment data
- `404`: Order not found
- `500`: Server error

---

### 1.3 Get Order by ID

**Endpoint:** `GET /orders/{orderId}`

**Description:** Retrieves a specific order by ID (used when loading table orders).

**Response:**

```json
{
  "success": true,
  "data": {
    "orderId": "ORD-12345",
    "orderType": "dine-in",
    "tableId": 2,
    "tableNumber": "2",
    "items": [
      {
        "id": 7,
        "name": "Grilled Chicken",
        "price": 850,
        "quantity": 2,
        "specialInstructions": "Extra spicy",
        "status": "preparing",
        "statusLabel": "Preparing"
      }
    ],
    "status": "sent-to-kitchen",
    "waiterId": 1,
    "createdAt": "2024-01-15T10:00:00Z",
    "subtotal": 1700,
    "tax": 272,
    "total": 1972
  }
}
```

**Status Codes:**

- `200`: Success
- `404`: Order not found
- `500`: Server error

---

### 1.4 Hold Order

**Endpoint:** `PUT /orders/{orderId}/hold`

**Description:** Puts an order on hold.

**Request Body:**

```json
{
  "reason": "string (optional)"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "orderId": "ORD-12345",
    "status": "on-hold",
    "message": "Order put on hold successfully"
  }
}
```

---

### 1.5 Resume Order

**Endpoint:** `PUT /orders/{orderId}/resume`

**Description:** Resumes an order that was on hold.

**Response:**

```json
{
  "success": true,
  "data": {
    "orderId": "ORD-12345",
    "status": "active",
    "message": "Order resumed successfully"
  }
}
```

---

### 1.6 Get Orders on Hold

**Endpoint:** `GET /orders/on-hold`

**Description:** Retrieves all orders currently on hold.

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "orderId": "ORD-12345",
      "orderType": "dine-in",
      "tableNumber": "5",
      "items": [...],
      "total": 1500,
      "createdAt": "2024-01-15T09:30:00Z"
    }
  ]
}
```

---

### 1.7 Update Order Status

**Endpoint:** `PUT /orders/{orderId}/status`

**Description:** Updates the status of an order (used by kitchen staff).

**Request Body:**

```json
{
  "status": "preparing|ready|served",
  "itemId": "number (optional - for updating specific item status)"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "orderId": "ORD-12345",
    "status": "ready",
    "message": "Order status updated successfully"
  }
}
```

---

## 2. Tables Management

### 2.1 Get All Tables

**Endpoint:** `GET /tables`

**Description:** Retrieves all restaurant tables with their current status.

**Query Parameters:**

- `status` (optional): Filter by status (available|occupied|reserved)

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "number": "1",
      "capacity": 2,
      "status": "available",
      "statusLabel": "Available",
      "orderId": null,
      "currentGuests": 0
    },
    {
      "id": 2,
      "number": "2",
      "capacity": 4,
      "status": "occupied",
      "statusLabel": "Occupied",
      "orderId": "ORD-001",
      "currentGuests": 3,
      "waiterName": "John Doe"
    }
  ]
}
```

---

### 2.2 Update Table Status

**Endpoint:** `PUT /tables/{tableId}/status`

**Description:** Updates the status of a table.

**Request Body:**

```json
{
  "status": "available|occupied|reserved",
  "orderId": "string|null",
  "guests": "number (optional)"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "tableId": 1,
    "status": "occupied",
    "message": "Table status updated successfully"
  }
}
```

---

### 2.3 Reserve Table

**Endpoint:** `POST /tables/{tableId}/reserve`

**Description:** Reserves a table for a specific time.

**Request Body:**

```json
{
  "customerName": "string",
  "customerPhone": "string",
  "guests": "number",
  "reservationTime": "ISO 8601 datetime",
  "duration": "number (minutes)"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "reservationId": "RES-12345",
    "tableId": 1,
    "tableNumber": "1",
    "message": "Table reserved successfully"
  }
}
```

---

## 3. Menu Management

### 3.1 Get Menu Categories

**Endpoint:** `GET /menu/categories`

**Description:** Retrieves all menu categories.

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "appetizers",
      "name": "Appetizers",
      "icon": "mdi mdi-food-apple",
      "itemCount": 6,
      "displayOrder": 1
    }
  ]
}
```

---

### 3.2 Get Menu Items

**Endpoint:** `GET /menu/items`

**Description:** Retrieves all menu items.

**Query Parameters:**

- `categoryId` (optional): Filter by category
- `search` (optional): Search by name or description
- `available` (optional): Filter by availability (true|false)

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "categoryId": "appetizers",
      "name": "Spring Rolls",
      "description": "Crispy vegetable spring rolls",
      "price": 350,
      "preparationTime": 10,
      "available": true,
      "image": "https://example.com/images/spring-rolls.jpg",
      "allergens": ["gluten"],
      "isVegetarian": true,
      "isVegan": false,
      "spicyLevel": 1
    }
  ]
}
```

---

### 3.3 Update Item Availability

**Endpoint:** `PUT /menu/items/{itemId}/availability`

**Description:** Updates the availability status of a menu item.

**Request Body:**

```json
{
  "available": "boolean",
  "reason": "string (optional)"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "itemId": 1,
    "available": false,
    "message": "Item availability updated successfully"
  }
}
```

---

## 4. Waiters Management

### 4.1 Get All Waiters

**Endpoint:** `GET /waiters`

**Description:** Retrieves all waiters/staff.

**Query Parameters:**

- `onDuty` (optional): Filter by duty status (true|false)

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "employeeId": "EMP-001",
      "activeOrders": 3,
      "onDuty": true,
      "shift": "morning",
      "avatar": "https://example.com/avatars/john.jpg"
    }
  ]
}
```

---

### 4.2 Get Waiter Performance

**Endpoint:** `GET /waiters/{waiterId}/performance`

**Description:** Retrieves performance metrics for a specific waiter.

**Query Parameters:**

- `startDate` (optional): Start date for metrics
- `endDate` (optional): End date for metrics

**Response:**

```json
{
  "success": true,
  "data": {
    "waiterId": 1,
    "name": "John Doe",
    "metrics": {
      "totalOrders": 45,
      "totalSales": 125000,
      "averageOrderValue": 2777.78,
      "customerRating": 4.5,
      "ordersCompleted": 43,
      "ordersCancelled": 2
    }
  }
}
```

---

## 5. Kitchen Display System (KDS)

### 5.1 Get Kitchen Orders

**Endpoint:** `GET /kitchen/orders`

**Description:** Retrieves all orders in the kitchen queue.

**Query Parameters:**

- `status` (optional): Filter by status (pending|preparing|ready)

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "orderId": "ORD-12345",
      "kotNumber": "KOT-001",
      "tableNumber": "5",
      "orderType": "dine-in",
      "items": [
        {
          "id": 7,
          "name": "Grilled Chicken",
          "quantity": 2,
          "specialInstructions": "Extra spicy",
          "status": "preparing",
          "startTime": "2024-01-15T10:05:00Z"
        }
      ],
      "priority": "normal",
      "orderTime": "2024-01-15T10:00:00Z",
      "estimatedCompletionTime": "2024-01-15T10:25:00Z"
    }
  ]
}
```

---

### 5.2 Update Kitchen Order Item Status

**Endpoint:** `PUT /kitchen/orders/{orderId}/items/{itemId}/status`

**Description:** Updates the preparation status of a specific item.

**Request Body:**

```json
{
  "status": "preparing|ready|served"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "orderId": "ORD-12345",
    "itemId": 7,
    "status": "ready",
    "message": "Item status updated successfully"
  }
}
```

---

## 6. Reports & Analytics

### 6.1 Get Sales Report

**Endpoint:** `GET /reports/sales`

**Description:** Retrieves sales report for a specific period.

**Query Parameters:**

- `startDate`: Start date (required)
- `endDate`: End date (required)
- `groupBy`: Group by (day|week|month)

**Response:**

```json
{
  "success": true,
  "data": {
    "period": {
      "start": "2024-01-01",
      "end": "2024-01-31"
    },
    "summary": {
      "totalOrders": 450,
      "totalSales": 1250000,
      "averageOrderValue": 2777.78,
      "totalTax": 200000,
      "totalDiscount": 50000
    },
    "breakdown": [
      {
        "date": "2024-01-15",
        "orders": 45,
        "sales": 125000
      }
    ],
    "topItems": [
      {
        "itemId": 7,
        "name": "Grilled Chicken",
        "quantity": 120,
        "revenue": 102000
      }
    ]
  }
}
```

---

### 6.2 Get Daily Summary

**Endpoint:** `GET /reports/daily-summary`

**Description:** Retrieves summary for the current day.

**Response:**

```json
{
  "success": true,
  "data": {
    "date": "2024-01-15",
    "ordersCount": 45,
    "totalSales": 125000,
    "averageOrderValue": 2777.78,
    "paymentMethods": {
      "cash": 50000,
      "card": 60000,
      "mpesa": 15000
    },
    "orderTypes": {
      "dine-in": 30,
      "takeaway": 10,
      "delivery": 5
    },
    "topWaiter": {
      "id": 1,
      "name": "John Doe",
      "orders": 15,
      "sales": 42000
    }
  }
}
```

---

## 7. Settings & Configuration

### 7.1 Get Restaurant Settings

**Endpoint:** `GET /settings`

**Description:** Retrieves restaurant configuration settings.

**Response:**

```json
{
  "success": true,
  "data": {
    "restaurantName": "DukaVerse Restaurant",
    "currency": "KSH",
    "taxRate": 16,
    "serviceCharge": 10,
    "logo": "https://example.com/logo.png",
    "address": "123 Main Street, Nairobi",
    "phone": "+254 700 000 000",
    "email": "info@dukaverse.com",
    "openingHours": {
      "monday": "09:00-22:00",
      "tuesday": "09:00-22:00"
    },
    "features": {
      "enableReservations": true,
      "enableDelivery": true,
      "enableTakeaway": true,
      "autoKitchenPrint": true
    }
  }
}
```

---

### 7.2 Update Restaurant Settings

**Endpoint:** `PUT /settings`

**Description:** Updates restaurant configuration settings.

**Request Body:**

```json
{
  "taxRate": 16,
  "serviceCharge": 10,
  "features": {
    "enableReservations": true
  }
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Settings updated successfully"
  }
}
```

---

## 8. Split Bill

### 8.1 Split Bill Equally

**Endpoint:** `POST /orders/{orderId}/split-equally`

**Description:** Splits the bill equally among multiple people.

**Request Body:**

```json
{
  "numberOfPeople": 3
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "orderId": "ORD-12345",
    "totalAmount": 3000,
    "numberOfPeople": 3,
    "amountPerPerson": 1000,
    "splits": [
      {
        "splitId": "SPLIT-001",
        "amount": 1000,
        "status": "pending"
      },
      {
        "splitId": "SPLIT-002",
        "amount": 1000,
        "status": "pending"
      },
      {
        "splitId": "SPLIT-003",
        "amount": 1000,
        "status": "pending"
      }
    ]
  }
}
```

---

### 8.2 Split Bill by Items

**Endpoint:** `POST /orders/{orderId}/split-by-items`

**Description:** Splits the bill based on specific items.

**Request Body:**

```json
{
  "splits": [
    {
      "personId": 1,
      "itemIds": [1, 2, 3]
    },
    {
      "personId": 2,
      "itemIds": [4, 5]
    }
  ]
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "orderId": "ORD-12345",
    "splits": [
      {
        "splitId": "SPLIT-001",
        "personId": 1,
        "items": [...],
        "amount": 1500,
        "status": "pending"
      },
      {
        "splitId": "SPLIT-002",
        "personId": 2,
        "items": [...],
        "amount": 1500,
        "status": "pending"
      }
    ]
  }
}
```

---

### 8.3 Process Split Payment

**Endpoint:** `POST /orders/{orderId}/splits/{splitId}/pay`

**Description:** Processes payment for a split bill.

**Request Body:**

```json
{
  "paymentMethod": "cash|card|mpesa",
  "amount": 1000
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "splitId": "SPLIT-001",
    "status": "paid",
    "paymentId": "PAY-12345",
    "message": "Split payment processed successfully"
  }
}
```

---

## Error Responses

All endpoints follow a consistent error response format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error message",
    "details": {
      "field": "Additional error details"
    }
  }
}
```

### Common Error Codes:

- `INVALID_REQUEST`: Invalid request data
- `NOT_FOUND`: Resource not found
- `UNAUTHORIZED`: Authentication required
- `FORBIDDEN`: Insufficient permissions
- `SERVER_ERROR`: Internal server error
- `VALIDATION_ERROR`: Data validation failed

---

## Authentication

All API endpoints require authentication using Bearer tokens:

```
Authorization: Bearer {token}
```

The token should be obtained from the login endpoint and included in all subsequent requests.

---

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Standard endpoints**: 100 requests per minute
- **Kitchen endpoints**: 200 requests per minute (higher for real-time updates)
- **Reports endpoints**: 20 requests per minute

---

## Webhooks (Optional)

For real-time updates, implement webhooks for:

- Order status changes
- Kitchen order updates
- Table status changes
- Payment confirmations

---

## Notes for Backend Implementation

1. **Real-time Updates**: Consider implementing WebSocket connections for real-time kitchen display and order status updates.

2. **Database Schema**: Ensure proper indexing on frequently queried fields (orderId, tableId, status, date ranges).

3. **Caching**: Implement caching for menu items and settings to reduce database load.

4. **Logging**: Log all order transactions, payments, and status changes for audit trails.

5. **Backup**: Implement automatic backups of order data and transactions.

6. **Security**: Implement proper authentication, authorization, and data encryption.

7. **Performance**: Optimize queries for reports and analytics endpoints.

8. **Scalability**: Design the system to handle multiple concurrent orders and kitchen stations.

---

## Testing Recommendations

1. Test concurrent order processing
2. Test payment processing with different methods
3. Test table status updates with multiple users
4. Test kitchen order queue management
5. Test split bill calculations
6. Test report generation with large datasets
7. Load testing for peak hours simulation

---

## Support

For questions or issues with API implementation, please contact the development team.
