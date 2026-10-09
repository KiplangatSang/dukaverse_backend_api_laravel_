# Restaurant & Delivery API Technical Specifications

## System Architecture

### Overview
The restaurant and delivery management system is built on Laravel 10+ with a modular, service-oriented architecture supporting multi-tenancy and real-time features.

### Core Architecture Principles
- **Multi-tenant**: Shared database with tenant isolation
- **Service-oriented**: Controller → Service → Repository → Model pattern
- **Real-time**: WebSocket integration for live updates
- **RESTful**: Comprehensive API with OpenAPI 3.0 documentation
- **Scalable**: Horizontal scaling with Redis caching and queuing

### Technology Stack
- **Backend**: Laravel 10+, PHP 8.2+
- **Database**: PostgreSQL 15+ with PostGIS extension
- **Cache/Queue**: Redis 7+
- **Real-time**: Laravel Broadcasting + Socket.io
- **File Storage**: AWS S3 with CloudFront CDN
- **Payment**: Stripe, M-PESA, PayPal
- **Communication**: Twilio, SendGrid

## Multi-Tenant Architecture

### Tenant Isolation Strategy
- **Database**: Shared PostgreSQL with tenant_id columns
- **Middleware**: Automatic tenant context injection
- **Models**: Global scopes for tenant data filtering
- **Cache**: Tenant-prefixed cache keys

### Tenant Management
```php
// Tenant identification middleware
class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        $tenantId = $request->header('X-Tenant-ID') ??
                   $request->route('restaurant_id');

        if (!$tenantId) {
            return response()->json(['error' => 'Tenant required'], 400);
        }

        // Set tenant context
        TenantContext::setTenantId($tenantId);

        return $next($request);
    }
}
```

## Database Schema

### Core Tables

