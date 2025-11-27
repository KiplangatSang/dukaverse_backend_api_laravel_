<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Models\Customer;
use App\Repositories\CustomersRepository;
use App\Repositories\EcommerceRepository;
use App\Repositories\OrdersRepository;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends BaseController
{
    /**
     * @var OrdersRepository
     * @var EcommerceRepository
     * @var CustomersRepository
     */
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * Get list of customers with credit information
     * Returns a list of customers, their credit transactions, credited customers and credited item count.
     */
    public function index(Request $request)
    {
        $customerlist = $this->ecommerce($request)->customers()->latest()->get();

        $creditAmount = $this->ecommerce($request)
            ->customers()->with([
            'credits' => function ($query) {
                $query->select(DB::raw('SUM(amount) as creditAmount'))->groupBy('created_at');
            },
        ])->latest()->get();

        $creditedItems = (new CustomersRepository($this->getAccount()))->getCreditedItems();

        $customerdata = [
            'customerlist'      => $customerlist,
            'customerCredit'    => $this->ecommerce($request)->customerCredits()->sum('amount'),
            'creditedCustomers' => $this->ecommerce($request)->customers()->whereHas('creditTransactions')->get(),
            "creditedItems"     => $creditedItems->count(),
        ];

        return $this->sendResponse($customerdata, "success,customers data");
    }

    /**
     * Create a new customer
     * Stores a new customer in the database
     */
    public function store(Request $request)
    {
        try {
            if ($request->email) {
                $request->validate([
                    'email' => ["email", "unique:users"],
                ]);
            }
            $result = $this->ecommerce($request)->customers()->create($request->all());
            return $this->sendResponse($result, "Success, customer added successfully");
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Get a single customer's details
     * Returns customer data including sales and credit transactions
     */
    public function show(Request $request, $customer)
    {
        $customer = $this->ecommerce($request)->customers()
            ->where('id', $customer)
            ->with('saletransactions.sales')
            ->with('saletransactions.credits')
            ->first();

        return $this->sendResponse($customer, "Success, Customer's data");
    }

    /**
     * Update customer details
     * Updates a customer's information
     */
    public function update(Request $request, $customer)
    {
        $customer = $this->ecommerce($request)->customers()->where("id", $customer)->first();

        try {
            $result = $customer->update($request->validated());

            if (! $result) {
                return $this->sendError('Error', "Could not Update Customer");
            }

            $customer = $this->ecommerce($request)->customers()->where("id", $customer->id)->first();
            return $this->sendResponse($customer, "Success, customer updated successfully");
        } catch (Exception $ex) {
            return $this->sendError('Error', "Could not Update Customer");
        }
    }

    /**
     * Delete a customer
     * Removes a customer from the database
     */
    public function destroy($customer)
    {
        Customer::destroy($customer);
        return redirect('/customers/index')->with('success', 'Customer removed successfully');
    }
}
