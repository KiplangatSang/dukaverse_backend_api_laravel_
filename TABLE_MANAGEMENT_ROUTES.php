<?php

/**
 * Table Management API Routes
 *
 * Copy and paste these routes into your routes/api.php file
 * Make sure to import the TableManagementController at the top of your api.php:
 * use App\Http\Controllers\TableManagementController;
 *
 * Place these routes inside the appropriate middleware group (auth:sanctum)
 */

// ============================================================================
// TABLE MANAGEMENT API ROUTES
// ============================================================================

Route::prefix('restaurants/{restaurant_id}')->middleware(['auth:sanctum', 'hassessionaccount'])->group(function () {

    // ========================================================================
    // 1. TABLE MANAGEMENT
    // ========================================================================

    // Get all tables with filtering
    Route::get('/tables', [TableManagementController::class, 'index']);

    // Get single table details
    Route::get('/tables/{table_id}', [TableManagementController::class, 'show']);

    // Create new table
    Route::post('/tables', [TableManagementController::class, 'store']);

    // Update table information
    Route::put('/tables/{table_id}', [TableManagementController::class, 'update']);

    // Delete table
    Route::delete('/tables/{table_id}', [TableManagementController::class, 'destroy']);


    // ========================================================================
    // 2. TABLE STATUS OPERATIONS
    // ========================================================================

    // Update table status
    Route::put('/tables/{table_id}/status', [TableManagementController::class, 'updateStatus']);

    // Mark table as occupied
    Route::post('/tables/{table_id}/occupy', [TableManagementController::class, 'markOccupied']);

    // Clear table and mark available
    Route::post('/tables/{table_id}/clear', [TableManagementController::class, 'clearTable']);

    // Mark table as cleaned
    Route::post('/tables/{table_id}/cleaned', [TableManagementController::class, 'markCleaned']);

    // Get status change history
    Route::get('/tables/{table_id}/status-history', [TableManagementController::class, 'getStatusHistory']);


    // ========================================================================
    // 3. FLOOR MANAGEMENT
    // ========================================================================

    // Get floor layout with tables
    Route::get('/floors/{floor_name}/layout', [TableManagementController::class, 'getFloorLayout']);

    // List all floors
    Route::get('/floors', [TableManagementController::class, 'getFloors']);

    // Update floor layout
    Route::put('/floors/{floor_name}/layout', [TableManagementController::class, 'updateFloorLayout']);

    // Export floor plan (PNG/PDF/SVG)
    Route::get('/floors/{floor_name}/export', [TableManagementController::class, 'exportFloorPlan']);


    // ========================================================================
    // 4. RESERVATION MANAGEMENT
    // ========================================================================

    // Create reservation
    Route::post('/reservations', [TableManagementController::class, 'createReservation']);

    // Get reservations with filtering
    Route::get('/reservations', [TableManagementController::class, 'getReservations']);

    // Get reservation details
    Route::get('/reservations/{reservation_id}', [TableManagementController::class, 'getReservation']);

    // Update reservation
    Route::put('/reservations/{reservation_id}', [TableManagementController::class, 'updateReservation']);

    // Cancel reservation
    Route::put('/reservations/{reservation_id}/cancel', [TableManagementController::class, 'cancelReservation']);

    // Check-in reservation
    Route::post('/reservations/{reservation_id}/checkin', [TableManagementController::class, 'checkinReservation']);

    // Check table availability
    Route::get('/reservations/availability', [TableManagementController::class, 'checkAvailability']);


    // ========================================================================
    // 5. TABLE ANALYTICS
    // ========================================================================

    // Get comprehensive table statistics
    Route::get('/tables/statistics', [TableManagementController::class, 'getStatistics']);

    // Get table performance metrics
    Route::get('/tables/{table_id}/performance', [TableManagementController::class, 'getTablePerformance']);

    // Get occupancy heatmap data
    Route::get('/tables/occupancy-heatmap', [TableManagementController::class, 'getOccupancyHeatmap']);


    // ========================================================================
    // 6. BULK OPERATIONS
    // ========================================================================

    // Update multiple tables
    Route::put('/tables/bulk-update', [TableManagementController::class, 'bulkUpdate']);

    // Change status of multiple tables
    Route::put('/tables/bulk-status', [TableManagementController::class, 'bulkStatusChange']);

    // Export tables data (CSV/Excel/PDF)
    Route::get('/tables/export', [TableManagementController::class, 'exportTables']);


    // ========================================================================
    // 7. QUICK ACTIONS
    // ========================================================================

    // Quick reservation for walk-ins
    Route::post('/tables/quick-reserve', [TableManagementController::class, 'quickReserve']);

    // Mark table for maintenance
    Route::post('/tables/{table_id}/maintenance', [TableManagementController::class, 'markForMaintenance']);


    // ========================================================================
    // 8. TABLE COMBINATIONS
    // ========================================================================

    // Combine multiple tables
    Route::post('/tables/combine', [TableManagementController::class, 'combineTables']);

    // Split combined tables
    Route::post('/tables/combinations/{combination_id}/split', [TableManagementController::class, 'splitTables']);


    // ========================================================================
    // 9. WAITLIST MANAGEMENT
    // ========================================================================

    // Add customer to waitlist
    Route::post('/tables/waitlist', [TableManagementController::class, 'addToWaitlist']);

    // Get current waitlist
    Route::get('/tables/waitlist', [TableManagementController::class, 'getWaitlist']);

    // Notify waitlist customer
    Route::post('/tables/waitlist/{waitlist_id}/notify', [TableManagementController::class, 'notifyWaitlistCustomer']);

    // Update waitlist entry
    Route::put('/tables/waitlist/{waitlist_id}', [TableManagementController::class, 'updateWaitlist']);

    // Remove from waitlist
    Route::delete('/tables/waitlist/{waitlist_id}', [TableManagementController::class, 'removeFromWaitlist']);
});