#### restaurants
```sql
CREATE TABLE restaurants (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(50),
    address JSONB,
    coordinates GEOGRAPHY(POINT),
    operating_hours JSONB,
    settings JSONB DEFAULT '{}',
    subscription_plan VARCHAR(50) DEFAULT 'basic',
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### restaurant_settings
```sql
CREATE TABLE restaurant_settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    restaurant_id UUID REFERENCES restaurants(id) ON DELETE CASCADE,
    category VARCHAR(50) NOT NULL,
    key VARCHAR(100) NOT NULL,
    value JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(restaurant_id, category, key)
);
```

#### menu_categories
```sql
CREATE TABLE menu_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    restaurant_id UUID REFERENCES restaurants(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### menu_items
```sql
CREATE TABLE menu_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    restaurant_id UUID REFERENCES restaurants(id) ON DELETE CASCADE,
    category_id UUID REFERENCES menu_categories(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    image_urls JSONB DEFAULT '[]',
    is_available BOOLEAN DEFAULT true,
    preparation_time INTEGER DEFAULT 15,
    calories INTEGER,
    allergens JSONB DEFAULT '[]',
    nutritional_info JSONB DEFAULT '{}',
    tags JSONB DEFAULT '[]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### orders
```sql
CREATE TABLE orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    restaurant_id UUID REFERENCES restaurants(id) ON DELETE CASCADE,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    order_type VARCHAR(20) DEFAULT 'dine_in',
    status VARCHAR(30) DEFAULT 'pending',
    customer_info JSONB,
    table_id UUID,
    items JSONB NOT NULL,
    pricing JSONB NOT NULL,
    special_instructions TEXT,
    scheduled_for TIMESTAMP,
    estimated_preparation_time INTEGER,
    created_by UUID,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### tables
```sql
CREATE TABLE tables (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    restaurant_id UUID REFERENCES restaurants(id) ON DELETE CASCADE,
    number VARCHAR(10) NOT NULL,
    name VARCHAR(100),
    capacity INTEGER NOT NULL,
    status VARCHAR(20) DEFAULT 'available',
    location VARCHAR(50) DEFAULT 'main_floor',
    features JSONB DEFAULT '[]',
    coordinates JSONB,
    current_order_id UUID,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### staff
```sql
CREATE TABLE staff (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    restaurant_id UUID REFERENCES restaurants(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(30) NOT NULL,
    pin_code VARCHAR(10),
    hourly_rate DECIMAL(8,2),
    status VARCHAR(20) DEFAULT 'active',
    permissions JSONB DEFAULT '[]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### shifts
```sql
CREATE TABLE shifts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    restaurant_id UUID REFERENCES restaurants(id) ON DELETE CASCADE,
    staff_id UUID REFERENCES staff(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_start TIME,
    break_end TIME,
    status VARCHAR(20) DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### deliveries
```sql
CREATE TABLE deliveries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id UUID REFERENCES orders(id) ON DELETE CASCADE,
    rider_id UUID REFERENCES staff(id),
    status VARCHAR(30) DEFAULT 'pending',
    pickup_location JSONB NOT NULL,
    delivery_location JSONB NOT NULL,
    estimated_distance DECIMAL(8,2),
    estimated_duration INTEGER,
    actual_distance DECIMAL(8,2),
    actual_duration INTEGER,
    fee DECIMAL(8,2),
    tips DECIMAL(8,2),
    rating INTEGER,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## API Endpoints

### Restaurant Management

#### GET /api/v1/restaurants/{restaurant_id}
**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "DukaVerse Restaurant",
    "description": "Modern fusion cuisine",
    "contact": {
      "phone": "+254700000000",
      "email": "info@restaurant.com"
    },
    "location": {
      "address": "123 Main St, Nairobi",
      "coordinates": {"lat": -1.2864, "lng": 36.8172}
    },
    "operating_hours": {
      "monday": {"open": "08:00", "close": "22:00"}
    },
    "settings": {
      "currency": "KES",
      "tax_rate": 16.0,
      "features": ["delivery", "takeaway"]
    }
  }
}
```

#### PUT /api/v1/restaurants/{restaurant_id}
**Request Body:**
```json
{
  "name": "Updated Restaurant Name",
  "settings": {
    "tax_rate": 18.0,
    "enable_delivery": false
  }
}
```

### Menu Management

#### GET /api/v1/restaurants/{restaurant_id}/menu/items
**Query Parameters:**
- `category_id`: Filter by category
- `search`: Search in name/description
- `is_available`: true/false
- `page`: 1
- `limit`: 20

#### POST /api/v1/restaurants/{restaurant_id}/menu/items
**Request Body:**
```json
{
  "name": "Chicken Burger",
  "description": "Grilled chicken with lettuce and mayo",
  "category_id": "uuid",
  "base_price": 450.0,
  "preparation_time": 15,
  "calories": 650,
  "allergens": ["gluten", "eggs"],
  "is_available": true
}
```

#### PUT /api/v1/restaurants/{restaurant_id}/menu/items/{item_id}
#### DELETE /api/v1/restaurants/{restaurant_id}/menu/items/{item_id}

### Order Management

#### POST /api/v1/restaurants/{restaurant_id}/orders
**Request Body:**
```json
{
  "order_type": "dine_in",
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

#### GET /api/v1/restaurants/{restaurant_id}/orders
**Query Parameters:**
- `status`: pending|confirmed|preparing|ready|completed
- `order_type`: dine_in|takeaway|delivery
- `date_from`: 2024-01-01
- `date_to`: 2024-01-31
- `page`: 1
- `limit`: 20

#### PUT /api/v1/restaurants/{restaurant_id}/orders/{order_id}/status
**Request Body:**
```json
{
  "status": "confirmed",
  "notes": "Order confirmed and sent to kitchen"
}
```

### Table Management

#### GET /api/v1/restaurants/{restaurant_id}/tables
**Query Parameters:**
- `status`: available|occupied|reserved
- `capacity_min`: 2
- `capacity_max`: 8

#### PUT /api/v1/restaurants/{restaurant_id}/tables/{table_id}/status
**Request Body:**
```json
{
  "status": "occupied",
  "order_id": "uuid"
}
```

### Reservation Management

#### POST /api/v1/restaurants/{restaurant_id}/reservations
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

#### GET /api/v1/restaurants/{restaurant_id}/reservations
**Query Parameters:**
- `date`: 2024-01-20
- `status`: pending|confirmed|seated|completed

### Staff Management

#### GET /api/v1/restaurants/{restaurant_id}/staff
**Query Parameters:**
- `role`: waiter|chef|manager
- `status`: active|inactive

#### POST /api/v1/restaurants/{restaurant_id}/staff
**Request Body:**
```json
{
  "user_id": "uuid",
  "role": "waiter",
  "pin_code": "1234",
  "hourly_rate": 250.0,
  "permissions": ["orders.create", "orders.update"]
}
```

### Shift Management

#### POST /api/v1/restaurants/{restaurant_id}/shifts
**Request Body:**
```json
{
  "staff_id": "uuid",
  "date": "2024-01-15",
  "start_time": "09:00",
  "end_time": "17:00",
  "break_start": "12:00",
  "break_end": "13:00"
}
```

#### POST /api/v1/restaurants/{restaurant_id}/shifts/{shift_id}/clock-in
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

### Delivery Management

#### POST /api/v1/delivery/orders
**Request Body:**
```json
{
  "restaurant_id": "uuid",
  "customer_info": {
    "name": "Jane Doe",
    "phone": "+254700000000"
  },
  "delivery_location": {
    "address": "456 Oak Avenue, Nairobi",
    "coordinates": {"lat": -1.2864, "lng": 36.8172}
  },
  "items": [
    {
      "name": "Chicken Burger",
      "quantity": 2,
      "price": 450.0
    }
  ],
  "delivery_fee": 150.0
}
```

#### PUT /api/v1/delivery/orders/{order_id}/status
**Request Body:**
```json
{
  "status": "assigned",
  "rider_id": "uuid"
}
```

### Payment Processing

#### POST /api/v1/restaurants/{restaurant_id}/payments
**Request Body:**
```json
{
  "order_id": "uuid",
  "amount": 1122.0,
  "payment_method": "mpesa",
  "phone_number": "+254700000000",
  "reference": "Order payment"
}
```

#### POST /api/v1/restaurants/{restaurant_id}/payments/{payment_id}/refund
**Request Body:**
```json
{
  "amount": 450.0,
  "reason": "wrong_order",
  "notes": "Customer received wrong item"
}
```

### Reporting & Analytics

#### GET /api/v1/restaurants/{restaurant_id}/reports/sales
**Query Parameters:**
- `start_date`: 2024-01-01
- `end_date`: 2024-01-31
- `group_by`: day|week|month

#### GET /api/v1/restaurants/{restaurant_id}/reports/customers
**Response:**
```json
{
  "success": true,
  "data": {
    "total_customers": 1250,
    "segments": {
      "new": {"count": 180, "percentage": 14.4},
      "returning": {"count": 850, "percentage": 68.0},
      "vip": {"count": 220, "percentage": 17.6}
    }
  }
}
```

## Real-time Features

### WebSocket Events

#### Order Events
```javascript
// Order created
{
  "event": "order.created",
  "data": {
    "order_id": "uuid",
    "restaurant_id": "uuid",
    "order_number": "ORD-001",
    "items": [...],
    "total": 1122.0
  }
}

// Order status updated
{
  "event": "order.status_updated",
  "data": {
    "order_id": "uuid",
    "status": "ready",
    "updated_by": "uuid"
  }
}
```

#### Delivery Events
```javascript
// Rider assigned
{
  "event": "delivery.assigned",
  "data": {
    "order_id": "uuid",
    "rider_id": "uuid",
    "rider_name": "Mike Johnson",
    "estimated_time": 35
  }
}

// Location updated
{
  "event": "delivery.location_updated",
  "data": {
    "order_id": "uuid",
    "latitude": -1.2864,
    "longitude": 36.8172,
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

#### Kitchen Events
```javascript
// Order ready for pickup
{
  "event": "kitchen.order_ready",
  "data": {
    "order_id": "uuid",
    "order_number": "ORD-001",
    "table_number": 5,
    "items": [...]
  }
}

// Item completed
{
  "event": "kitchen.item_completed",
  "data": {
    "order_id": "uuid",
    "item_id": "uuid",
    "completed_by": "uuid"
  }
}
```

### Channel Subscriptions
```php
// Restaurant-specific channel
Broadcast::channel('restaurant.{restaurantId}', function ($user, $restaurantId) {
    return $user->restaurant_id === $restaurantId;
});

// Order-specific channel
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    return $order && $order->restaurant_id === $user->restaurant_id;
});
```

## Security Implementation

### Authentication & Authorization
```php
// JWT with tenant context
class RestaurantAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $restaurantId = $request->route('restaurant_id');

        if (!$user->canAccessRestaurant($restaurantId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
```

### Data Validation
```php
// Comprehensive request validation
class StoreOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'order_type' => 'required|in:dine_in,takeaway,delivery',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|uuid|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'customer_info.name' => 'required_if:order_type,delivery|string|max:255',
            'customer_info.phone' => 'required_if:order_type,delivery|string|regex:/^\+254[0-9]{9}$/',
        ];
    }
}
```

### Rate Limiting
```php
// API rate limiting
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{order_id}/status', [OrderController::class, 'updateStatus']);
});
```

## Performance Optimization

### Database Optimization
```sql
-- Indexes for performance
CREATE INDEX idx_orders_restaurant_status ON orders(restaurant_id, status);
CREATE INDEX idx_orders_created_at ON orders(created_at DESC);
CREATE INDEX idx_menu_items_category ON menu_items(category_id, is_available);
CREATE INDEX idx_deliveries_status_location ON deliveries(status, delivery_location);

