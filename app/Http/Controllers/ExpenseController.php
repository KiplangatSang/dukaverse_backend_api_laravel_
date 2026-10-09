<?php

namespace App\Http\Controllers;
use App\Http\Resources\ApiResource;

use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Exception;

class ExpenseController extends BaseController
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
            $expenses = Expense::all();
            return $this->sendResponse($expenses, 'success, expenses fetched successfully.');
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
            $expenses = Expense::all();
            return $this->sendResponse($expenses, 'success, expenses fetched successfully.');
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        try{
            $expenses = Expense::all();
            return $this->sendResponse($expenses, 'success, expenses fetched successfully.');
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        try{
            $expenses = Expense::all();
            return $this->sendResponse($expenses, 'success, expenses fetched successfully.');
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        try{
            $expenses = Expense::all();
            return $this->sendResponse($expenses, 'success, expenses fetched successfully.');
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        try{
            $expenses = Expense::all();
            return $this->sendResponse($expenses, 'success, expenses fetched successfully.');
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        try{
            $expenses = Expense::all();
            return $this->sendResponse($expenses, 'success, expenses fetched successfully.');
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
        //
    }
}
