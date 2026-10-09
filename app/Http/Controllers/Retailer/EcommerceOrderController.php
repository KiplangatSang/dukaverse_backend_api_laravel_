<?php

namespace App\Http\Controllers\Retailer;

use App\Http\Resources\ApiResource;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class EcommerceOrderController extends Controller
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

     try {

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

     try {

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

     try {

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

     try {

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

     try {

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

     try {

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

     try {

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        //
    }
}
