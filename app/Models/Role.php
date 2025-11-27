<?php
namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at'  => DateTimeCasting::class,
        'permissions' => JsonCasing::class,
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function roleable()
    {
        return $this->morphTo();
    }

    public function employees()
    {
        return $this->morphedByMany(Employee::class, 'employeeroleables');
    }

    const EMPLOYEE_PERMISSIONS = [
        "sales"           => "Sales",
        "stock"           => "Stock",
        "credit"          => "Credit",
        "payments"        => "Payments",
        "employees"       => "Employees",
        "reports"         => "Reports",
        "settings"        => "Settings",
        "users"           => "Users",
        "customers"       => "Customers",
        "products"        => "Products",
        "categories"      => "Categories",
        "brands"          => "Brands",
        "suppliers"       => "Suppliers",
        "warehouses"      => "Warehouses",
        "returns"         => "Returns",
        "discounts"       => "Discounts",
        "taxes"           => "Taxes",
        "accounting"      => "Accounting",
        "purchases"       => "Purchases",
        "expenses"        => "Expenses",
        "cash_register"   => "Cash Register",
        "giftcards"       => "Gift Cards",
        "messages"        => "Messages",
        "notifications"   => "Notifications",
        "calendar"        => "Calendar",
        "events"          => "Events",
        "tasks"           => "Tasks",
        'admin'           => "Admin",
        "orders"          => "orders",
        "supplies"        => "supplies",
        "ecommerce"       => "ecommerce",
        "tasks"           => "tasks",
        "projects"        => "projects",
        "create-projects" => "create-projects",
        "wallet"          => "wallet",
        "analytics"       => "analytics",
    ];

}
