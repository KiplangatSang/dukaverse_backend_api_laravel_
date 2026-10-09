<?php

namespace App\Http\Controllers;
use App\Http\Resources\ApiResource;

use App\Models\Location;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use Exception;

class LocationController extends Controller
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
        try{
            $locations = Location::all();
            return $locations;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try{
            $locations = Location::all();
            return $locations;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationRequest $request)
    {
        try{
            $locations = Location::all();
            return $locations;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        try{
            $locations = Location::all();
            return $locations;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        try{
            $locations = Location::all();
            return $locations;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        try{
            $locations = Location::all();
            return $locations;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        try{
            $locations = Location::all();
            return $locations;
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }
}
