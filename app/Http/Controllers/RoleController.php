<?php
namespace App\Http\Controllers;
use App\Http\Resources\ApiResource;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\AuthService;
use App\Services\RoleService;
use Exception;

class RoleController extends BaseController
{



    public function __construct(
        private readonly AuthService $authService,
         private readonly RoleService $roleService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
     }
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
        try{
            $result = $this->roleService->index();
            if(isset($result["error"])){
             return   $this->apiResource->error("Error occured", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        }catch(Exception $ex){
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500,[$ex->getMessage()]);
        }

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
        try{
            $result = $this->roleService->create();
            if(isset($result["error"])){
             return   $this->apiResource->error("Error occured", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        }catch(Exception $ex){
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500,[$ex->getMessage()]);
        }

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
        try{
            $result = $this->roleService->store($request->all());
            if(isset($result["error"])){
             return   $this->apiResource->error("Error occured", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"] , $result["message"], $result["httpCode"]);
        }catch(Exception $ex){
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500,[$ex->getMessage()]);
        }

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
        try{
            $result = $this->roleService->show($role);
            if(isset($result["error"])){
             return   $this->apiResource->error("Error occured", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        }catch(Exception $ex){
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500,[$ex->getMessage()]);
        }

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
        try{
            $result = $this->roleService->edit( $role);
            if(isset($result["error"])){
             return   $this->apiResource->error("Error occured", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        }catch(Exception $ex){
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500,[$ex->getMessage()]);
        }

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
        try{
            $result = $this->roleService->update( $request->all(), $role);
            if(isset($result["error"])){
             return   $this->apiResource->error("Error occured", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        }catch(Exception $ex){
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500,[$ex->getMessage()]);
        }

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
        try{
            $result = $this->roleService->destroy($role);
            if(isset($result["error"])){
             return   $this->apiResource->error("Error occured", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        }catch(Exception $ex){
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500,[$ex->getMessage()]);
        }

    }
}
