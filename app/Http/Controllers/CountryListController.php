<?php

namespace App\Http\Controllers;

use App\Models\CountryList;
use App\Http\Requests\StoreCountryListRequest;
use App\Http\Requests\UpdateCountryListRequest;

class CountryListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreCountryListRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CountryList $countryList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CountryList $countryList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryListRequest $request, CountryList $countryList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CountryList $countryList)
    {
        //
    }
}
