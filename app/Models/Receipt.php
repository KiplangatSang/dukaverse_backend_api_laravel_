<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model {
    use HasFactory, SoftDeletes;
    const RECEIPTLEN = 5;

    const CASH_TYPE     = "Cash Payment";
    const CARD_TYPE     = "Card Payment";
    const TRANSFER_TYPE = "Transfer Payment";
    const CASHBACK_TYPE = "Cashback Payment";
    const VOUCHER_TYPE  = "Voucher Payment";
    const DISCOUNT_TYPE = "Discount Payment";
    const CREDIT_TYPE   = "Credit Payment";

    protected $guarded = [];

    public function receiptable()
    {
        return $this->morphTo();
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function saleTransaction()
    {

        return $this->belongsTo(SaleTransaction::class, 'sale_transaction_id');

    }
    public function transaction()
    {

        return $this->belongsTo(Transaction::class, 'transaction_id');

    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public static function generateReceiptNumber()
    {
        $randomString   = Str::random(self::RECEIPTLEN);
        $timestamp      = Carbon::now()->format('YmdH');
        $receipt_number = $randomString . $timestamp;

        $receipt_number = "RCP_" . $receipt_number;

        return $receipt_number;
    }

}
