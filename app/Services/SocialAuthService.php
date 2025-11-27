<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;
use Illuminate\Support\Str;

class SocialAuthService
{
    /**
     * Find or create user from social provider data
     */
    public function findOrCreateUser($socialUser, string $provider)
    {
        Log::info("Social auth attempt: {$provider} - {$socialUser->getId()}");

        // Check if user exists with this provider and provider_id
        $user = User::where('provider', $provider)
                   ->where('provider_id', $socialUser->getId())
                   ->first();

        if ($user) {
            // Update avatar if changed
            if ($socialUser->getAvatar() && $user->avatar !== $socialUser->getAvatar()) {
                $user->update(['avatar' => $socialUser->getAvatar()]);
            }
            Log::info("Existing user logged in via {$provider}: {$user->id}");
            return $user;
        }

        // Check if user exists with same email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link the social account to existing user
            Log::info("Linking social account {$provider} to existing user: {$user->id}");
            return $this->linkSocialAccount($user, $socialUser, $provider);
        }

        // Create new user
        $newUser = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname(),
            'email' => $socialUser->getEmail(),
            'username' => $this->generateUniqueUsername($socialUser->getNickname() ?? $socialUser->getName()),
            'password' => Hash::make(Str::random(16)), // Random password for social users
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'social_data' => $socialUser->user ?? [],
            'email_verified_at' => now(), // Social logins are pre-verified
            'is_active' => true,
        ]);

        Log::info("New user created via {$provider}: {$newUser->id}");
        return $newUser;
    }

    /**
     * Link social account to existing user
     */
    public function linkSocialAccount(User $user, $socialUser, string $provider)
    {
        try {
            // Check if this provider is already linked
            if ($user->provider === $provider && $user->provider_id === $socialUser->getId()) {
                throw new \Exception('This social account is already linked to your account.');
            }

            // Check if another user has this social account
            $existingUser = User::where('provider', $provider)
                               ->where('provider_id', $socialUser->getId())
                               ->where('id', '!=', $user->id)
                               ->first();

            if ($existingUser) {
                throw new \Exception('This social account is already linked to another user.');
            }

            // Check for email conflicts across providers
            $emailUser = User::where('email', $socialUser->getEmail())
                           ->where('id', '!=', $user->id)
                           ->first();

            if ($emailUser) {
                throw new \Exception('An account with this email address already exists. Please use a different social account or contact support.');
            }

            // Update user with social data
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                'social_data' => array_merge($user->social_data ?? [], $socialUser->user ?? []),
            ]);

            Log::info("Social account linked successfully: {$provider} for user {$user->id}");
            return $user;
        } catch (\Exception $e) {
            Log::error("Failed to link social account {$provider} for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Unlink social account from user
     */
    public function unlinkSocialAccount(User $user, string $provider)
    {
        try {
            if ($user->provider !== $provider) {
                throw new \Exception('This social account is not linked to your account.');
            }

            // Check if user has a password set (to prevent lockout)
            if (!$user->password || !password_get_info($user->password)['algo']) {
                throw new \Exception('Cannot unlink social account. Please set a password first to ensure account access.');
            }

            $user->update([
                'provider' => null,
                'provider_id' => null,
                'avatar' => null,
                'social_data' => null,
            ]);

            Log::info("Social account unlinked successfully: {$provider} for user {$user->id}");
            return $user;
        } catch (\Exception $e) {
            Log::error("Failed to unlink social account {$provider} for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get linked social accounts for user
     */
    public function getLinkedAccounts(User $user)
    {
        $linkedAccounts = [];

        if ($user->provider && $user->provider_id) {
            $linkedAccounts[] = [
                'provider' => $user->provider,
                'provider_id' => $user->provider_id,
                'avatar' => $user->avatar,
                'linked_at' => $user->updated_at, // Approximate link time
            ];
        }

        return $linkedAccounts;
    }

    /**
     * Generate unique username from social data
     */
    private function generateUniqueUsername(string $baseUsername)
    {
        $username = Str::slug($baseUsername);
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