// ============================================================================
// ALTERNATIVE: RESOURCE-BASED ROUTES (Optional Approach)
// ============================================================================

/*
 * If you prefer Laravel's resource routing, you can use this approach instead:
 * This provides standard CRUD operations with RESTful conventions
 */

Route::prefix('restaurants/{restaurant_id}')->middleware(['auth:sanctum', 'hassessionaccount'])->group(function () {

    // Tables Resource
    Route::apiResource('tables', TableManagementController::class);

    // Reservations Resource
    Route::apiResource('reservations', TableManagementController::class);

    // Additional custom routes
    Route::post('tables/{table_id}/occupy', [TableManagementController::class, 'markOccupied']);
    Route::post('tables/{table_id}/clear', [TableManagementController::class, 'clearTable']);
    Route::post('tables/{table_id}/cleaned', [TableManagementController::class, 'markCleaned']);
    Route::put('tables/{table_id}/status', [TableManagementController::class, 'updateStatus']);
    Route::get('tables/{table_id}/status-history', [TableManagementController::class, 'getStatusHistory']);
    Route::get('tables/{table_id}/performance', [TableManagementController::class, 'getTablePerformance']);

    // Floor management
    Route::get('floors', [TableManagementController::class, 'getFloors']);
    Route::get('floors/{floor_name}/layout', [TableManagementController::class, 'getFloorLayout']);
    Route::put('floors/{floor_name}/layout', [TableManagementController::class, 'updateFloorLayout']);
    Route::get('floors/{floor_name}/export', [TableManagementController::class, 'exportFloorPlan']);

    // Reservation operations
    Route::post('reservations/{reservation_id}/checkin', [TableManagementController::class, 'checkinReservation']);
    Route::put('reservations/{reservation_id}/cancel', [TableManagementController::class, 'cancelReservation']);
    Route::get('reservations/availability', [TableManagementController::class, 'checkAvailability']);

    // Analytics
    Route::get('tables/statistics', [TableManagementController::class, 'getStatistics']);
    Route::get('tables/occupancy-heatmap', [TableManagementController::class, 'getOccupancyHeatmap']);

    // Bulk operations
    Route::put('tables/bulk-update', [TableManagementController::class, 'bulkUpdate']);
    Route::put('tables/bulk-status', [TableManagementController::class, 'bulkStatusChange']);
    Route::get('tables/export', [TableManagementController::class, 'exportTables']);

    // Quick actions
    Route::post('tables/quick-reserve', [TableManagementController::class, 'quickReserve']);
    Route::post('tables/{table_id}/maintenance', [TableManagementController::class, 'markForMaintenance']);

    // Table combinations
    Route::post('tables/combine', [TableManagementController::class, 'combineTables']);
    Route::post('tables/combinations/{combination_id}/split', [TableManagementController::class, 'splitTables']);

    // Waitlist
    Route::post('tables/waitlist', [TableManagementController::class, 'addToWaitlist']);
    Route::get('tables/waitlist', [TableManagementController::class, 'getWaitlist']);
    Route::post('tables/waitlist/{waitlist_id}/notify', [TableManagementController::class, 'notifyWaitlistCustomer']);
    Route::put('tables/waitlist/{waitlist_id}', [TableManagementController::class, 'updateWaitlist']);
    Route::delete('tables/waitlist/{waitlist_id}', [TableManagementController::class, 'removeFromWaitlist']);
});


