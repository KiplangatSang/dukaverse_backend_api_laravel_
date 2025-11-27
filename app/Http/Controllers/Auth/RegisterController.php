<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;

class RegisterController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Register a new user",
     *     description="Registers a new user with the provided credentials and role details.",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "username", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="username", type="string", example="johndoe123"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone_number", type="string", example="1234567890"),
     *             @OA\Property(property="role", type="string", example="retailer"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration successful"),
     *             @OA\Property(property="data", type="object", example={
     *                 "id": 1,
     *                 "name": "John Doe",
     *                 "email": "johndoe@example.com"
     *             })
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation or business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The email has already been taken."),
     *             @OA\Property(property="errors", type="object", example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="errors", type="object", example=null)
     *         )
     *     )
     * )
     */
    public function register(Request $request)
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
     * @OA\Get(
     *     path="/api/v1/register/roles/{type}",
     *     summary="Fetch registration roles",
     *     description="Fetches available roles and levels for user registration.",
     *     operationId="fetchRegisterRoles",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Roles fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(property="data", type="object", example={
     *                 "roles": {"retailer": "Retailer", "wholesaler": "Wholesaler"},
     *                 "levels": {"basic": "Basic", "premium": "Premium"},
     *                 "role": "retailer",
     *                 "level": "basic"
     *             })
     *         )
     *     )
     * )
     */
    public function fetchRegisterRoles($type = null)
    {

        try {
            $result = $this->authService->registerRoles($type);

            if (isset($result['error'])) {
                return $this->apiResource->error(message: $result['message'], errors: $result['error'], code: $result['httpCode'] ?? 400);
            }

            return $this->apiResource->success($result['data'], 'Registration successful', 201);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

}
