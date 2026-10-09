# Table Management Routes - Ready to Copy & Paste

## Step 1: Add Controller Import

Add this line at the top of your `routes/api.php` file with the other use statements:

```php
use App\Http\Controllers\TableManagementController;
```

---

## Step 2: Copy & Paste These Routes

Add these routes inside your existing `Route::prefix('v1')->group(function () {` block, preferably after the restaurant routes section:

```php
// ============================================================================
// TABLE MANAGEMENT API ROUTES
// ============================================================================

Route::prefix('restaurants/{restaurant_id}')->middleware(['auth:sanctum', 'hassessionaccount'])->group(function () {
    
    // Tables CRUD
    Route::get('/tables', [TableManagementController::class, 'index']);
    Route::post('/tables', [TableManagementController::class, 'store']);
    Route::get('/tables/{table_id}', [TableManagementController::class, 'show']);
    Route::put('/tables/{table_id}', [TableManagementController::class, 'update']);
    Route::delete('/tables/{table_id}', [TableManagementController::class, 'destroy']);
    
    // Table Status Operations
    Route::put('/tables/{table_id}/status', [TableManagementController::class, 'updateStatus']);
    Route::post('/tables/{table_id}/occupy', [TableManagementController::class, 'markOccupied']);
    Route::post('/tables/{table_id}/clear', [TableManagementController::class, 'clearTable']);
    Route::post('/tables/{table_id}/cleaned', [TableManagementController::class, 'markCleaned']);
    Route::get('/tables/{table_id}/status-history', [TableManagementController::class, 'getStatusHistory']);
    
    // Floor Management
    Route::get('/floors', [TableManagementController::class, 'getFloors']);
    Route::get('/floors/{floor_name}/layout', [TableManagementController::class, 'getFloorLayout']);
    Route::put('/floors/{floor_name}/layout', [TableManagementController::class, 'updateFloorLayout']);
    Route::get('/floors/{floor_name}/export', [TableManagementController::class, 'exportFloorPlan']);
    
    // Reservations
    Route::get('/reservations', [TableManagementController::class, 'getReservations']);
    Route::post('/reservations', [TableManagementController::class, 'createReservation']);
    Route::get('/reservations/availability', [TableManagementController::class, 'checkAvailability']);
    Route::get('/reservations/{reservation_id}', [TableManagementController::class, 'getReservation']);
    Route::put('/reservations/{reservation_id}', [TableManagementController::class, 'updateReservation']);
    Route::put('/reservations/{reservation_id}/cancel', [TableManagementController::class, 'cancelReservation']);
    Route::post('/reservations/{reservation_id}/checkin', [TableManagementController::class, 'checkinReservation']);
    
    // Analytics
    Route::get('/tables/statistics', [TableManagementController::class, 'getStatistics']);
    Route::get('/tables/{table_id}/performance', [TableManagementController::class, 'getTablePerformance']);
    Route::get('/tables/occupancy-heatmap', [TableManagementController::class, 'getOccupancyHeatmap']);
    
    // Bulk Operations
    Route::put('/tables/bulk-update', [TableManagementController::class, 'bulkUpdate']);
    Route::put('/tables/bulk-status', [TableManagementController::class, 'bulkStatusChange']);
    Route::get('/tables/export', [TableManagementController::class, 'exportTables']);
    
    // Quick Actions
    Route::post('/tables/quick-reserve', [TableManagementController::class, 'quickReserve']);
    Route::post('/tables/{table_id}/maintenance', [TableManagementController::class, 'markForMaintenance']);
    
    // Table Combinations
    Route::post('/tables/combine', [TableManagementController::class, 'combineTables']);
    Route::post('/tables/combinations/{combination_id}/split', [TableManagementController::class, 'splitTables']);
    
    // Waitlist Management
    Route::get('/tables/waitlist', [TableManagementController::class, 'getWaitlist']);
    Route::post('/tables/waitlist', [TableManagementController::class, 'addToWaitlist']);
    Route::put('/tables/waitlist/{waitlist_id}', [TableManagementController::class, 'updateWaitlist']);
    Route::post('/tables/waitlist/{waitlist_id}/notify', [TableManagementController::class, 'notifyWaitlistCustomer']);
    Route::delete('/tables/waitlist/{waitlist_id}', [TableManagementController::class, 'removeFromWaitlist']);
});
```

---

## Step 3: Verify Routes

After adding the routes, verify them by running:

```bash
php artisan route:list | grep tables
```

Or to see all table management routes:

```bash
php artisan route:list --path=restaurants
```

---

## Complete Route List (40 Endpoints)

### Table Management (5 routes)
1. `GET    /api/v1/restaurants/{restaurant_id}/tables` - List all tables
2. `POST   /api/v1/restaurants/{restaurant_id}/tables` - Create table
3. `GET    /api/v1/restaurants/{restaurant_id}/tables/{table_id}` - Get table details
4. `PUT    /api/v1/restaurants/{restaurant_id}/tables/{table_id}` - Update table
5. `DELETE /api/v1/restaurants/{restaurant_id}/tables/{table_id}` - Delete table

### Table Status (5 routes)
6. `PUT    /api/v1/restaurants/{restaurant_id}/tables/{table_id}/status` - Update status
7. `POST   /api/v1/restaurants/{restaurant_id}/tables/{table_id}/occupy` - Mark occupied
8. `POST   /api/v1/restaurants/{restaurant_id}/tables/{table_id}/clear` - Clear table
9. `POST   /api/v1/restaurants/{restaurant_id}/tables/{table_id}/cleaned` - Mark cleaned
10. `GET   /api/v1/restaurants/{restaurant_id}/tables/{table_id}/status-history` - Status history

