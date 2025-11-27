<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserActivity extends Model
{
    use HasFactory, Notifiable;

    const LOGIN_TOKEN_LENGTH = 6;
    const API_TOKEN_LENGTH   = 36;
    const PROFILE_FOLDER     = "/users/profiles/";
    const DUKAVERSE_ID       = 1;

    const DUKAVERSE_ADMIN_ACCOUNT_TYPE    = "admin";
    const ADMIN_ACCOUNT_TYPE              = "admin";
    const DUKAVERSE_EMPLOYEE_ACCOUNT_TYPE = "employee";
    const EMPLOYEE_ACCOUNT_TYPE           = "employee";
    const RETAILER_ACCOUNT_TYPE           = "retailer";
    const RETAIL_EMPLOYEE_ACCOUNT_TYPE    = "retail_employee";
    const CUSTOMER_ACCOUNT_TYPE           = "customer";
    const SUPPLIER_ACCOUNT_TYPE           = "supplier";
    const SUPPLIER_EMPLOYEE_ACCOUNT_TYPE  = "supplier_employee";
    const LEAD_ACCOUNT_TYPE               = "lead";
    const CONTRACTOR_ACCOUNT_TYPE         = "contractor";

    const ROLETYPES = [
        self::ADMIN_ACCOUNT_TYPE              => self::ADMIN_ACCOUNT_TYPE,
        self::DUKAVERSE_EMPLOYEE_ACCOUNT_TYPE => self::DUKAVERSE_EMPLOYEE_ACCOUNT_TYPE,
        self::RETAILER_ACCOUNT_TYPE           => self::RETAILER_ACCOUNT_TYPE,
        self::RETAIL_EMPLOYEE_ACCOUNT_TYPE    => self::RETAIL_EMPLOYEE_ACCOUNT_TYPE,
        self::CUSTOMER_ACCOUNT_TYPE           => self::CUSTOMER_ACCOUNT_TYPE,
        self::SUPPLIER_ACCOUNT_TYPE           => self::SUPPLIER_ACCOUNT_TYPE,
        self::LEAD_ACCOUNT_TYPE               => self::LEAD_ACCOUNT_TYPE,
        self::CONTRACTOR_ACCOUNT_TYPE         => self::CONTRACTOR_ACCOUNT_TYPE,
        self::EMPLOYEE_ACCOUNT_TYPE           => self::EMPLOYEE_ACCOUNT_TYPE,
    ];

    const LEVEL_1 = 1;
    const LEVEL_2 = 2;

    const USER_LEVEL = [
        self::LEVEL_1 => self::LEVEL_1,
        self::LEVEL_2 => self::LEVEL_2,
    ];

    const LOGINS = [
        'google'   => 'google',
        'facebook' => 'facebook',
        // 'twitter'  => 'twitter',
        // 'github'   => 'github',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "username",
        "email",
        "password",
        "phone_number",
        "terms",
        "is_admin",
        "is_retailer",
        "is_retail_employee",
        "is_supplier",
        "is_suspended",
        "is_active",
        "login_token",
        'api_token',
        'role',
        'user_level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'login_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    protected $appends = ['profile_url'];

    public function profile()
    {
        return $this->morphOne(Profile::class, 'profileable');
    }

    public function getProfileUrlAttribute()
    {
        return $this->profile ? $this->profile->profile_image : null;
    }

    public function userProfile()
    {
        # code...
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function retails()
    {
        return $this->morphMany(Retail::class, 'ownerable');
    }

    public function retailOwner()
    {
        return $this->hasOne(RetailOwner::class, 'user_id');
    }

    public function retail()
    {
        return $this->hasMany(Retail::class, 'user_id');
    }

    public function userRetails()
    {
        return $this->hasMany(Retail::class, 'user_id');
    }

    public function offices()
    {
        return $this->morphMany(Office::class, 'ownerable');
    }

    public function sessionAccount()
    {
        return $this->morphOne(SessionAccount::class, "ownerable");
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, "user_id");
    }

    public function supplyItems()
    {
        return $this->morphMany(Supply::class, 'supply_itemable');
    }

    // public function retailSalary()
    // {
    //     return $this->hasMany(RetailSalary::class, 'paid_by');
    // }

    //notifications
    public function receivesBroadcastNotificationsOn()
    {
        return 'users.' . $this->id;
    }

    // public function verification()
    // {
    //     # code...
    //     return $this->morphOne(Verification::class, "verifyable");
    // }

    public function supplier()
    {
        # code...
        return $this->morphOne(Supplier::class, "supplierable");
    }

    public function ecommerce()
    {

        return $this->hasOne(Ecommerce::class, 'user_id');

    }

    public function ecommerces()
    {

        return $this->morphMany(Ecommerce::class, 'ownerable');

    }

    public function receipt()
    {
        return $this->hasMany(Receipt::class, 'user_id');
    }

    public function terminals()
    {
        return $this->belongsToMany(Terminal::class, 'terminal_user', 'user_id', 'terminal_id');
    }

    public function activeTerminal()
    {
        return $this->terminals()->where('is_active', true);
    }

    public function tiers()
    {
        return $this->morphMany(Tier::class, 'tierable');
    }

    public function createdTodos()
    {
        return $this->hasMany(Todo::class, 'user_id');
    }

    public function assigneedTodos()
    {
        return $this->hasMany(Todo::class, 'assigned_to');
    }

    public function uploadedMedia()
    {
        return $this->hasMany(Medium::class, 'user_id');
    }

    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'user_id');
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

    public function setting()
    {
        return $this->hasOne(RetailSetting::class, 'ownerable');
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

    public function assignedTasks()
    {
        return $this->belongsToMany(Task::class, "task_assignees", "assignee_id", "task_id");
    }

    public function assignedCampaignTasks()
    {
        return $this->assignedTasks()->whereHas('campaign');
    }

    public function reviewTasks()
    {
        return $this->belongsToMany(Task::class, "task_assignees", "reviewer_id", "task_id");
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

    public function userLeads()
    {
        return $this->hasMany(Lead::class, 'user_id');
    }

    public function activities()
    {
        return $this->morphMany(UserActivity::class, 'ownerable');

    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'ownerable');

    }

}