// ============================================================================
// ROUTE NAMING (Optional but Recommended)
// ============================================================================

/*
 * You can add route names for easier reference in your application:
 * Example: route('tables.index', ['restaurant_id' => $id])
 */

Route::prefix('restaurants/{restaurant_id}')->middleware(['auth:sanctum', 'hassessionaccount'])->name('tables.')->group(function () {
    Route::get('/tables', [TableManagementController::class, 'index'])->name('index');
    Route::post('/tables', [TableManagementController::class, 'store'])->name('store');
    Route::get('/tables/{table_id}', [TableManagementController::class, 'show'])->name('show');
    Route::put('/tables/{table_id}', [TableManagementController::class, 'update'])->name('update');
    Route::delete('/tables/{table_id}', [TableManagementController::class, 'destroy'])->name('destroy');

    // Status operations
    Route::put('/tables/{table_id}/status', [TableManagementController::class, 'updateStatus'])->name('status.update');
    Route::post('/tables/{table_id}/occupy', [TableManagementController::class, 'markOccupied'])->name('occupy');
    Route::post('/tables/{table_id}/clear', [TableManagementController::class, 'clearTable'])->name('clear');
    Route::post('/tables/{table_id}/cleaned', [TableManagementController::class, 'markCleaned'])->name('cleaned');

    // Add more named routes as needed...
});


// ============================================================================
// NOTES FOR IMPLEMENTATION
// ============================================================================

/*
 * 1. Controller Location:
 *    Create the controller at: app/Http/Controllers/TableManagementController.php
 *
 * 2. Middleware:
 *    - auth:sanctum: Ensures user is authenticated
 *    - hassessionaccount: Your custom middleware for session account validation
 *
 * 3. Route Parameters:
 *    - {restaurant_id}: The restaurant identifier
 *    - {table_id}: The table identifier
 *    - {reservation_id}: The reservation identifier
 *    - {floor_name}: Floor name (main, upstairs, outdoor, etc.)
 *    - {combination_id}: Table combination identifier
 *    - {waitlist_id}: Waitlist entry identifier
 *
 * 4. Request Validation:
 *    Create Form Request classes for validation:
 *    - app/Http/Requests/TableManagement/StoreTableRequest.php
 *    - app/Http/Requests/TableManagement/UpdateTableRequest.php
 *    - app/Http/Requests/TableManagement/CreateReservationRequest.php
 *    etc.
 *
 * 5. Response Format:
 *    All responses should follow the standard format:
 *    {
 *        "success": true/false,
 *        "data": {...},
 *        "message": "...",
 *        "pagination": {...}
 *    }
 *
 * 6. Error Handling:
 *    Implement proper error handling in the controller
 *    Return appropriate HTTP status codes
 *
 * 7. Database Tables Needed:
 *    - tables
 *    - table_status_history
 *    - reservations
 *    - floors
 *    - table_combinations
 *    - waitlist
 *
 * 8. Testing:
 *    Test routes using:
 *    php artisan route:list --name=tables
 *    php artisan route:list | grep tables
 */
