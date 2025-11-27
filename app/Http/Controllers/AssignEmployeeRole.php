<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;

class AssignEmployeeRole extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/roles/{role}/employees/{employee}/assign",
     *     tags={"Roles"},
     *     summary="Assign a role to an employee",
     *     description="This endpoint assigns the specified role to an employee without detaching existing roles. It is idempotent and will not create duplicates.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         description="ID of the role to be assigned",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="employee",
     *         in="path",
     *         required=true,
     *         description="ID of the employee to whom the role is assigned",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee successfully assigned to role",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="integer", example=10)),
     *             @OA\Property(property="message", type="string", example="success, employee role updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Could not assign employee this role"
     *     )
     * )
     */
    public function assignEmployeeRole(Role $role, Employee $employee)
    {
        $result = $role->employees()->syncWithoutDetaching($employee);

        if (! $result) {
            return $this->sendError($result, 'Could not assign employee this role');
        }
        return $this->sendResponse($result, 'success, employee role updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/roles/{role}/employees/{employee}/unassign",
     *     tags={"Roles"},
     *     summary="Unassign a role from an employee",
     *     description="This endpoint detaches (removes) the specified role from an employee. If the employee does not have this role, no error is thrown.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         description="ID of the role to be unassigned",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="employee",
     *         in="path",
     *         required=true,
     *         description="ID of the employee from whom the role will be removed",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee successfully unassigned from role",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="success, employee role unassigned successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Could not unassign employee this role"
     *     )
     * )
     */
    public function unAssignEmployeeRole(Role $role, Employee $employee)
    {
        $result = $role->employees()->detach($employee);

        if (! $result) {
            return $this->sendError($result, 'Could not unassign employee this role');
        }
        return $this->sendResponse($result, 'success, employee role unassigned successfully');
    }
}
