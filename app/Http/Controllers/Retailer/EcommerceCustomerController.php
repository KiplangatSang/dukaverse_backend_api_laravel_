<?php

namespace App\Http\Controllers\Retailer;

use App\Http\Resources\ApiResource;

use App\Http\Controllers\Controller;
use App\Services\EcommerceService;
use Illuminate\Http\Request;

class EcommerceCustomerController extends Controller
{

  public function __construct(
        protected ApiResource $apiResource,
        protected  EcommerceService $ecommerceService,
    ) {
        parent::__construct($apiResource);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        try {
            //
            $ecommerceServices = $this->ecommerceService->getCustomers();

            if(isset($ecommerceServices['error'])){
                return $this->apiResource->error('An error occurred',  400, ['errors' => $ecommerceServices['error']]);
            }
            return $this->apiResource->success(['ecommerceCustomers' => $ecommerceServices], 'Ecommerce Customers fetched successfully');

        } catch (\Exception $e) {
            return $this->apiResource->error('An error occurred', 500, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        try {
            //
        } catch (\Exception $e) {
            return $this->apiResource->error('An error occurred', 500, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        try {
            //
        } catch (\Exception $e) {
            return $this->apiResource->error('An error occurred', 500, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //

        try {
            //
        } catch (\Exception $e) {
            return $this->apiResource->error('An error occurred', 500, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        try {
            //
        } catch (\Exception $e) {
            return $this->apiResource->error('An error occurred', 500, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        try {
            //
        } catch (\Exception $e) {
            return $this->apiResource->error('An error occurred', 500, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        try {
            //
        } catch (\Exception $e) {
            return $this->apiResource->error('An error occurred', 500, ['errors' => $e->getMessage()]);
        }
    }
}
