<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\ApiResource;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Repositories\EmployeesRepository;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Employees",
 *     description="Manage employees and their roles"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class EmployeeController extends BaseController
{

    protected $user;
    protected $retail;
    protected $employeesRepo;

    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    public function employeeRepository()
    {
        # code...

        $this->retail = $this->getAccount();

        $this->employeesRepo = new EmployeesRepository($this->retail);
        return $this->employeesRepo;
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/employees",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of employees",
     *     description="Fetch all employees for the authenticated retailer",
     *     @OA\Response(
     *         response=200,
     *         description="List of employees retrieved successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        //
        $employee_list = $this->employeeRepository()->getEmployees();
        if (! $employee_list) {
            $this->sendError($employee_list, 'error, could not fetch employees');
        }

        return $this->sendResponse($employee_list, 'Success, list of employees');

    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/employees/create",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get data for creating employee",
     *     description="Fetch roles and related data required for creating a new employee",
     *     @OA\Response(response=200, description="Data fetched successfully")
     * )
     */
    public function create()
    {
        //
        $roles      = $this->getAccount()->roles()->get();
        $user_roles = User::ROLETYPES;
        $user_level = User::USER_LEVEL;
        $empdata    = [
            'checked_status' => false,
            'email'          => "",
            'employee_roles' => $roles,
            'user_roles'     => $user_roles,
            'user_levels'    => $user_level,
        ];
        return $this->sendResponse($empdata, 'Success, You can create an employee account');

    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *     path="/api/v1/employees/validate",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Validate employee account existence",
     *     description="Checks if employee email or phone number already exists",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="employee@example.com"),
     *             @OA\Property(property="phone_number", type="string", example="+254700000000"),
     *             @OA\Property(property="status", type="string", example="incomplete")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Email validated successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function validateAccountExistence(Request $request)
    {
        if ($request->email) {
            $request->validate(
                [
                    'email' => ['required', 'email', 'unique:users'],
                ]
            );
        }
        if ($request->phone_number) {
            $request->validate(
                [
                    "phone_number" => ['required', 'unique:users'],
                ]
            );
        }

        if (request()->email && request()->status == "incomplete") {
            $employeeWithAccount = $this->employeeWithAccount($request->email);
            if ($employeeWithAccount) {
                return $this->sendError('error', "The employee email owner is already registered as an employee");
            } else {
                // $roles   = $this->getRetailEmployeeRoles();
                $empdata = [
                    'email'          => $request->email,
                    'checked_status' => true,
                    // 'roles'          => $roles,
                ];
                return $this->sendResponse($empdata, "Email validated, you can continue with the registration");

            }
        }

    }

    public function registerUser(Request $request)
    {

        try {
            $result = $this->authService->apiRegister($request->all());

            if (isset($result['error'])) {
                return $this->apiResource->error(message: $result['message'], errors: $result['error'], code: $result['httpCode'] ?? 400);
            }

            return $this->apiResource->success($result['data'], 'Registration successful', 201);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/employees",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new employee",
     *     description="Registers a new employee and assigns them to a retail account",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firstname","lastname","email","phone_number","employee_role","user_role","user_level"},
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="lastname", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phone_number", type="string", example="+254700000000"),
     *             @OA\Property(property="employee_role", type="integer", example=1),
     *             @OA\Property(property="user_role", type="string", example="cashier"),
     *             @OA\Property(property="user_level", type="string", example="staff")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Employee created successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(StoreEmployeeRequest $request)
    {
        //
        $employee_to_register = User::where('email', $request->email)
            ->orwhere('phone_number', $request['phone_number'])
            ->orwhere('username', $request['firstname'] . " " . $request['lastname'])
            ->first();

        $employee = $employee_to_register;

        $password = "password";
        $data     = [
            'name'                  => $request['firstname'] . " " . $request['lastname'],
            'email'                 => $request['email'],
            'username'              => '@' . $request['lastname'],
            'password'              => $password,
            'phone_number'          => $request['phone_number'],
            'password_confirmation' => ($password),
            'is_retailer'           => false,
            'is_retail_employee'    => true,
            'is_supplier'           => false,
            'employee_role'         => $request['employee_role'],
            "role"                  => $request->user_role,
            "user_level"            => $request->user_level,

        ];

        if (! $employee_to_register) {
            $validator = Validator::make($data,
                [
                    'name'          => ['required', 'string', 'max:255', 'unique:users'],
                    'username'      => ['required', 'string', 'max:255', 'unique:users'],
                    'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'password'      => ['required', 'string', 'min:8', 'confirmed'],
                    'employee_role' => 'required',
                    'role'          => 'required',
                    'phone_number'  => ['required', 'min:8', 'max:255', 'unique:users'],
                ]);
            if ($validator->fails()) {
                return $this->sendError('Error', ['Error, Employee could not be created', $validator->errors()]);
            }
            $registered_user_account = $this->registerUser(new Request($data));
            $registered_user_account = json_decode($registered_user_account->getContent(), true);

            $employee = $registered_user_account['data']['user'];
            $employee = User::where('id', $employee['id'])->first();
        } else {
            $employee = $employee_to_register;
        }

        try {
            $user_id      = $employee->id;
            $employeedata = $request->only([
                'employee_national_id',
                'employee_role',
                'employee_salary',
            ]);

            $validator = Validator::make($employeedata, [
                // 'employee_national_id' => 'required',
                'employee_role' => 'required',
                // 'employee_salary'      => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error', ['Error, Employee could not be created', $validator->errors()]);
            }

            $employee = $this->account()->employees()->updateOrCreate(
                ["user_id" => $user_id],
                ['employee_salary'     => $employeedata['employee_salary'] ?? null,
                    'employee_national_id' => $employeedata['employee_national_id'] ?? null,
                ]
            );

            $role = $this->account()->roles()->where('id', $employeedata['employee_role'])->first();

            $role->employees()->attach($employee->id);

            if (! $employee) {
                return $this->sendError('Error', 'Error, Employee could not be created');
            }

            return $this->sendResponse($employee, 'Success, Employee data saved successfully');
        } catch (Exception $ex) {

            return $this->sendError($ex->getMessage(), 'Error, Error registering employee');
        }
    }

    /**
     * Display the specified resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/employees/{id}",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get employee details",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Employee details retrieved")
     * )
     */
    public function show($employee)
    {
        //
        $employee = Employee::where('id', $employee)
            ->with('user.userProfile')
            ->with("roles")
            ->first();
        return $this->sendResponse($employee, 'Success, Employee data');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        //
        $employee = Employee::where('id', $employee->id)
            ->with('user')
            ->with('roles')
            ->first();
        $employeeRoles             = $this->getAccount()->roles()->latest()->get();
        $employee['employeeRoles'] = $employeeRoles;
        return $this->sendResponse($employee, 'Success, Employee data');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/employees/{id}",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update employee details",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="jane.doe@example.com"),
     *             @OA\Property(property="phone", type="string", example="+123456789"),
     *             @OA\Property(property="position", type="string", example="Manager")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee updated successfully"
     *     )
     * )
     */

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        //
        $validated = $request->validated();
        $result    = $employee->update(
            $validated,
        );
        if (! $result) {
            return $this->sendError('Error', 'Error, Employee could not be updated');
        }

        return $this->sendResponse($employee, 'Success, Employee data updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */

    /**
     * @OA\Delete(
     *     path="/api/v1/employees/{id}",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete employee",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Employee deleted successfully")
     * )
     */
    public function destroy(Employee $employee)
    {
        //
        $result = Employee::destroy($employee->id);

        if (! $result) {
            return $this->sendError('Error', 'Error, Employee could not be deleted');
        }

        return $this->sendResponse($employee, 'Success, Employee data deleted successfully');

    }

    public function employeeWithAccount($email = null, $phone_number = null)
    {
        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        } elseif ($phone_number) {
            $user = User::where('phone_number', $phone_number)->first();
        } elseif ($email && $phone_number) {
            $user = User::where('email', $email)->orWhere('phone_number', $phone_number)->first();
        }

        if ($user) {
            return $user;
        } else {
            return false;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/employees/{employee_id}/edit/roles/assign",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Assign roles to employee",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"employee_id","roles"},
     *             @OA\Property(property="employee_id", type="integer", example=5),
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Role added successfully")
     * )
     */
    public function assignEmployeeRole(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'role_id'     => ['required'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $employee = Employee::where('id', $request->employee_id)->first();
        $result   = null;
        foreach ($request->roles as $role) {

            $result = $employee->roles()->syncWithoutDetaching($role['id']);
        }

        if ($result) {
            return $this->sendResponse($result, "Role added successfully");
        }

        return $this->sendError("Failed", "Unable to add role");

    }

    /**
     * @OA\Post(
     *     path="/api/v1/employees/{employee_id}/edit/roles/unassign/{role_id}",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     summary="Unassign a role from employee",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"employee_id","role_id"},
     *             @OA\Property(property="employee_id", type="integer", example=5),
     *             @OA\Property(property="role_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Role removed successfully")
     * )
     */
    public function unAssignEmployeeRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id'     => ['required', 'integer', 'exists:roles,id'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $employee = Employee::where('id', $request->employee_id)->first();
        $role     = Role::where('id', $request->role_id)->first();
        $result   = $employee->roles()->detach($role);

        if ($result) {
            return $this->sendResponse($result, "Role added successfully");
        }

        return $this->sendError("Failed", "Unable to add role");
    }
}
