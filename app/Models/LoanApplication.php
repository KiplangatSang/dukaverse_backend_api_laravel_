<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApplication extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function loanapplicable()
    {
        return $this->morphTo();
    }

    public function loans()
    {
        # code...
        return $this->belongsTo(Loan::class, 'loans_id');
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

}
