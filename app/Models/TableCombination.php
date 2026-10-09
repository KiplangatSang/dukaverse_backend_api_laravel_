<?php

namespace App\Models;

use App\Casts\DateTimeCasting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TableCombination extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'split_at' => 'datetime',
        'total_capacity' => 'integer',
        'party_size' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($combination) {
            if (empty($combination->uuid)) {
                $combination->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function tables()
    {
        return $this->belongsToMany(Table::class, 'combination_table')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('combination_table.order');
    }

    public function orderable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function splitBy()
    {
        return $this->belongsTo(User::class, 'split_by');
    }

    // Scopes
    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSplit($query)
    {
        return $query->where('status', 'split');
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsSplitAttribute()
    {
        return $this->status === 'split';
    }

    public function getTableNumbersAttribute()
    {
        return $this->tables->pluck('table_number')->toArray();
    }

    // Methods
    public function split($userId = null, $notes = null)
    {
        $this->update([
            'status' => 'split',
            'split_by' => $userId,
            'split_at' => now(),
            'notes' => $notes,
        ]);

        // Mark all tables as cleaning
        foreach ($this->tables as $table) {
            $table->update(['status' => 'cleaning']);
        }
    }

    public function calculateTotalCapacity()
    {
        return $this->tables()->sum('capacity');
    }
}
