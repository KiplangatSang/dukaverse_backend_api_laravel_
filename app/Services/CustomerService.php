<?php
namespace App\Services;

use App\Models\Customer;
use App\Services\CustomersService;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class CustomerService extends BaseService
{


    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
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

        $creditedItems = $this->getCreditedItems();

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

    public function getDisctictCustomers()
    {
        $customers = $this->account->customers()->distinct('email')->get();

        return $customers;
    }
    public function recentCustomers()
    {
        $customers = $this->account->customers()->latest()->get();
        return $customers;
    }
    public function getAllCustomers()
    {
        $customers = $this->account->customers()->get();
        return $customers;
    }

    //get sale by item id
    public function getCustomerById($id)
    {
        $customer = $this->account
            ->customers()
            ->where('id', $id)
            ->with('saletransactions')
            ->with('credits')
            ->first();

        return $customer;
    }

    //get employee sales
    public function getCustomersCredit($id)
    {
        $credits = $this->account->customerCredits()
            ->where('id', $id)
            ->with('customer')
            ->with('saleTransaction.sales.items')
            ->first();

        return $credits;
    }

    //get employee sales
    public function getCustomersByDate($startDate, $endDate)
    {
        $order = $this->account->customers()->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"])->get();
        return $order;
    }

    public function getCustomersCost()
    {
        $creditPrice = $this->account->customers()->sum('creditPrice');
        # code...
        return $creditPrice;
    }

    public function getCustomers($month = null, $year = null)
    {

        if (! $year) {
            $year = date("Y");
        }
        $customers = null;
        if ($month) {
            $customers = $this->account->customers()
                ->whereMonth("created_at", $month)
                ->whereYear('created_at', '=', $year)
                ->get();
        } else {
            $customers = $this->account->customers()
            // ->whereYear('created_at', '=', $year)
                ->get();
        }

        //dd($sales);
        return $customers;
    }

    public function getCustomersByKeys($key, $value)
    {

        $customers = null;
        $customers = $this->account->customers()->where($key, $value)->orderBy('created_at', 'DESC')->get();
        return $customers;
    }

    public function getCreditedItems()
    {
        $creditedItems = $this->account->sales()->where('on_credit', true)->get();

        return $creditedItems;
    }

    public function getCustomerGrowth($month = null)
    {

        if (! $month) {
            $month = date('m');
        }

        $currentCustomers  = count($this->getCustomers($month));
        $previousCustomers = count($this->getCustomers($month - 1));

        //dd("$currentRevenue");

        if ($currentCustomers <= 0) {
            $previousCustomers = 1;
            $currentCustomers  = 1;
        }

        $growth = (($currentCustomers - $previousCustomers) / $currentCustomers) * 100;
        $growth = number_format($growth, 2);

        return $growth;
    }

}
