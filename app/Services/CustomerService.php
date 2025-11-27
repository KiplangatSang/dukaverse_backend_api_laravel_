<?php
namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomersRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class CustomerService extends BaseService
{
    protected CustomersRepository $customersRepository;


    public function __construct(
        CustomersRepository $customersRepository,
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
        $this->customersRepository = $customersRepository;
    }

    public function getIndexData()
    {
        $customerlist = $this->account->customers()->latest()->get();

        $creditAmount = $this->account
            ->customers()->with([
            'credits' => function ($query) {
                $query->select(DB::raw('SUM(amount) as creditAmount'))
                    ->groupBy('created_at');
            },
        ])->latest()->get();

        $creditedItems = $this->customersRepository->getCreditedItems();

        $customerdata = [
            'customerlist'      => $customerlist,
            'customerCredit'    => $this->account->customerCredits()->sum('amount'),
            'creditedCustomers' => $this->account->customers()->whereHas('creditTransactions')->get(),
            "creditedItems"     => $creditedItems->count(),
        ];

        return $customerdata;
    }

    public function createCustomer($request)
    {
        if ($request->email) {
            $request->validate([
                'email' => ["email", "unique:users"],
            ]);
        }

        $result = $this->account->customers()->create($request->all());

        return $result;
    }

    public function getShowData($customerId)
    {
        $customer = $this->account->customers()
            ->where('id', $customerId)
            ->with('saletransactions.sales')
            ->with('saletransactions.credits')
            ->first();

        return $customer;
    }

    public function updateCustomer($customer, $request)
    {
        $customer = $this->account->customers()->where("id", $customer->id)->first();

        $result = $customer->update($request->validated());

        if (!$result) {
            throw new Exception("Could not Update Customer");
        }

        $customer = $this->account->customers()->where("id", $customer->id)->first();
        return $customer;
    }

    public function deleteCustomer($customer)
    {
        Customer::destroy($customer->id);
        return true;
    }
}
