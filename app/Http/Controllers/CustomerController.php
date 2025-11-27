<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\ApiResource;
use App\Models\Customer;
use App\Repositories\CustomersRepository;
use App\Services\AuthService;
use App\Services\CustomerService;
use Exception;

class CustomerController extends BaseController
{
    protected CustomerService $customerService;

    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
        $this->customerService = new CustomerService(new CustomersRepository($this->getAccount()), $this->getAccount());
    }

    /**
     * Get all customers for the current retail store
     * List of customers with their credit information
     */
    public function index()
    {
        try {
            $customerdata = $this->customerService->getIndexData();
            return $this->sendResponse($customerdata, "success,customers data");
        } catch (Exception $e) {
            return $this->sendError('Error retrieving customers', $e->getMessage());
        }
    }

    /**
     * Create a new customer
     * Adds a new customer to the current retail store
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            $result = $this->customerService->createCustomer($request);
            return $this->sendResponse($result, "Success, customer added successfully", 201);
        } catch (Exception $e) {
            return $this->sendError('Error creating customer', $e->getMessage());
        }
    }

    /**
     * Get a single customer's details
     */
    public function show(Customer $customer)
    {
        try {
            $customer = $this->customerService->getShowData($customer->id);
            return $this->sendResponse($customer, "Success, Customer's data");
        } catch (Exception $e) {
            return $this->sendError('Error retrieving customer', $e->getMessage());
        }
    }

    /**
     * Update an existing customer
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        try {
            $result = $this->customerService->updateCustomer($customer, $request);
            return $this->sendResponse($result, "Success, customer updated successfully");
        } catch (Exception $e) {
            return $this->sendError('Error updating customer', $e->getMessage());
        }
    }

    /**
     * Delete a customer
     */
    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->deleteCustomer($customer);
            return redirect('/customers/index')->with('success', 'Customer removed successfully');
        } catch (Exception $e) {
            return redirect('/customers/index')->with('error', 'Could not delete customer: ' . $e->getMessage());
        }
    }
}
