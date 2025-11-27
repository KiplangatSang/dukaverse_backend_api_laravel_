<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class Office extends Model
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function officeable()
    {

        return $this->morphTo();
    }

    public function owners()
    {
        return $this->ownerable();
    }

    public function user()
    {
        return $this->owners()->where('ownerable_type', User::class)->where('ownerable_id', Auth::id());
    }

    public function registeredOwner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner()
    {
        return $this->registeredOwner();
    }

    public function officeOwner()
    {
        return $this->registeredOwner();
    }

    public function officeOwners()
    {
        return $this->ownerable();
    }

    public static function employeePermissions()
    {
        # code...
        $permissions = Role::EMPLOYEE_PERMISSIONS;
        return $permissions;
    }

    public function profile()
    {
        # code...
        return $this->morphOne(Profile::class, 'profileable');
    }

    public function sales()
    {
        return $this->morphMany(Sale::class, 'ownerable');
    }

    public function stocks()
    {
        return $this->morphMany(Stock::class, 'ownerable');
    }

    public function employees()
    {
        return $this->morphMany(Employee::class, 'ownerable');
    }

    public function orders()
    {
        return $this->morphMany(Order::class, 'ownerable');
    }

    public function crossOrders()
    {
        return $this->morphMany(Order::class, 'orderables');
    }

    public function bills()
    {
        return $this->morphToMany(Bill::class, 'ownerable');
    }

    public function customers()
    {

        return $this->morphMany(Customer::class, 'ownerable');
    }

    public function crossCustomers()
    {

        return $this->morphToMany(Customer::class, 'customerables');
    }

    public function supplies()
    {
        return $this->morphMany(Supply::class, 'ownerable');
    }

    public function requiredItems()
    {
        return $this->morphMany(RequiredItem::class, 'ownerable');
    }

    public function expenses()
    {
        # code...
        return $this->morphMany(Expense::class, 'ownerable');
    }

    public function revenues()
    {
        # code...
        return $this->morphMany(Revenue::class, 'ownerable');
    }

    public function profit()
    {
        # code...
        return $this->morphMany(Profit::class, 'ownerable');
    }

    public function saleTransactions()
    {
        # code...
        return $this->morphMany(SaleTransaction::class, 'ownerable');
    }

    public function loanApplications()
    {
        return $this->morphMany(LoanApplication::class, 'ownerable');
    }
    public function loans()
    {
        return $this->morphMany(Loan::class, 'ownerable');
    }

    public function items()
    {
        return $this->morphMany(RetailItem::class, 'ownerable');
    }

    public function retailItem()
    {
        return $this->morphMany(RetailItem::class, 'ownerable');
    }

    public function accountTransactions()
    {
        return $this->morphMany(Transaction::class, "ownerable");
    }

    public function transactions()
    {
        return $this->accountTransactions();
    }

    public function accounts()
    {
        return $this->morphMany(Account::class, "ownerable");
    }

    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, "ownerable");
    }
    public function customerCredits()
    {
        return $this->morphMany(CustomerCredit::class, "ownerable");
    }

    public function messages()
    {
        return $this->morphMany(Message::class, "messageable");
    }

    public function roles()
    {
        return $this->morphMany(Role::class, 'ownerable');
    }

    public function saleSetting()
    {
        return $this->morphOne(SaleSetting::class, 'ownerable');
    }

    public function reciepts()
    {
        return $this->morphMany(Receipt::class, 'ownerable');
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'ownerable');
    }

    public function shippingMethods()
    {
        return $this->morphMany(ShippingMethod::class, 'ownerable');
    }

    public function shippings()
    {
        return $this->morphMany(Shipping::class, 'ownerable');
    }

    public function todos()
    {
        return $this->morphMany(Todo::class, 'ownerable');
    }

    public function media()
    {
        return $this->morphMany(Medium::class, 'ownerable');
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'ownerable');
    }

    public function teams()
    {
        return $this->morphMany(Team::class, 'ownerable');
    }

    public function projects()
    {
        return $this->morphMany(Project::class, 'ownerable');
    }

    public function campaigns()
    {
        return $this->morphMany(Campaign::class, 'ownerable');
    }

    public function officeCampaigns()
    {
        return $this->morphMany(Campaign::class, 'campaignable');
    }

    public function leads()
    {
        return $this->morphMany(Lead::class, 'ownerable');
    }

    public function activities()
    {
        return $this->morphMany(UserActivity::class, 'ownerable');

    }

    public function paymentGateways()
    {
        return $this->morphMany(PaymentGateway::class, 'ownerable');

    }

}
