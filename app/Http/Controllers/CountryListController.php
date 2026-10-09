<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Models\CountryList;
use App\Http\Requests\StoreCountryListRequest;
use App\Http\Requests\UpdateCountryListRequest;
use Exception;

class CountryListController extends Controller
{

    public function __construct(
        protected ApiResource $apiResource,
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

        try {
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryListRequest $request)
    {
        //

        try {
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CountryList $countryList)
    {
        //

        try {
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CountryList $countryList)
    {
        //

        try {
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryListRequest $request, CountryList $countryList)
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
    public function destroy(CountryList $countryList)
    {
        //

        try {
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
