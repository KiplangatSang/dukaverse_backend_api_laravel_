<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Models\Bill;

class BillController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // $billslist = Bills::all();

        $thirdPartyRepo   = new ThirdPartyRepository();
        $thirdPartyImages = $thirdPartyRepo->getThirdPartyImages();
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
        $retail    = $this->getAccount();
        $billslist = $retail->bills()->where('id', $bill->id)->first();

        $billsdata = [
            'customer' => $billslist,
        ];

        return $billsdata;

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        //
    }
}
