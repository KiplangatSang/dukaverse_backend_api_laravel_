<?php
namespace App\Models;

use App\Models\Ecommerce;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model {
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    const THEMES = ['light', 'dark', 'auto'];

    public function settingable()
    {
        return $this->morphTo();
    }

    public function retail()
    {
        return $this->settingable()->where(
            'settingable_type', Retail::class
        );
    }

    public function ecommerce()
    {
        return $this->settingable()->where(
            'settingable_type', Ecommerce::class
        );
    }

    public function user()
    {
        return $this->settingable()->where(
            'settingable_type', User::class
        );
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

}
