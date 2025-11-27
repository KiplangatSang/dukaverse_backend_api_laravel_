<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function loanable()
    {
        return $this->morphTo();
    }

    public function applications()
    {
        # code...
        return $this->hasMany(LoanApplication::class);
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

}
