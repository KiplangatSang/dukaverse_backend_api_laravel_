<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Restaurant;
use App\Models\Employee;
use App\Models\Table;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class RestaurantService extends BaseService
{
    public function __construct(
        StoreFileResource $storeFileResource,
        private ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    /**
     * Get restaurants with filtering
     */
    public function getRestaurants($filters = [])
    {
        try {
            $query = Restaurant::with(['owner', 'settings']);

            // Apply filters
            if (isset($filters['owner_id'])) {
                $query->where('ownerable_type', get_class(Auth::user()))
                     ->where('ownerable_id', $filters['owner_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            $restaurants = $query->orderBy('created_at', 'desc')
                               ->paginate($filters['limit'] ?? 20);

            return $this->responseHelper->respond($restaurants, 'Restaurants retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve restaurants', 500, [$e->getMessage()]);
        }
    }

    /**
     * Create a new restaurant
     */
    public function createRestaurant($data)
    {
        try {
            DB::beginTransaction();

            $restaurant = Restaurant::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'address' => $data['address'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'website' => $data['website'] ?? null,
                'logo_url' => $data['logo_url'] ?? null,
                'cover_image_url' => $data['cover_image_url'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => 'inactive',
                'ownerable_type' => get_class(Auth::user()),
                'ownerable_id' => Auth::id(),
            ]);

            // Create default settings
            $restaurant->settings()->create([
                'operating_hours' => $data['operating_hours'] ?? [
                    'monday' => ['open' => '09:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '22:00'],
                    'thursday' => ['open' => '09:00', 'close' => '22:00'],
                    'friday' => ['open' => '09:00', 'close' => '22:00'],
                    'saturday' => ['open' => '09:00', 'close' => '22:00'],
                    'sunday' => ['open' => '09:00', 'close' => '22:00'],
                ],
                'tax_rate' => $data['tax_rate'] ?? 16.0,
                'service_charge_rate' => $data['service_charge_rate'] ?? 0.0,
                'currency' => $data['currency'] ?? 'KES',
                'timezone' => $data['timezone'] ?? 'Africa/Nairobi',
                'max_tables' => $data['max_tables'] ?? 50,
                'max_capacity' => $data['max_capacity'] ?? 200,
            ]);

            DB::commit();

            return $this->responseHelper->respond(
                $restaurant->load('settings'),
                'Restaurant created successfully'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseHelper->error('Failed to create restaurant', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update restaurant
     */
    public function updateRestaurant($restaurantId, $data)
    {
        try {
            $restaurant = Restaurant::findOrFail($restaurantId);

            // Check ownership
            if ($restaurant->ownerable_id !== Auth::id()) {
                return $this->responseHelper->error('Unauthorized access to restaurant', 403);
            }

            DB::beginTransaction();

            $restaurant->update([
                'name' => $data['name'] ?? $restaurant->name,
                'description' => $data['description'] ?? $restaurant->description,
                'address' => $data['address'] ?? $restaurant->address,
                'phone' => $data['phone'] ?? $restaurant->phone,
                'email' => $data['email'] ?? $restaurant->email,
                'website' => $data['website'] ?? $restaurant->website,
                'logo_url' => $data['logo_url'] ?? $restaurant->logo_url,
                'cover_image_url' => $data['cover_image_url'] ?? $restaurant->cover_image_url,
                'latitude' => $data['latitude'] ?? $restaurant->latitude,
                'longitude' => $data['longitude'] ?? $restaurant->longitude,
                'status' => $data['status'] ?? $restaurant->status,
            ]);

            // Update settings if provided
            if (isset($data['settings'])) {
                $restaurant->settings()->update($data['settings']);
            }

            DB::commit();

            return $this->responseHelper->respond(
                $restaurant->fresh(['settings']),
                'Restaurant updated successfully'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseHelper->error('Failed to update restaurant', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get restaurant tables
     */
    public function getTables($restaurantId, $filters = [])
    {
        try {
            $restaurant = Restaurant::findOrFail($restaurantId);

            $query = Table::where('restaurant_id', $restaurantId);

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['capacity'])) {
                $query->where('capacity', '>=', $filters['capacity']);
            }

            if (isset($filters['section'])) {
                $query->where('section', $filters['section']);
            }

            $tables = $query->orderBy('table_number')->get();

            return $this->responseHelper->respond($tables, 'Tables retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve tables', 500, [$e->getMessage()]);
        }
    }

    /**
     * Create restaurant table
     */
    public function createTable($restaurantId, $data)
    {
        try {
            $restaurant = Restaurant::findOrFail($restaurantId);

            // Check ownership
            if ($restaurant->ownerable_id !== Auth::id()) {
                return $this->responseHelper->error('Unauthorized access to restaurant', 403);
            }

            $table = Table::create([
                'restaurant_id' => $restaurantId,
                'table_number' => $data['table_number'],
                'capacity' => $data['capacity'],
                'section' => $data['section'] ?? 'main',
                'features' => $data['features'] ?? [],
                'status' => 'available',
                'position_x' => $data['position_x'] ?? null,
                'position_y' => $data['position_y'] ?? null,
            ]);

            return $this->responseHelper->respond($table, 'Table created successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to create table', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update table status
     */
    public function updateTableStatus($restaurantId, $tableId, $status, $notes = null)
    {
        try {
            $table = Table::where('restaurant_id', $restaurantId)
                         ->findOrFail($tableId);

            $table->update([
                'status' => $status,
                'notes' => $notes,
            ]);

            return $this->responseHelper->respond($table, 'Table status updated successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to update table status', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get restaurant staff
     */
    public function getStaff($restaurantId, $filters = [])
    {
        try {
            $restaurant = Restaurant::findOrFail($restaurantId);

            $query = Employee::whereHas('roles', function($q) use ($restaurantId) {
                $q->where('ownerable_type', Restaurant::class)
                  ->where('ownerable_id', $restaurantId);
            });

            // Apply filters
            if (isset($filters['role'])) {
                $query->where('role', $filters['role']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                });
            }

            $staff = $query->with('user')->paginate($filters['limit'] ?? 20);

            return $this->responseHelper->respond($staff, 'Staff retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve staff', 500, [$e->getMessage()]);
        }
    }

    /**
     * Add staff to restaurant
     */
    public function addStaff($restaurantId, $data)
    {
        try {
            $restaurant = Restaurant::findOrFail($restaurantId);

            // Check ownership
            if ($restaurant->ownerable_id !== Auth::id()) {
                return $this->responseHelper->error('Unauthorized access to restaurant', 403);
            }

            DB::beginTransaction();

            $employee = Employee::create([
                'user_id' => $data['user_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role' => $data['role'],
                'status' => 'active',
                'hire_date' => now(),
                'hourly_rate' => $data['hourly_rate'] ?? 0,
                'permissions' => $data['permissions'] ?? [],
            ]);

            // Assign restaurant role
            $employee->roles()->attach($data['role_id'], [
                'ownerable_type' => Restaurant::class,
                'ownerable_id' => $restaurantId,
            ]);

            DB::commit();

            return $this->responseHelper->respond(
                $employee->load('roles'),
                'Staff member added successfully'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseHelper->error('Failed to add staff member', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get restaurant analytics
     */
    public function getAnalytics($restaurantId, $filters = [])
    {
        try {
            $restaurant = Restaurant::findOrFail($restaurantId);

            // Check ownership
            if ($restaurant->ownerable_id !== Auth::id()) {
                return $this->responseHelper->error('Unauthorized access to restaurant', 403);
            }

            $analytics = [
                'overview' => $this->getOverviewMetrics($restaurantId, $filters),
                'sales' => $this->getSalesAnalytics($restaurantId, $filters),
                'operations' => $this->getOperationalMetrics($restaurantId, $filters),
                'customers' => $this->getCustomerMetrics($restaurantId, $filters),
            ];

            return $this->responseHelper->respond($analytics, 'Analytics retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve analytics', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get overview metrics
     */
    private function getOverviewMetrics($restaurantId, $filters)
    {
        // Implementation for overview metrics
        return [
            'total_orders' => 0,
            'total_revenue' => 0,
            'average_order_value' => 0,
            'total_customers' => 0,
            'table_utilization' => 0,
            'staff_count' => 0,
        ];
    }

    /**
     * Get sales analytics
     */
    private function getSalesAnalytics($restaurantId, $filters)
    {
        // Implementation for sales analytics
        return [
            'daily_sales' => [],
            'popular_items' => [],
            'revenue_by_category' => [],
            'payment_methods' => [],
        ];
    }

    /**
     * Get operational metrics
     */
    private function getOperationalMetrics($restaurantId, $filters)
    {
        // Implementation for operational metrics
        return [
            'average_service_time' => 0,
            'table_turnover_rate' => 0,
            'kitchen_efficiency' => 0,
            'staff_performance' => [],
        ];
    }

    /**
     * Get customer metrics
     */
    private function getCustomerMetrics($restaurantId, $filters)
    {
        // Implementation for customer metrics
        return [
            'total_customers' => 0,
            'repeat_customers' => 0,
            'average_rating' => 0,
            'customer_segments' => [],
        ];
    }
}
