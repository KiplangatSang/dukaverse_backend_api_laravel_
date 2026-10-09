<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanRequest;
use App\Models\Loan;
use Exception;

class LoanController extends Controller
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
            //
            $loans = Loan::all();
            return $loans;
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
        try {
            //

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLoanRequest $request)
    {
        try {
            //

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        try {
            //

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan)
    {
        try {
            //

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLoanRequest $request, Loan $loan)
    {
        try {
            //

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        try {
            //
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
