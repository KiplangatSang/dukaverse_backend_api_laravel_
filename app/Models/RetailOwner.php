<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetailOwner extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function retails()
    {
        return $this->morphedByMany(Retail::class, 'retailownerable');
    }

    public function retailownerable()
    {
        return $this->morphedByMany(Retail::class, 'retailownerable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    const SALESREPORT               = "SALESREPORT";
    const EXPENSENOTIFICATION       = "EXPENSENOTIFICATION";
    const REQUIREDITEMSNOTIFICATION = "REQUIREDITEMSNOTIFICATION";
    const ORDERSNOTIFICATION        = "ORDERSNOTIFICATION";
    const SUPPLIESNOTIFICATION      = "SUPPLIESNOTIFICATION";
    const EMPLOYEESNOTIFICATION     = "EMPLOYEESNOTIFICATION";
    const EMPLOYEESALESNOTIFICATION = "EMPLOYEESALESNOTIFICATION";
    const FINANCEREPORTNOTIFICATION = "FINANCEREPORTNOTIFICATION";

    public function notifyRetailOwner($notification_type, $notification)
    {
        switch ($notification_type) {
            case $notification_type == self::SALESREPORT:
                return $this->sendSalesReportNotification($notification);
            case $notification_type == self::EXPENSENOTIFICATION:
                return $this->sendExpenseReportNotification($notification);
            case $notification_type == self::REQUIREDITEMSNOTIFICATION:
                return $this->sendRequiredItemsNotification($notification);
            case $notification_type == self::ORDERSNOTIFICATION:
                return $this->sendOrdersNotification($notification);
            case $notification_type == self::SUPPLIESNOTIFICATION:
                return $this->sendSuppliesNotification($notification);
            case $notification_type == self::EMPLOYEESNOTIFICATION:
                return $this->sendEmployeesNotification($notification);
            case $notification_type == self::EMPLOYEESALESNOTIFICATION:
                return $this->sendEmployeesSalesNotification($notification);
            case $notification_type == self::FINANCEREPORTNOTIFICATION:
                return $this->sendFinanceReportNotification($notification);
        }

    }

    private function sendAddedStockNotification($notification)
    {
        return $this->user->notify($notification);
    }

    private function sendSalesReportNotification($notification)
    {
        return $this->user->notify($notification);

    }

    private function sendExpenseReportNotification($notification)
    {
        return $this->user->notify($notification);

    }

    private function sendRequiredItemsNotification($notification)
    {
        return $this->user->notify($notification);

    }

    private function sendOrdersNotification($notification)
    {
        return $this->user->notify($notification);

    }
    private function sendSuppliesNotification($notification)
    {
        return $this->user->notify($notification);

    }

    private function sendEmployeesNotification($notification)
    {
        return $this->user->notify($notification);

    }

    private function sendEmployeesSalesNotification($notification)
    {
        return $this->user->notify($notification);

    }

    private function sendFinanceReportNotification($notification)
    {
        return $this->user->notify($notification);

    }

}
