<?php

namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'table_status_history';

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'changed_at' => 'datetime',
        'metadata' => JsonCasing::class,
    ];

    // Relationships
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function related()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByTable($query, $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('new_status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('changed_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('changed_at', 'desc')->limit($limit);
    }
}
