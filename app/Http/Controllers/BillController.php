<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Models\Bill;
use App\Services\ThirdPartyService;
use Exception;

class BillController extends BaseController
{

    public function __construct(
        protected ApiResource $apiResource,
        protected readonly ThirdPartyService $thirdPartyService,
    ) {
        parent::__construct($apiResource);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            // $billslist = Bills::all();

            $thirdPartyImages = $this->thirdPartyService->getThirdPartyImages();
            // $bill = Bills::where('id',$bill_id)->first();
            $billPaymentData = [
                'thirdPartyImages' => $thirdPartyImages,
                // 'bill'  => $bill,
            ];

            //dd($billslist);
            // $billsdata = array(
            //     'billslist' => $billslist,
            // );

            return $billPaymentData;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        //
        return $bill;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        //

        try {
            $retail    = $this->getAccount();
            $billslist = $retail->bills()->where('id', $bill->id)->first();

            $billsdata = [
                'customer' => $billslist,
            ];

            return $billsdata;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        //
        try {
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        try {
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }
}
