<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplyRequest;
use App\Http\Requests\UpdateSupplyRequest;
use App\Models\Supply;

class SupplyController extends BaseController
{

    private $supplyrepo;
    private $retail;

    public function suppliesRepository()
    {
        # code...
        $this->retail     = $this->getAccount();
        $this->supplyrepo = new SuppliesRepository($this->retail);
        return $this->supplyrepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $suppliesdata["supplyitems"] = $this->suppliesRepository()->getAllSupplies();
        return $this->sendResponse($suppliesdata, "success");
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
    public function store(StoreSupplyRequest $request)
    {
        //
        $this->suppliesRepository();

        $this->getAccount()->supplies()->create(
            $request->validated(),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Supply $supply)
    {
        //
        $supply = $this->suppliesRepository()->getSuppliesById($supply->id);
        return $this->sendResponse($supply, 'Success, Supply item fetched');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supply $supply)
    {
        //
        $supply = $this->suppliesRepository()->getSuppliesById($supply->id);
        return $this->sendResponse($supply, 'Success, Supply item fetched');

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplyRequest $request, Supply $supply)
    {
        //
        $this->retail->supplies()->where('$supply->id', $supply->id)->update(
            $request->all(),
        );

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supply $supply)
    {
        //

        Supply::destroy($supply->id);
    }
}
