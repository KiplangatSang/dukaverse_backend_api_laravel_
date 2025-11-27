<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;

class RoleController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/roles",
     *     summary="Get list of roles",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Roles fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error fetching roles"
     *     )
     * )
     */
    public function index()
    {
        //
        $roles = $this->getAccount()->roles()->with('employees')->get();

        if (! $roles) {
            return $this->sendError($roles, "Error, could not fetch any roles");
        }

        return $this->sendResponse($roles, "Success, roles fetched");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/create",
     *     summary="Get data for creating a role",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Role creation data fetched"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error fetching role creation data"
     *     )
     * )
     */
    public function create()
    {
        //
        $roles['permissions'] = $this->getRetailEmployeePermissions();
        if (! $roles) {
            return $this->sendError($roles, "Error, could not fetch any roles");
        }
        return $this->sendResponse($roles, "Success, roles fetched");
    }
    /**
     * @OA\Post(
     *     path="/api/v1/roles",
     *     summary="Create a new role",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "permissions"},
     *             @OA\Property(property="name", type="string", example="Admin"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="edit_users")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Admin"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="edit_users")),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error creating role"
     *     )
     * )
     */

    public function store(StoreRoleRequest $request)
    {
        //
        $validated = $request->validated();
        $role      = $this->getAccount()->roles()->create($validated);

        if (! $role) {
            return $this->sendError($role, "Error, could not create this role");
        }

        return $this->sendResponse($role, "Success, role created successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/{id}",
     *     summary="Get a specific role",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function show(Role $role)
    {
        //
        $role                   = $this->getAccount()->roles()->findOrFail($role->id)->with('employees.user')->first();
        $role['employees_list'] = $this->getAccount()->employees()->with('user')->with('roles')->get();
        if (! $role) {
            return $this->sendError($role, "Error, could not fetch this role");
        }

        return $this->sendResponse($role, "Success, role fetched successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/{id}/edit",
     *     summary="Get role data for editing",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role data fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function edit(Role $role)
    {
        //
        $role                         = $this->getAccount()->roles()->findOrFail($role->id);
        $role['employee_permissions'] = $this->getRetailEmployeePermissions();
        if (! $role) {
            return $this->sendError($role, "Error, could not fetch this role");
        }

        return $this->sendResponse($role, "Success, role fetched successfully");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/roles/{id}",
     *     summary="Update a role",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Admin"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="edit_users")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Admin"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="edit_users")),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error updating role"
     *     )
     * )
     */

    public function update(UpdateRoleRequest $request, Role $role)
    {
        //
        $validated = $request->validated();
        $role      = $role->update($validated);

        if (! $role) {
            return $this->sendError($role, "Error, could not create this role");
        }

        return $this->sendResponse($role, "Success, role created successfully");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/roles/{id}",
     *     summary="Delete a role",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error deleting role"
     *     )
     * )
     */
    public function destroy(Role $role)
    {
        //
        $role = $this->getAccount()->roles()->destroy($role->id);

        if (! $role) {
            return $this->sendError($role, "Error, role could not be deleted");
        }

        return $this->sendResponse($role, "Success, role deleted successfully");
    }
}
