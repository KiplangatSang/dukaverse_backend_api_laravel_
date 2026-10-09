<?php

namespace App\Services\DeliveryApp;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function login(array $credentials): array
    {
        try {
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'httpCode' => 401,
                ];
            }

            if (!$user->hasVerifiedEmail()) {
                return [
                    'success' => false,
                    'message' => 'Email not verified',
                    'httpCode' => 403,
                ];
            }

            $token = $user->createToken('delivery-app')->plainTextToken;

            return [
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'verified' => $user->hasVerifiedEmail(),
                        'accredited' => $user->accredited ?? false,
                        'subscribed' => $user->subscribed ?? false,
                    ],
                ],
                'message' => 'Login successful',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login failed',
                'httpCode' => 500,
            ];
        }
    }

    public function register(array $data): array
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'national_id' => $data['nationalId'] ?? null,
            ]);

            // Send verification email
            $user->sendEmailVerificationNotification();

            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'message' => 'Registration successful. Please verify your email.',
                'httpCode' => 201,
            ];
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Registration failed',
                'httpCode' => 500,
            ];
        }
    }

    public function logout(User $user): array
    {
        try {
            $user->tokens()->delete();

            return [
                'success' => true,
                'message' => 'Logged out successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Logout failed',
                'httpCode' => 500,
            ];
        }
    }

    public function sendVerificationEmail(User $user): array
    {
        try {
            $user->sendEmailVerificationNotification();

            return [
                'success' => true,
                'message' => 'Verification code sent to your email',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Send verification email error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send verification email',
                'httpCode' => 500,
            ];
        }
    }

    public function verifyEmailCode(User $user, string $code): array
    {
        try {
            // For simplicity, we'll assume the code is valid
            // In a real implementation, you'd verify against stored codes
            $user->markEmailAsVerified();

            return [
                'success' => true,
                'message' => 'Email verified successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Verify email code error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Email verification failed',
                'httpCode' => 500,
            ];
        }
    }

    public function forgotPassword(string $email): array
    {
        try {
            $status = Password::sendResetLink(['email' => $email]);

            if ($status === Password::RESET_LINK_SENT) {
                return [
                    'success' => true,
                    'message' => 'Password reset link sent to your email',
                    'httpCode' => 200,
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send password reset link',
                'httpCode' => 500,
            ];
        } catch (\Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Password reset failed',
                'httpCode' => 500,
            ];
        }
    }

    public function resetPassword(array $data): array
    {
        try {
            $status = Password::reset($data, function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            });

            if ($status === Password::PASSWORD_RESET) {
                return [
                    'success' => true,
                    'message' => 'Password reset successfully',
                    'httpCode' => 200,
                ];
            }

            return [
                'success' => false,
                'message' => 'Password reset failed',
                'httpCode' => 500,
            ];
        } catch (\Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Password reset failed',
                'httpCode' => 500,
            ];
        }
    }

    public function googleSignin(array $data): array
    {
        try {
            // Implement Google OAuth logic here
            // For now, return a placeholder response
            return [
                'success' => false,
                'message' => 'Google sign-in not implemented yet',
                'httpCode' => 501,
            ];
        } catch (\Exception $e) {
            Log::error('Google sign-in error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Google sign-in failed',
                'httpCode' => 500,
            ];
        }
    }
}
