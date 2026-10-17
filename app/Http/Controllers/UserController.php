<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\DeliveryApp\UserService;

class UserController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        private  UserService $userService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    private function loginTokenGenerator($token_length)
    {

        $token = null;

        $string1 = Str::random(round($token_length / 3));
        $string2 = rand();
        for ($i = 0; $i < round($token_length / 3); $i++) {
            $string2 = rand(10, strlen(round($token_length / 3)) - 1);
        }
        $string3 = Str::random(round($token_length / 3));
        $token   = $string1 .= $string2 .= $string3;
        return $token;
    }

    public function generateAPIToken()
    {
        $token = null;
        do {
            // Generate a token
            $token = Str::random(User::API_TOKEN_LENGTH);

            // Validate the token's uniqueness
            $validator = Validator::make(['api_token' => $token], [
                'api_token' => 'required|unique:users,api_token',
            ]);
        } while ($validator->fails());

        $user            = $this->user();
        $user->api_token = $token;
        $user->save();
        return $token;
    }

    public function generateLoginToken()
    {
        do {
            // Generate a token
            $token = $this->loginTokenGenerator(User::LOGIN_TOKEN_LENGTH);

            // Validate the token's uniqueness
            $validator = Validator::make(['login_token' => $token], [
                'login_token' => 'required|unique:users,login_token',
            ]);
        } while ($validator->fails());

        $user              = $this->user();
        $user->login_token = $token;
        $user->save();

        return response()->json(['message' => 'Token Generated Successfully', 'token' => $token], 200);
    }

    public function loginUsingToken(Request $request)
    {
        $token = $request->token;

        $validator = Validator::make(['login_token' => $token], [
            'login_token' => 'required|exists:users,login_token',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $user = User::where('login_token', $token)->first();
        if (! $user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $user = Auth::loginUsingId($user->id);

        return response()->json(['message' => 'Login successful', 'user' => $user], 200);
    }



    /**
     * @OA\Put(
     *     path="/api/v1/user/profile",
     *     summary="Update user profile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->apiResource->error("Validation Error", 422, $validator->errors());
            }

            $result = $this->userService->updateProfile($request->user(), $request->all());

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (\Exception $e) {
            return $this->apiResource->error("An error occurred", 500);
        }
    }

}