-- Partial indexes
CREATE INDEX idx_active_tables ON tables(restaurant_id, status) WHERE status = 'available';
```

### Caching Strategy
```php
// Menu caching
class MenuCache
{
    public static function getMenuItems($restaurantId)
    {
        return Cache::tags(['restaurant', "restaurant_{$restaurantId}"])
                   ->remember("menu_items_{$restaurantId}", 3600, function () use ($restaurantId) {
                       return MenuItem::where('restaurant_id', $restaurantId)
                                    ->where('is_available', true)
                                    ->with('category')
                                    ->get();
                   });
    }
}
```

### Queue Processing
```php
// Background job processing
class ProcessOrder implements ShouldQueue
{
    public function handle()
    {
        // Heavy processing like notification sending
        // Payment processing
        // Inventory updates
    }
}
```

## Error Handling & Logging

### Structured Error Responses
```php
class ApiExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'trace' => config('app.debug') ? $exception->getTrace() : null
                ]
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
```

### Comprehensive Logging
```php
// Structured logging
Log::channel('restaurant')->info('Order created', [
    'order_id' => $order->id,
    'restaurant_id' => $order->restaurant_id,
    'total' => $order->total_amount,
    'items_count' => $order->items->count()
]);
```

## Testing Strategy

### Unit Tests
```php
class OrderServiceTest extends TestCase
{
    public function test_can_create_order()
    {
        $orderData = [
            'restaurant_id' => $this->restaurant->id,
            'order_type' => 'dine_in',
            'items' => [...]
        ];

        $order = $this->orderService->createOrder($orderData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('pending', $order->status);
    }
}
```

### Feature Tests
```php
class OrderApiTest extends TestCase
{
    public function test_can_create_order_via_api()
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson("/api/v1/restaurants/{$this->restaurant->id}/orders", [
            'order_type' => 'dine_in',
            'items' => [...]
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'order_id',
                        'order_number',
                        'status'
                    ]
                ]);
    }
}
```

### Integration Tests
```php
class OrderPaymentIntegrationTest extends TestCase
{
    public function test_full_order_payment_flow()
    {
        // Create order
        $order = Order::factory()->create();

        // Process payment
        $payment = $this->paymentService->processPayment([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => 'mpesa'
        ]);

        // Assert payment success
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals($order->total_amount, $payment->amount);
    }
}
```

## Deployment & DevOps

### Environment Configuration
```bash
# Docker Compose for development
version: '3.8'
services:
  app:
    build: .
    environment:
      - APP_ENV=local
      - DB_HOST=postgres
      - REDIS_HOST=redis
    depends_on:
      - postgres
      - redis

  postgres:
    image: postgres:15
    environment:
      - POSTGRES_DB=dukaverse

  redis:
    image: redis:7-alpine
```

### CI/CD Pipeline
```yaml
# GitHub Actions workflow
name: CI/CD
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Run tests
        run: |
          composer install
          php artisan test
          ./vendor/bin/phpstan analyse

  deploy:
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to production
        run: |
          # Deployment steps
```

### Monitoring & Alerting
```php
// Health checks
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'database' => DB::connection()->getPdo() ? 'ok' : 'error',
        'redis' => Redis::ping() ? 'ok' : 'error',
        'timestamp' => now()
    ]);
});
```

This technical specification provides the foundation for implementing a robust, scalable restaurant and delivery management system. The modular architecture ensures maintainability while the comprehensive feature set supports enterprise-level operations.
