<?php
namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItemCustomization extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'sort_order' => 'integer',
        'options' => JsonCasing::class,
    ];

    // Relationships
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    // Scopes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getIsMultipleSelectionAttribute()
    {
        return in_array($this->type, ['multiple', 'quantity']);
    }

    public function getIsSingleSelectionAttribute()
    {
        return $this->type === 'single';
    }

    // Methods
    public function validateSelection($selectedOptions)
    {
        $errors = [];

        // Check minimum selections
        if ($this->min_selections > 0 && count($selectedOptions) < $this->min_selections) {
            $errors[] = "Minimum {$this->min_selections} selections required";
        }

        // Check maximum selections
        if ($this->max_selections > 0 && count($selectedOptions) > $this->max_selections) {
            $errors[] = "Maximum {$this->max_selections} selections allowed";
        }

        // Validate selected options exist
        foreach ($selectedOptions as $selectedOption) {
            $optionExists = collect($this->options)->contains('id', $selectedOption['id']);
            if (!$optionExists) {
                $errors[] = "Invalid option selected";
                break;
            }
        }

        return $errors;
    }

    public function calculatePriceModifier($selectedOptions)
    {
        $totalModifier = 0;

        foreach ($selectedOptions as $selectedOption) {
            $option = collect($this->options)->firstWhere('id', $selectedOption['id']);
            if ($option && isset($option['price_modifier'])) {
                $totalModifier += $option['price_modifier'];
            }
        }

        return $totalModifier;
    }

    // Constants
    const TYPE_SINGLE = 'single';
    const TYPE_MULTIPLE = 'multiple';
    const TYPE_QUANTITY = 'quantity';

    const TYPES = [
        self::TYPE_SINGLE,
        self::TYPE_MULTIPLE,
        self::TYPE_QUANTITY,
    ];
}
