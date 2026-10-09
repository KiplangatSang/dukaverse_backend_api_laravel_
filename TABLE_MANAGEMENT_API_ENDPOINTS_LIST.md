# Table Management API Endpoints - Quick Reference

## Base URL
```
https://api.dukaverse.com/api/v1
```

## Authentication
All endpoints require JWT authentication:
```
Authorization: Bearer <jwt_token>
X-Restaurant-ID: <restaurant_id>
```

---

## 1. Table Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/restaurants/{restaurant_id}/tables` | Get all tables with filtering |
| GET | `/restaurants/{restaurant_id}/tables/{table_id}` | Get single table details |
| POST | `/restaurants/{restaurant_id}/tables` | Create new table |
| PUT | `/restaurants/{restaurant_id}/tables/{table_id}` | Update table information |
| DELETE | `/restaurants/{restaurant_id}/tables/{table_id}` | Delete table |

---

## 2. Table Status Operations

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT | `/restaurants/{restaurant_id}/tables/{table_id}/status` | Update table status |
| POST | `/restaurants/{restaurant_id}/tables/{table_id}/occupy` | Mark table as occupied |
| POST | `/restaurants/{restaurant_id}/tables/{table_id}/clear` | Clear table and mark available |
| POST | `/restaurants/{restaurant_id}/tables/{table_id}/cleaned` | Mark table as cleaned |
| GET | `/restaurants/{restaurant_id}/tables/{table_id}/status-history` | Get status change history |

---

## 3. Floor Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/restaurants/{restaurant_id}/floors/{floor_name}/layout` | Get floor layout with tables |
| GET | `/restaurants/{restaurant_id}/floors` | List all floors |
| PUT | `/restaurants/{restaurant_id}/floors/{floor_name}/layout` | Update floor layout |
| GET | `/restaurants/{restaurant_id}/floors/{floor_name}/export` | Export floor plan (PNG/PDF/SVG) |

---

## 4. Reservation Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/restaurants/{restaurant_id}/reservations` | Create reservation |
| GET | `/restaurants/{restaurant_id}/reservations` | Get reservations with filtering |
| GET | `/restaurants/{restaurant_id}/reservations/{reservation_id}` | Get reservation details |
| PUT | `/restaurants/{restaurant_id}/reservations/{reservation_id}` | Update reservation |
| PUT | `/restaurants/{restaurant_id}/reservations/{reservation_id}/cancel` | Cancel reservation |
| POST | `/restaurants/{restaurant_id}/reservations/{reservation_id}/checkin` | Check-in reservation |
| GET | `/restaurants/{restaurant_id}/reservations/availability` | Check table availability |

---

## 5. Table Analytics

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/restaurants/{restaurant_id}/tables/statistics` | Get comprehensive table statistics |
| GET | `/restaurants/{restaurant_id}/tables/{table_id}/performance` | Get table performance metrics |
| GET | `/restaurants/{restaurant_id}/tables/occupancy-heatmap` | Get occupancy heatmap data |

---

## 6. Bulk Operations

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT | `/restaurants/{restaurant_id}/tables/bulk-update` | Update multiple tables |
| PUT | `/restaurants/{restaurant_id}/tables/bulk-status` | Change status of multiple tables |
| GET | `/restaurants/{restaurant_id}/tables/export` | Export tables data (CSV/Excel/PDF) |

---

## 7. Quick Actions

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/restaurants/{restaurant_id}/tables/quick-reserve` | Quick reservation for walk-ins |
| POST | `/restaurants/{restaurant_id}/tables/{table_id}/maintenance` | Mark table for maintenance |

---

## 8. Table Combinations

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/restaurants/{restaurant_id}/tables/combine` | Combine multiple tables |
| POST | `/restaurants/{restaurant_id}/tables/combinations/{combination_id}/split` | Split combined tables |

---

## 9. Waitlist Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/restaurants/{restaurant_id}/tables/waitlist` | Add customer to waitlist |
| GET | `/restaurants/{restaurant_id}/tables/waitlist` | Get current waitlist |
| POST | `/restaurants/{restaurant_id}/tables/waitlist/{waitlist_id}/notify` | Notify waitlist customer |
| PUT | `/restaurants/{restaurant_id}/tables/waitlist/{waitlist_id}` | Update waitlist entry |
| DELETE | `/restaurants/{restaurant_id}/tables/waitlist/{waitlist_id}` | Remove from waitlist |

---

## 10. Real-time WebSocket

| Type | Endpoint | Description |
|------|----------|-------------|
| WebSocket | `wss://api.dukaverse.com/ws/restaurants/{restaurant_id}/tables` | Real-time table updates |

### WebSocket Events:
- `table.status_changed` - Table status updated
- `table.occupied` - Table marked as occupied
- `table.cleared` - Table cleared
- `reservation.created` - New reservation
- `reservation.checked_in` - Customer checked in
- `waitlist.added` - Customer added to waitlist
- `waitlist.notified` - Waitlist customer notified

---

## Common Query Parameters

