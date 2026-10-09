<?php

namespace App\Services;

use App\Models\Table;
use App\Models\Reservation;
use App\Models\Floor;
use App\Models\TableCombination;
use App\Models\Waitlist;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TableManagementService
{
    /**
     * Get tables with filters and statistics
     */
    public function getTables($restaurantId, array $filters = [])
    {
        $query = Table::byRestaurant($restaurantId)
            ->with(['floor', 'currentReservation']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['floor'])) {
            $query->where('floor', $filters['floor']);
        }

        if (isset($filters['section'])) {
            $query->where('section', $filters['section']);
        }

        if (isset($filters['capacity'])) {
            $query->where('capacity', '>=', $filters['capacity']);
        }

        if (isset($filters['capacity_min']) && isset($filters['capacity_max'])) {
            $query->byCapacity($filters['capacity_min'], $filters['capacity_max']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'table_number';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['limit'] ?? 20);
    }

    /**
     * Get table statistics
     */
    public function getTableStatistics($restaurantId, array $filters = [])
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $tables = Table::byRestaurant($restaurantId);

        // Apply date filters if provided
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            // Statistics for date range would require order/transaction data
        }

        $stats = [
            'overview' => [
                'total_tables' => $tables->count(),
                'total_capacity' => $tables->sum('capacity'),
                'current_occupancy' => [
                    'occupied_tables' => $tables->where('status', 'occupied')->count(),
                    'occupied_seats' => $tables->where('status', 'occupied')->sum('capacity'),
                    'occupancy_rate' => $this->calculateOccupancyRate($restaurantId),
                    'seat_occupancy_rate' => $this->calculateSeatOccupancyRate($restaurantId),
                ],
            ],
            'by_status' => [
                'available' => $tables->where('status', 'available')->count(),
                'occupied' => $tables->where('status', 'occupied')->count(),
                'reserved' => $tables->where('status', 'reserved')->count(),
                'cleaning' => $tables->where('status', 'cleaning')->count(),
                'out_of_order' => $tables->where('status', 'out_of_order')->count(),
            ],
            'by_floor' => $this->getStatsByFloor($restaurantId),
            'by_section' => $this->getStatsBySection($restaurantId),
        ];

        return $stats;
    }

    /**
     * Calculate occupancy rate
     */
    protected function calculateOccupancyRate($restaurantId)
    {
        $total = Table::byRestaurant($restaurantId)->count();
        $occupied = Table::byRestaurant($restaurantId)
            ->whereIn('status', ['occupied', 'reserved'])
            ->count();

        return $total > 0 ? round(($occupied / $total) * 100, 2) : 0;
    }

    /**
     * Calculate seat occupancy rate
     */
    protected function calculateSeatOccupancyRate($restaurantId)
    {
        $totalSeats = Table::byRestaurant($restaurantId)->sum('capacity');
        $occupiedSeats = Table::byRestaurant($restaurantId)
            ->whereIn('status', ['occupied', 'reserved'])
            ->sum('capacity');

        return $totalSeats > 0 ? round(($occupiedSeats / $totalSeats) * 100, 2) : 0;
    }

    /**
     * Get statistics by floor
     */
    protected function getStatsByFloor($restaurantId)
    {
        return Floor::byRestaurant($restaurantId)
            ->with('tables')
            ->get()
            ->map(function ($floor) {
                $tables = $floor->tables;
                return [
                    'floor' => $floor->name,
                    'total_tables' => $tables->count(),
                    'occupied' => $tables->where('status', 'occupied')->count(),
                    'occupancy_rate' => $tables->count() > 0
                        ? round(($tables->where('status', 'occupied')->count() / $tables->count()) * 100, 2)
                        : 0,
                ];
            });
    }

    /**
     * Get statistics by section
     */
    protected function getStatsBySection($restaurantId)
    {
        return Table::byRestaurant($restaurantId)
            ->select('section', DB::raw('count(*) as table_count'))
            ->groupBy('section')
            ->get()
            ->map(function ($item) use ($restaurantId) {
                $occupied = Table::byRestaurant($restaurantId)
                    ->where('section', $item->section)
                    ->where('status', 'occupied')
                    ->count();

                return [
                    'section' => $item->section,
                    'table_count' => $item->table_count,
                    'occupancy_rate' => $item->table_count > 0
                        ? round(($occupied / $item->table_count) * 100, 2)
                        : 0,
                ];
            });
    }

    /**
     * Check table availability for reservation
     */
    public function checkAvailability($restaurantId, $date, $time, $partySize, $duration = 120)
    {
        $reservationDatetime = Carbon::parse("$date $time");
        $endDatetime = $reservationDatetime->copy()->addMinutes($duration);

        // Find tables that can accommodate the party size
        $suitableTables = Table::byRestaurant($restaurantId)
            ->where('capacity', '>=', $partySize)
            ->where('status', '!=', 'out_of_order')
            ->get();

        $availableTables = [];

        foreach ($suitableTables as $table) {
            // Check if table has conflicting reservations
            $hasConflict = Reservation::where('table_id', $table->id)
                ->whereIn('status', ['confirmed', 'seated'])
                ->where(function ($query) use ($reservationDatetime, $endDatetime) {
                    $query->whereBetween('reservation_datetime', [$reservationDatetime, $endDatetime])
                        ->orWhere(function ($q) use ($reservationDatetime, $endDatetime) {
                            $q->where('reservation_datetime', '<=', $reservationDatetime)
                              ->whereRaw('DATE_ADD(reservation_datetime, INTERVAL duration_minutes MINUTE) >= ?', [$reservationDatetime]);
                        });
                })
                ->exists();

            if (!$hasConflict) {
                $availableTables[] = $table;
            }
        }

        return [
            'is_available' => count($availableTables) > 0,
            'available_tables' => $availableTables,
            'alternative_times' => $this->suggestAlternativeTimes($restaurantId, $date, $partySize),
        ];
    }

    /**
     * Suggest alternative times
     */
    protected function suggestAlternativeTimes($restaurantId, $date, $partySize)
    {
        $alternatives = [];
        $baseDate = Carbon::parse($date);

        // Check availability for different time slots
        $timeSlots = ['11:00', '12:00', '13:00', '18:00', '19:00', '20:00', '21:00'];

        foreach ($timeSlots as $time) {
            $availability = $this->checkAvailability($restaurantId, $date, $time, $partySize);
            if ($availability['is_available']) {
                $alternatives[] = [
                    'time' => $time,
                    'available_tables' => count($availability['available_tables']),
                ];
            }
        }

        return $alternatives;
    }

    /**
     * Combine tables
     */
    public function combineTables($restaurantId, array $tableIds, $combinedName, $partySize, $userId = null)
    {
        return DB::transaction(function () use ($restaurantId, $tableIds, $combinedName, $partySize, $userId) {
            $tables = Table::byRestaurant($restaurantId)
                ->whereIn('id', $tableIds)
                ->get();

            // Validate all tables are available
            foreach ($tables as $table) {
                if ($table->status !== 'available') {
                    throw new \Exception("Table {$table->table_number} is not available");
                }
            }

            $totalCapacity = $tables->sum('capacity');

            // Create combination
            $combination = TableCombination::create([
                'restaurant_id' => $restaurantId,
                'combined_name' => $combinedName,
                'total_capacity' => $totalCapacity,
                'party_size' => $partySize,
                'status' => 'active',
                'created_by' => $userId,
            ]);

            // Attach tables
            foreach ($tables as $index => $table) {
                $combination->tables()->attach($table->id, ['order' => $index + 1]);
                $table->markAsOccupied("Combined as {$combinedName}", $combination);
            }

            return $combination->load('tables');
        });
    }

    /**
     * Split combined tables
     */
    public function splitTables($combinationId, $userId = null, $notes = null)
    {
        return DB::transaction(function () use ($combinationId, $userId, $notes) {
            $combination = TableCombination::with('tables')->findOrFail($combinationId);

            $combination->split($userId, $notes);

            return $combination;
        });
    }

    /**
     * Add to waitlist
     */
    public function addToWaitlist($restaurantId, array $data)
    {
        return Waitlist::create(array_merge($data, [
            'restaurant_id' => $restaurantId,
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Get waitlist
     */
    public function getWaitlist($restaurantId, $status = 'waiting')
    {
        $query = Waitlist::byRestaurant($restaurantId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('position')->get();
    }

    /**
     * Notify waitlist customer
     */
    public function notifyWaitlistCustomer($waitlistId, $tableId, $userId = null, $message = null)
    {
        $waitlist = Waitlist::findOrFail($waitlistId);
        $waitlist->notify($tableId, $userId, $message);

        return $waitlist;
    }

    /**
     * Get floor layout
     */
    public function getFloorLayout($restaurantId, $floorName)
    {
        $floor = Floor::byRestaurant($restaurantId)
            ->where('name', $floorName)
            ->with(['tables' => function ($query) {
                $query->with('currentReservation');
            }])
            ->firstOrFail();

        return [
            'floor_name' => $floor->name,
            'display_name' => $floor->display_name,
            'dimensions' => [
                'width' => $floor->width,
                'height' => $floor->height,
            ],
            'elements' => $floor->elements,
            'tables' => $floor->tables,
            'statistics' => [
                'total_tables' => $floor->tables->count(),
                'total_capacity' => $floor->tables->sum('capacity'),
                'available_tables' => $floor->tables->where('status', 'available')->count(),
                'occupied_tables' => $floor->tables->where('status', 'occupied')->count(),
                'reserved_tables' => $floor->tables->where('status', 'reserved')->count(),
            ],
        ];
    }

    /**
     * Update floor layout
     */
    public function updateFloorLayout($restaurantId, $floorName, array $data)
    {
        return DB::transaction(function () use ($restaurantId, $floorName, $data) {
            $floor = Floor::byRestaurant($restaurantId)
                ->where('name', $floorName)
                ->firstOrFail();

            // Update floor dimensions and elements
            if (isset($data['dimensions'])) {
                $floor->update([
                    'width' => $data['dimensions']['width'] ?? $floor->width,
                    'height' => $data['dimensions']['height'] ?? $floor->height,
                ]);
            }

            if (isset($data['elements'])) {
                $floor->update(['elements' => $data['elements']]);
            }

            // Update table positions
            if (isset($data['table_positions'])) {
                foreach ($data['table_positions'] as $tablePosition) {
                    Table::where('id', $tablePosition['table_id'])
                        ->update([
                            'position_x' => $tablePosition['position']['x'],
                            'position_y' => $tablePosition['position']['y'],
                        ]);
                }
            }

            return $floor->fresh(['tables']);
        });
    }
}
