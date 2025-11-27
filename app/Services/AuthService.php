<?php
namespace App\Services;

use App\Models\SessionAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class AuthService extends BaseService
{
    public function __construct(
        StoreFileResource $storeFileResource,
       private ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }
    public function logout(User $user): array
    {
        $user->tokens->each(function ($token) {
            $token->delete();
        });

        if ($user->sessionRetail) {
            SessionAccount::destroy($user->sessionRetail->id);
        }

        return $this->responseHelper->respond(data: ['message' => 'Logged out successfully.'], message: "Logout successful", httpCode: 200);
    }

    public function registerRoles($type = null, $level = null): array
    {

        $roles = User::USER_ROLETYPES;

        // $levels = User::USER_LEVEL;
        $role = null;
        if ($type) {
            $role = $roles[$type];
        }
        // if ($level) {
        //     $level = $levels[$level];
        // }

        $logins = User::LOGINS;

        return $this->responseHelper->respond(data: [
            "roles"  => $roles,
            // "levels" => $levels,
            "role"   => $role,
            // "level"  => $level,
            "logins" => $logins,
        ], httpCode: 200);

    }

    public function loginRequest(array $request): array
    {

        $validator = Validator::make($request, [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(error: ["errors" => $validator->errors()], message: "Bad Request", httpCode: 401);
        }

        // Attempt to authenticate the user
        if (Auth::attempt($validator->validated())) {
            // If the authentication is successful, generate a new token for the user
            $user = Auth::user();
            $user = User::where('id', $user->id)->with('profile')->first();

            // $userController = new UserController();
            // $api_token      = $userController->generateAPIToken();

            // $user->api_token = $api_token;
            $user->save();
            // 'token' => $token,

            // Return the user information and token as JSON
            return $this->responseHelper->respond(data: [
                'user' => $user,
            ], httpCode: 200);

        }

        return $this->responseHelper->error(message: "Invalid credentials", error:
            ['message' => 'Invalid credentials'], httpCode: 401);

        // If the authentication fails, return an error message
        // return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function saveUserToken($userId)
    {

        $user = User::find($userId);
        if (! $user) {
            return $this->responseHelper->error(message: "User Not Found", error:
                ['message' => 'User could not be found.'], httpCode: 404);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $user->save();
        return $this->responseHelper->respond(data: [
            'role'  => $user->role,
            'token' => $token,
        ], httpCode: 200);
    }

    protected function create($data)
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    private function formatPhoneNumber($phoneNumber)
    {
        // Format the phone number as needed, e.g., remove spaces, dashes, etc.
        return preg_replace('/\D/', '', $phoneNumber);
    }

    private function formatUsername($username)
    {
        // Format the username as needed, e.g., trim whitespace, enforce lowercase, etc.
        return "@" . strtolower(trim($username));
    }

    public function apiRegister(array $request): array
    {
        $validator = Validator::make($request, [
            'name'         => ['required', 'string', 'max:255'],
            'username'     => ['required', 'string', 'max:255', 'unique:users'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'phone_number' => ['required', 'min:8', 'max:255', 'unique:users'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(message: "Bad request: Missing values", error:
                $validator->errors(), httpCode: 422);
        }

        $request['password'] = Hash::make($request['password']);

        $result = $user = User::firstOrCreate(
            [
                'phone_number' => $this->formatPhoneNumber($request['phone_number']),
                'username'     => $this->formatUsername($request['username']),
                'email'        => $request['email'],
                "status"       => User::USER_ACCOUNT_STATUS['active'],
                'role'         => $request['role'],
            ],
            collect($request)->except(['username', 'email', 'phone_number'])->toArray(),
        );

        if (! $result) {
            return $this->responseHelper->error(error: ["error" => 'Could not create this account.'], message: "Bad request: Could not create user.", httpCode: 500);
        }

        $image  = $this->getNoProfileImage();
        $user   = User::where('id', $user->id)->first();
        $result = $user->profile()->create([
            "user_id"       => $user->id,
            "profile_image" => $image,
        ]);

        if (! $result) {

            return $this->responseHelper->error(message: "Bad request", error:
                ["error" => 'Could not create this account.'], httpCode: 500);

        }

        $user = User::where('id', $user->id)->with('profile')->first();
        if (! $user) {

            return $this->responseHelper->error(message: "Bad request", error:
                ["error" => 'Could not create this account.'], httpCode: 500);
        }

        //isset(["find_out_site", "referal_id"], $request)

        if (isset($request['referal_id'])) {
            $user->find_out_site = 'referal';
            $user->referal_id    = $request['referal'] ?? null;
        }
        $user->save();

        event(new Registered($user));

        return $this->responseHelper->respond(data:
            ["user" => collect([
                'id'       => $user->id,
                'username' => $user->username,
                'role'     => $user->role,
                "status"   => User::USER_ACCOUNT_STATUS['active'],
            ]

            )], message: 'User registered successfully.', httpCode: 201);

    }

    public function verify($request, $id, $hash): array
    {
        $user = User::findOrFail($id);

        // Check if the hash matches the SHA-1 of the user's email.
        // This assumes that your User model implements MustVerifyEmail, which provides getEmailForVerification().
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->responseHelper->error(error: ['message' => 'Invalid verification token.'], httpCode: 400);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->responseHelper->respond(data:
                ["user" => $user], message: 'User is already verified.', httpCode: 200);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return $this->responseHelper->respond(data:
            ["user" => $user], message: 'User has been verified successfully.', httpCode: 200);
    }

    public function resend($request): array
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->responseHelper->error('You have already verified your email.', ["user" => $user, "message" => 'You have already veirified your email.'], 501);
        }

        $user->sendEmailVerificationNotification();

        return $this->responseHelper->respond(["user" => $user, 'message' => 'Verification email has been sent!'], 'Verification email has been sent!', 200);

    }

}
