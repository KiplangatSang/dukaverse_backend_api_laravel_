<?php
namespace App\Models;

use App\Casts\DateTimeCasting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
    ];

    //
    protected $guarded = [];

    public function roles()
    {
        return $this->morphToMany(Role::class, 'employeeroleables')
            ->withTimestamps()
            ->withPivot('role_id', 'employeeroleables_id', 'employeeroleables_type');
    }

    const FULL_TIME = 'full time';
    const FREELANCE = 'freelance';
    const CONTRACT  = 'contract';

    const EMPLOYEE_TYPE = [
        self::FULL_TIME,
        self::FREELANCE,
        self::CONTRACT,
    ];

    const ACTIVE    = 'active';
    const INACTIVE  = 'inactive';
    const SUSPENDED = 'suspended';
    const BLOCKED   = 'blocked';

    const EMPLOYEE_STATUS = [

        self::ACTIVE,
        self::INACTIVE,
        self::SUSPENDED,
        self::BLOCKED,
    ];

    public function ownerable()
    {

        return $this->morphTo();
    }

    public function employeeable()
    {

        return $this->morphTo();
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function requiredItems()
    {
        return $this->hasMany(RequiredItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function saleTransactions()
    {
        # code...
        return $this->hasMany(SaleTransaction::class, "employee_id");
    }

    public function media()
    {
        return $this->morphMany(Medium::class, 'mediumable');
    }

    public function teams()
    {
        return $this->morphedByMany(Team::class, 'teamables');
    }

    // protected $dispatchesEvents = [
    //     'created' => EmployeeUpdated::class,
    //     'update' => EmployeeUpdated::class,
    //     'deleted' => EmployeeUpdated::class,
    // ];
}
