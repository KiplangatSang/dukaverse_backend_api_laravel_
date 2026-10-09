<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
        }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->authService->login($request->all());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);

   } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Register new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="nationalId", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful"
     *     )
     * )
     */
    public function register(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'nationalId' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->authService->register($request->all());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);

   } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try{
        $result = $this->authService->logout($request->user());

        return $this->apiResource->success(null, $result['message'], $result['httpCode']);

   } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/verify-email",
     *     summary="Send email verification code",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification code sent"
     *     )
     * )
     */
    public function verifyEmail(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $user = \App\Models\User::where('email', $request->email)->first();
        $result = $this->authService->sendVerificationEmail($user);

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success(null, $result['message'], $result['httpCode']);

   } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/verify-code",
     *     summary="Verify email with code",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","code"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="code", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully"
     *     )
     * )
     */
    public function verifyCode(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $user = \App\Models\User::where('email', $request->email)->first();
        $result = $this->authService->verifyEmailCode($user, $request->code);

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success(null, $result['message'], $result['httpCode']);

   } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot-password",
     *     summary="Request password reset",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent"
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->authService->forgotPassword($request->email);

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success(null, $result['message'], $result['httpCode']);

   } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/reset-password",
     *     summary="Reset password with token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","password"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string", minLength=8)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully"
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->authService->resetPassword($request->all());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success(null, $result['message'], $result['httpCode']);

   } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
        }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/google-signin",
     *     summary="Google OAuth sign-in",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"access_token"},
     *             @OA\Property(property="access_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Google sign-in successful"
     *     )
     * )
     */
    public function googleSignin(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->authService->googleSignin($request->all());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);

         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
