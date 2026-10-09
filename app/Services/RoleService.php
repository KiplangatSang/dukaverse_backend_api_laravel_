<?php

namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Retail;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleService extends BaseService
{
    protected $month, $year, $revenue = null, $lastRevenue = null, $revenue_id = null, $result = null;

    public function __construct(
        private readonly ProfitService $profitService,
        protected readonly StoreFileResource $storeFileResource,
        protected readonly ResponseHelper $responseHelper
    ) {
        $this->month = date('M');
        $this->year  = date("Y");
        parent::__construct($storeFileResource, $responseHelper);
    }


    public function index()
    {
        //
        $roles = $this->account->roles()->with('employees')->get();

        if (! $roles) {
            return $this->responseHelper->error('An error occurred', $roles, "Error, could not fetch any roles");
        }

        return $this->responseHelper->respond($roles, "Success, roles fetched");
    }


    public function create()
    {
        //
        $roles['permissions'] = Retail::employeePermissions();
        if (! $roles) {
            return $this->responseHelper->error('An error occurred', $roles, "Error, could not fetch any roles");
        }
        return $this->responseHelper->respond($roles, "Success, roles fetched");
    }


    public function store(array $data)
    {
        //

        $validator  = Validator::make(
            $data,
            [
                "role" => ["required", "string"],
                "permissions" => [
                    "required","array",
                    Rule::in(array_keys(Retail::employeePermissions()))
                ],
            ]
        );

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', $validator->errors());
        }

        $validated = $validator->validated();
        $role      = $this->account->roles()->create($validated);

        if (! $role) {
            return $this->responseHelper->error('An error occurred', "Error, could not create this role");
        }

        return $this->responseHelper->respond($role, "Success, role created successfully");
    }


    public function show(Role $role)
    {
        //
        $role                   = $this->account->roles()->findOrFail($role->id)->with('employees.user')->first();
        if (!$role) {
            return $this->responseHelper->error($message = "An error occurred", $error = 'Role not found.', $httpCode = 404);
        }

        $employees_list = $this->account->employees()->with('user')->with('roles')->get();
        if (! $employees_list) {
            return $this->responseHelper->error('An error occurred',  "Error, could not fetch the employees list");
        }

        $role['employees_list'] = $employees_list;

        return $this->responseHelper->respond($role, "Success, role fetched successfully");
    }


    public function edit(Role $role)
    {
        //
        $role                         = $this->account->roles()->findOrFail($role->id);

        if (! $role) {
            return $this->responseHelper->error('An error occurred', "Error, could not fetch this role");
        }

        $role['employee_permissions'] = Retail::getRetailEmployeePermissions();

        return $this->responseHelper->respond($role, "Success, role fetched successfully");
    }

    public function update(array $data, Role $role)
    {
        //

        $validator = Validator::make($data, []);
        if ($validator->fails()) {
            return $this->responseHelper->error("Bad request", ['errors' => $validator->errors()]);
        }
        $validated = $validator->validated();
        $role      = $role->update($validated);

        if (! $role) {
            return $this->responseHelper->error('An error occurred', "Error, could not create this role", 500);
        }

        return $this->responseHelper->respond($role, "Success, role created successfully");
    }

    public function destroy(Role $role)
    {
        //
        $role = $this->account->roles()->delete($role->id);

        if (! $role) {
            return $this->responseHelper->error("Error, role could not be deleted", [$role]);
        }

        return $this->responseHelper->respond($role, "Success, role deleted successfully");
    }
}