### Floor Management (4 routes)
11. `GET   /api/v1/restaurants/{restaurant_id}/floors` - List floors
12. `GET   /api/v1/restaurants/{restaurant_id}/floors/{floor_name}/layout` - Get layout
13. `PUT   /api/v1/restaurants/{restaurant_id}/floors/{floor_name}/layout` - Update layout
14. `GET   /api/v1/restaurants/{restaurant_id}/floors/{floor_name}/export` - Export floor plan

### Reservations (7 routes)
15. `GET   /api/v1/restaurants/{restaurant_id}/reservations` - List reservations
16. `POST  /api/v1/restaurants/{restaurant_id}/reservations` - Create reservation
17. `GET   /api/v1/restaurants/{restaurant_id}/reservations/availability` - Check availability
18. `GET   /api/v1/restaurants/{restaurant_id}/reservations/{reservation_id}` - Get reservation
19. `PUT   /api/v1/restaurants/{restaurant_id}/reservations/{reservation_id}` - Update reservation
20. `PUT   /api/v1/restaurants/{restaurant_id}/reservations/{reservation_id}/cancel` - Cancel
21. `POST  /api/v1/restaurants/{restaurant_id}/reservations/{reservation_id}/checkin` - Check-in

### Analytics (3 routes)
22. `GET   /api/v1/restaurants/{restaurant_id}/tables/statistics` - Get statistics
23. `GET   /api/v1/restaurants/{restaurant_id}/tables/{table_id}/performance` - Table performance
24. `GET   /api/v1/restaurants/{restaurant_id}/tables/occupancy-heatmap` - Occupancy heatmap

### Bulk Operations (3 routes)
25. `PUT   /api/v1/restaurants/{restaurant_id}/tables/bulk-update` - Bulk update
26. `PUT   /api/v1/restaurants/{restaurant_id}/tables/bulk-status` - Bulk status change
27. `GET   /api/v1/restaurants/{restaurant_id}/tables/export` - Export tables

### Quick Actions (2 routes)
28. `POST  /api/v1/restaurants/{restaurant_id}/tables/quick-reserve` - Quick reserve
29. `POST  /api/v1/restaurants/{restaurant_id}/tables/{table_id}/maintenance` - Maintenance

### Table Combinations (2 routes)
30. `POST  /api/v1/restaurants/{restaurant_id}/tables/combine` - Combine tables
31. `POST  /api/v1/restaurants/{restaurant_id}/tables/combinations/{combination_id}/split` - Split

### Waitlist (5 routes)
32. `GET   /api/v1/restaurants/{restaurant_id}/tables/waitlist` - Get waitlist
33. `POST  /api/v1/restaurants/{restaurant_id}/tables/waitlist` - Add to waitlist
34. `PUT   /api/v1/restaurants/{restaurant_id}/tables/waitlist/{waitlist_id}` - Update waitlist
35. `POST  /api/v1/restaurants/{restaurant_id}/tables/waitlist/{waitlist_id}/notify` - Notify customer
36. `DELETE /api/v1/restaurants/{restaurant_id}/tables/waitlist/{waitlist_id}` - Remove from waitlist

---

## Next Steps

After adding the routes, you need to:

1. **Create the Controller**
   ```bash
   php artisan make:controller TableManagementController
   ```

2. **Create Request Validation Classes**
   ```bash
   php artisan make:request TableManagement/StoreTableRequest
   php artisan make:request TableManagement/UpdateTableRequest
   php artisan make:request TableManagement/CreateReservationRequest
   ```

3. **Create Database Migrations**
   ```bash
   php artisan make:migration create_tables_table
   php artisan make:migration create_reservations_table
   php artisan make:migration create_floors_table
   php artisan make:migration create_table_combinations_table
   php artisan make:migration create_waitlist_table
   php artisan make:migration create_table_status_history_table
   ```

4. **Create Models**
   ```bash
   php artisan make:model Table
   php artisan make:model Reservation
   php artisan make:model Floor
   php artisan make:model TableCombination
   php artisan make:model Waitlist
   php artisan make:model TableStatusHistory
   ```

5. **Test the Routes**
   ```bash
   php artisan route:list | grep tables
   ```

---

## Example Usage

### Test with cURL:

```bash
# Get all tables
curl -X GET "http://localhost:8000/api/v1/restaurants/1/tables" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Create a table
curl -X POST "http://localhost:8000/api/v1/restaurants/1/tables" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "number": 5,
    "capacity": 4,
    "section": "window",
    "floor": "main",
    "position": {"x": 50, "y": 50}
  }'

# Update table status
curl -X PUT "http://localhost:8000/api/v1/restaurants/1/tables/5/status" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "occupied",
    "order_id": "uuid-here"
  }'
```

---

## Important Notes

- All routes require authentication (`auth:sanctum` middleware)
- All routes require session account validation (`hassessionaccount` middleware)
- Replace `{restaurant_id}`, `{table_id}`, etc. with actual IDs
- Refer to `TABLE_MANAGEMENT_API_SPECIFICATION.md` for complete request/response examples
- Refer to `TABLE_MANAGEMENT_API_ENDPOINTS_LIST.md` for quick reference
