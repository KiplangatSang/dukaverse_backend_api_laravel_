<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model {
    use HasFactory, SoftDeletes;

    const DIRECT_SALE = 'order placed';

    const AWAITING_AUTHORIZATION = 'awaiting authorization';
    const FAILED                 = 'failed';
    const UNPAID                 = 'unpaid';
    const PAID                   = 'paid';

    const PAYSTATUS = [
        self::AWAITING_AUTHORIZATION,
        self::FAILED,
        self::UNPAID,
        self::PAID,
    ];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
    ];

    const TRANSACTIONSTRLEN = 4;
    //
    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function saleable()
    {
        return $this->morphTo();
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->belongsTo(RetailItem::class, "retail_item_id");
    }

    public function saleTransactions()
    {
        # code...
        return $this->belongsTo(SaleTransaction::class, 'sale_transaction_id');
    }

    public function accountTransactions()
    {
        return $this->morphMany(Transaction::class, "purposeable");
    }

    public static function retail()
    {
        $baseController = new BaseController();
        $retail         = $baseController->retail();
        return $retail;
    }

    public static function generateSaleTransactionId()
    {
        // Generate a random string
        $randomString = Str::random(self::TRANSACTIONSTRLEN);
// Get the current timestamp with hour precision
        $timestamp = Carbon::now()->format('YmdH');
// Combine both to create a unique transaction ID
        $transaction_id     = $randomString . $timestamp;
        $originalRetailName = self::retail()['retail_name'];
        $cleanRetailName    = str_replace(' ', '', $originalRetailName);

        $transaction_id = $cleanRetailName . $transaction_id;

        return $transaction_id;
    }

    public function media()
    {
        return $this->morphMany(Medium::class, 'mediumable');
    }
}
