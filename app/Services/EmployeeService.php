<?php

namespace App\Services;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class EmployeeService extends BaseService
{
 
    protected $currentAccount;


    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
     }

    public function getEmployees()
    {
        $employees = $this->account->employees()
            ->orderBy('created_at', 'desc')
            ->with("user")
            ->with("roles")
            ->get();
        return $employees;
    }

    //get sale by item id
    public function getEmployeeById($id)
    {
        $employee = $this->account->employees()->where('id', $id)->first();

        return $employee;
    }

    //get employee sales
    public function getEmployeeSales($empid)
    {
        $sales = $this->account->sales()->where('employees_id', $empid)->get();

        return $sales;
    }




    public function getSaleItem($key, $value)
    {
        $sales =  $this->account->employees()->where($key, $value)->orderBy('created_at', 'DESC')->get();
        //dd($sales);
        return $sales;
    }
}