### Filtering
- `status` - Filter by status (available, occupied, reserved, cleaning, out_of_order)
- `floor` - Filter by floor (main, upstairs, outdoor, basement)
- `section` - Filter by section (window, center, corner, bar, private)
- `capacity` - Filter by capacity
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)

### Pagination
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 20, max: 100)

### Sorting
- `sort_by` - Sort field (number, capacity, status, created_at)
- `sort_order` - Sort order (asc, desc)

---

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "pagination": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Error message",
    "details": { ... },
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

---

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 409 | Conflict |
| 422 | Validation Error |
| 429 | Rate Limit Exceeded |
| 500 | Internal Server Error |

---

## Common Error Codes

| Code | Description |
|------|-------------|
| `TABLE_NOT_FOUND` | Table does not exist |
| `TABLE_OCCUPIED` | Table is currently occupied |
| `TABLE_RESERVED` | Table is reserved |
| `INVALID_STATUS_TRANSITION` | Invalid status change |
| `RESERVATION_NOT_FOUND` | Reservation does not exist |
| `RESERVATION_CONFLICT` | Time slot already booked |
| `INVALID_PARTY_SIZE` | Party size exceeds capacity |
| `VALIDATION_ERROR` | Request validation failed |

---

## Rate Limits

- **Standard**: 1000 requests/hour
- **Premium**: 5000 requests/hour
- **Enterprise**: Unlimited

---

## Implementation Priority

### Phase 1 - Core Functionality (Week 1-2)
1. ✅ GET `/restaurants/{restaurant_id}/tables` - List tables
2. ✅ GET `/restaurants/{restaurant_id}/tables/{table_id}` - Get table details
3. ✅ POST `/restaurants/{restaurant_id}/tables` - Create table
4. ✅ PUT `/restaurants/{restaurant_id}/tables/{table_id}` - Update table
5. ✅ PUT `/restaurants/{restaurant_id}/tables/{table_id}/status` - Update status
6. ✅ POST `/restaurants/{restaurant_id}/tables/{table_id}/occupy` - Mark occupied
7. ✅ POST `/restaurants/{restaurant_id}/tables/{table_id}/clear` - Clear table

### Phase 2 - Reservations (Week 3)
8. ✅ POST `/restaurants/{restaurant_id}/reservations` - Create reservation
9. ✅ GET `/restaurants/{restaurant_id}/reservations` - List reservations
10. ✅ POST `/restaurants/{restaurant_id}/reservations/{reservation_id}/checkin` - Check-in
11. ✅ GET `/restaurants/{restaurant_id}/reservations/availability` - Check availability

### Phase 3 - Floor Management (Week 4)
12. ✅ GET `/restaurants/{restaurant_id}/floors/{floor_name}/layout` - Get layout
13. ✅ PUT `/restaurants/{restaurant_id}/floors/{floor_name}/layout` - Update layout
14. ✅ GET `/restaurants/{restaurant_id}/floors` - List floors

### Phase 4 - Analytics & Advanced Features (Week 5-6)
15. ✅ GET `/restaurants/{restaurant_id}/tables/statistics` - Statistics
16. ✅ GET `/restaurants/{restaurant_id}/tables/{table_id}/performance` - Performance
17. ✅ POST `/restaurants/{restaurant_id}/tables/waitlist` - Waitlist
18. ✅ PUT `/restaurants/{restaurant_id}/tables/bulk-update` - Bulk operations
19. ✅ WebSocket connection - Real-time updates

---

## Testing Endpoints

### Sandbox Environment
```
Base URL: https://sandbox-api.dukaverse.com/api/v1
Restaurant ID: test-restaurant-uuid
API Key: test_api_key_12345
```

### Sample cURL Commands

**Get All Tables:**
```bash
curl -X GET "https://api.dukaverse.com/api/v1/restaurants/{restaurant_id}/tables" \
  -H "Authorization: Bearer {token}" \
  -H "X-Restaurant-ID: {restaurant_id}"
```

**Create Table:**
```bash
curl -X POST "https://api.dukaverse.com/api/v1/restaurants/{restaurant_id}/tables" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "number": 15,
    "capacity": 4,
    "section": "window",
    "floor": "main",
    "position": {"x": 50, "y": 50}
  }'
```

**Update Table Status:**
```bash
curl -X PUT "https://api.dukaverse.com/api/v1/restaurants/{restaurant_id}/tables/{table_id}/status" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "occupied",
    "order_id": "uuid",
    "waiter_id": "uuid"
  }'
```

**Create Reservation:**
```bash
curl -X POST "https://api.dukaverse.com/api/v1/restaurants/{restaurant_id}/reservations" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Jane Doe",
    "customer_phone": "+254700000000",
    "party_size": 4,
    "reservation_date": "2024-01-20",
    "reservation_time": "19:30"
  }'
```

---

## Support & Documentation

- **Full Documentation**: See `TABLE_MANAGEMENT_API_SPECIFICATION.md`
- **Email**: api-support@dukaverse.com
- **Developer Portal**: https://developers.dukaverse.com
- **Status Page**: https://status.dukaverse.com

---

**Last Updated**: 2024-01-15
**Version**: 1.0.0
