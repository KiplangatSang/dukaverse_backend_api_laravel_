<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model {
    use HasFactory, SoftDeletes;
    const INVOICELEN = 10;

    const PENDING_STATUS   = "pending";
    const PAID_STATUS      = "paid";
    const CANCELLED_STATUS = "cancelled";
    const CASH_TYPE        = "Cash Invoice";

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function invoiceable()
    {

        return $this->morphTo();
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function saleTransaction()
    {
        return $this->belongsTo(SaleTransaction::class, 'sale_transaction_id');
    }

    public static function generateInvoiceNumber()
    {
        $randomString   = Str::random(self::INVOICELEN);
        $timestamp      = Carbon::now()->format('YmdH');
        $invoice_number = $randomString . $timestamp;

        $invoice_number = "INV_" . $invoice_number;

        return $invoice_number;
    }

}
