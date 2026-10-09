<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Reservation;
use App\Models\Floor;
use App\Models\TableCombination;
use App\Models\Waitlist;
use App\Models\Restaurant;
use App\Services\TableManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Table Management",
 *     description="API Endpoints for Restaurant Table Management"
 * )
 */
class TableManagementController extends BaseController
{
    protected $tableService;

    public function __construct(TableManagementService $tableService)
    {
        parent::__construct(app(\App\Http\Resources\ApiResource::class));
        $this->tableService = $tableService;
    }

    // ========================================================================
    // TABLE MANAGEMENT
    // ========================================================================

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{restaurant_id}/tables",
     *     summary="Get all tables",
     *     description="Retrieve all tables for a restaurant with filtering and pagination",
     *     operationId="getTables",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="path",
     *         description="Restaurant ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"available", "occupied", "reserved", "cleaning", "out_of_order"})
     *     ),
     *     @OA\Parameter(
     *         name="floor",
     *         in="query",
     *         description="Filter by floor",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="section",
     *         in="query",
     *         description="Filter by section",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="capacity",
     *         in="query",
     *         description="Minimum capacity",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request, $restaurant_id)
    {
        try {
            $filters = $request->only(['status', 'floor', 'section', 'capacity', 'capacity_min', 'capacity_max', 'sort_by', 'sort_order', 'limit']);

            $tables = $this->tableService->getTables($restaurant_id, $filters);

            return $this->sendResponse($tables, 'Tables retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving tables', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{restaurant_id}/tables/{table_id}",
     *     summary="Get table details",
     *     description="Retrieve detailed information about a specific table",
     *     operationId="getTable",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="table_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
      *     ),
     *     @OA\Response(response=404, description="Table not found")
     * )
     */
    public function show($restaurant_id, $table_id)
    {
        try {
            $table = Table::byRestaurant($restaurant_id)
                ->with(['floor', 'currentReservation', 'statusHistory' => function ($query) {
                    $query->limit(10);
                }])
                ->findOrFail($table_id);

            return $this->sendResponse($table, 'Table retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Table not found', [$e->getMessage()], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/restaurants/{restaurant_id}/tables",
     *     summary="Create new table",
     *     description="Add a new table to the restaurant",
     *     operationId="createTable",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"table_number", "capacity", "section"},
     *             @OA\Property(property="table_number", type="string", example="5"),
     *             @OA\Property(property="capacity", type="integer", example=4),
     *             @OA\Property(property="section", type="string", example="window"),
     *             @OA\Property(property="floor", type="string", example="main"),
     *             @OA\Property(property="floor_id", type="integer", example=1),
     *             @OA\Property(property="position_x", type="number", example=50.5),
     *             @OA\Property(property="position_y", type="number", example=30.2),
     *             @OA\Property(property="features", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Table created successfully",
      *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request, $restaurant_id)
    {
        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string',
            'capacity' => 'required|integer|min:1|max:20',
            'section' => 'required|string|in:main,patio,private,bar,lounge,outdoor',
            'floor' => 'nullable|string',
            'floor_id' => 'nullable|exists:floors,id',
            'position_x' => 'nullable|numeric|min:0|max:100',
            'position_y' => 'nullable|numeric|min:0|max:100',
            'features' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            // Check for duplicate table number
            $exists = Table::byRestaurant($restaurant_id)
                ->where('table_number', $request->table_number)
                ->exists();

            if ($exists) {
                return $this->sendError('Table number already exists', ['table_number' => 'This table number is already in use'], 422);
            }

            $table = Table::create(array_merge($request->all(), [
                'restaurant_id' => $restaurant_id,
                'status' => 'available',
            ]));

            return $this->sendResponse($table, 'Table created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating table', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/restaurants/{restaurant_id}/tables/{table_id}",
     *     summary="Update table",
     *     description="Update table information",
     *     operationId="updateTable",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="table_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="table_number", type="string"),
     *             @OA\Property(property="capacity", type="integer"),
     *             @OA\Property(property="section", type="string"),
     *             @OA\Property(property="position_x", type="number"),
     *             @OA\Property(property="position_y", type="number"),
     *             @OA\Property(property="features", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Table updated successfully"
     *     ),
     *     @OA\Response(response=404, description="Table not found")
     * )
     */
    public function update(Request $request, $restaurant_id, $table_id)
    {
        $validator = Validator::make($request->all(), [
            'table_number' => 'sometimes|string',
            'capacity' => 'sometimes|integer|min:1|max:20',
            'section' => 'sometimes|string|in:main,patio,private,bar,lounge,outdoor',
            'floor' => 'nullable|string',
            'floor_id' => 'nullable|exists:floors,id',
            'position_x' => 'nullable|numeric|min:0|max:100',
            'position_y' => 'nullable|numeric|min:0|max:100',
            'features' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            $table = Table::byRestaurant($restaurant_id)->findOrFail($table_id);

            // Check for duplicate table number if changing
            if ($request->has('table_number') && $request->table_number != $table->table_number) {
                $exists = Table::byRestaurant($restaurant_id)
                    ->where('table_number', $request->table_number)
                    ->where('id', '!=', $table_id)
                    ->exists();

                if ($exists) {
                    return $this->sendError('Table number already exists', ['table_number' => 'This table number is already in use'], 422);
                }
            }

            $table->update($request->all());

            return $this->sendResponse($table->fresh(), 'Table updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error updating table', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/restaurants/{restaurant_id}/tables/{table_id}",
     *     summary="Delete table",
     *     description="Remove a table from the system",
     *     operationId="deleteTable",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="table_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         description="Force delete even if occupied",
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Table deleted successfully"
     *     ),
     *     @OA\Response(response=409, description="Table is occupied")
     * )
     */
    public function destroy(Request $request, $restaurant_id, $table_id)
    {
        try {
            $table = Table::byRestaurant($restaurant_id)->findOrFail($table_id);

            // Check if table is occupied
            if (!$request->boolean('force') && in_array($table->status, ['occupied', 'reserved'])) {
                return $this->sendError('Cannot delete occupied table', [
                    'table_id' => $table_id,
                    'status' => $table->status,
                    'message' => 'Table is currently ' . $table->status
                ], 409);
            }

            $table->delete();

            return $this->sendResponse([], 'Table deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error deleting table', [$e->getMessage()], 500);
        }
    }

    // ========================================================================
    // TABLE STATUS OPERATIONS
    // ========================================================================

    /**
     * @OA\Put(
     *     path="/api/v1/restaurants/{restaurant_id}/tables/{table_id}/status",
     *     summary="Update table status",
     *     description="Change the status of a table",
     *     operationId="updateTableStatus",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="table_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"available", "occupied", "reserved", "cleaning", "maintenance", "out_of_order"}),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="order_id", type="integer"),
     *             @OA\Property(property="waiter_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully"
     *     )
     * )
     */
    public function updateStatus(Request $request, $restaurant_id, $table_id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,occupied,reserved,cleaning,maintenance,out_of_order',
            'notes' => 'nullable|string',
            'order_id' => 'nullable|integer',
            'waiter_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            $table = Table::byRestaurant($restaurant_id)->findOrFail($table_id);

            $table->updateStatus(
                $request->status,
                $request->notes,
                null,
                Auth::id()
            );

            return $this->sendResponse($table->fresh(), 'Table status updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error updating status', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/restaurants/{restaurant_id}/tables/{table_id}/occupy",
     *     summary="Mark table as occupied",
     *     description="Mark a table as occupied and optionally create an order",
     *     operationId="markTableOccupied",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_name", type="string"),
     *             @OA\Property(property="customer_phone", type="string"),
     *             @OA\Property(property="party_size", type="integer"),
     *             @OA\Property(property="waiter_id", type="integer"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Table marked as occupied")
     * )
     */
    public function markOccupied(Request $request, $restaurant_id, $table_id)
    {
        try {
            $table = Table::byRestaurant($restaurant_id)->findOrFail($table_id);

            if ($table->status !== 'available') {
                return $this->sendError('Table not available', [
                    'current_status' => $table->status
                ], 409);
            }

            $table->markAsOccupied($request->notes);

            // TODO: Create order if needed

            return $this->sendResponse([
                'table' => $table->fresh(),
                'message' => 'Table marked as occupied'
            ], 'Table marked as occupied successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error marking table as occupied', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/restaurants/{restaurant_id}/tables/{table_id}/clear",
     *     summary="Clear table",
     *     description="Clear a table and mark it for cleaning or available",
     *     operationId="clearTable",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer"),
     *             @OA\Property(property="payment_completed", type="boolean"),
     *             @OA\Property(property="requires_cleaning", type="boolean"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Table cleared successfully")
     * )
     */
    public function clearTable(Request $request, $restaurant_id, $table_id)
    {
        try {
            $table = Table::byRestaurant($restaurant_id)->findOrFail($table_id);

            $newStatus = $request->boolean('requires_cleaning', true) ? 'cleaning' : 'available';

            $table->updateStatus($newStatus, $request->notes, null,Auth::id());

            return $this->sendResponse([
                'table' => $table->fresh(),
                'status' => $newStatus
            ], 'Table cleared successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error clearing table', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/restaurants/{restaurant_id}/tables/{table_id}/cleaned",
     *     summary="Mark table as cleaned",
     *     description="Mark a table as cleaned and available",
     *     operationId="markTableCleaned",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="cleaned_by", type="integer"),
     *             @OA\Property(property="cleaning_notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Table marked as cleaned")
     * )
     */
    public function markCleaned(Request $request, $restaurant_id, $table_id)
    {
        try {
            $table = Table::byRestaurant($restaurant_id)->findOrFail($table_id);

            $table->markAsAvailable($request->cleaning_notes);

            return $this->sendResponse($table->fresh(), 'Table marked as cleaned and available');
        } catch (\Exception $e) {
            return $this->sendError('Error marking table as cleaned', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{restaurant_id}/tables/{table_id}/status-history",
     *     summary="Get table status history",
     *     description="Retrieve status change history for a table",
     *     operationId="getTableStatusHistory",
     *     tags={"Table Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="restaurant_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="table_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=50)),
     *     @OA\Response(response=200, description="Status history retrieved")
     * )
     */
    public function getStatusHistory(Request $request, $restaurant_id, $table_id)
    {
        try {
            $limit = $request->input('limit', 50);

            $history = \App\Models\TableStatusHistory::byTable($table_id)
                ->with('changedBy:id,name')
                ->orderBy('changed_at', 'desc')
                ->limit($limit)
                ->get();

            return $this->sendResponse($history, 'Status history retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving status history', [$e->getMessage()], 500);
        }
    }

    // Continue in next part...
}
