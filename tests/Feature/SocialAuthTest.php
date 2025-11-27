<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Socialite for all tests
        $this->mockSocialite();
    }

    protected function mockSocialite()
    {
        $socialiteUser = $this->createMock(SocialiteUser::class);
        $socialiteUser->method('getId')->willReturn('123456789');
        $socialiteUser->method('getName')->willReturn('Test User');
        $socialiteUser->method('getEmail')->willReturn('test@example.com');
        $socialiteUser->method('getAvatar')->willReturn('https://example.com/avatar.jpg');
        $socialiteUser->method('getNickname')->willReturn('testuser');
        $socialiteUser->user = ['id' => '123456789', 'name' => 'Test User'];

        $mockDriver = Mockery::mock();
        $mockDriver->shouldReceive('stateless')->andReturnSelf();
        $mockDriver->shouldReceive('redirect')->andReturn(redirect('https://google.com/oauth'));
        $mockDriver->shouldReceive('getTargetUrl')->andReturn('https://google.com/oauth');
        $mockDriver->shouldReceive('user')->andReturn($socialiteUser);
        $mockDriver->shouldReceive('userFromToken')->andReturn($socialiteUser);

        // Mock the Socialite facade properly
        Socialite::shouldReceive('driver')->andReturn($mockDriver);
    }

    public function test_redirect_to_provider()
    {
        $response = $this->get('/api/v1/auth/google');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'authorization_url',
                    'provider'
                ]);
    }

    public function test_redirect_to_invalid_provider()
    {
        $response = $this->get('/api/v1/auth/invalid');

        $response->assertStatus(400)
                ->assertJson(['error' => 'Invalid provider']);
    }

    public function test_handle_provider_callback_creates_new_user()
    {
        $response = $this->get('/api/v1/auth/google/callback');

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'username' => 'testuser',
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);
    }

    public function test_handle_provider_callback_links_existing_user()
    {
        // Create existing user with same email
        User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Existing User',
        ]);

        $response = $this->get('/api/v1/auth/google/callback');

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);
    }

    public function test_link_social_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/v1/auth/google/link', [
                            'code' => 'test_code'
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Google account linked successfully',
                    'linked_accounts' => [
                        [
                            'provider' => 'google',
                            'provider_id' => '123456789',
                            'avatar' => 'https://example.com/avatar.jpg',
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);
    }

    public function test_link_already_linked_account()
    {
        $user = User::factory()->create([
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/v1/auth/google/link', [
                            'code' => 'test_code'
                        ]);

        $response->assertStatus(400)
                ->assertJson(['error' => 'This social account is already linked to your account.']);
    }

    public function test_unlink_social_account()
    {
        $user = User::factory()->create([
            'provider' => 'google',
            'provider_id' => '123456789',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
                        ->deleteJson('/api/v1/auth/google/unlink');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Google account unlinked successfully',
                    'linked_accounts' => []
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'provider' => null,
            'provider_id' => null,
        ]);
    }

    public function test_unlink_without_password()
    {
        $user = User::factory()->create([
            'provider' => 'google',
            'provider_id' => '123456789',
            'password' => Hash::make('password123'), // Set a password
        ]);

        $response = $this->actingAs($user, 'sanctum')
                        ->deleteJson('/api/v1/auth/google/unlink');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Google account unlinked successfully',
                    'linked_accounts' => []
                ]);
    }

    public function test_get_linked_accounts()
    {
        $user = User::factory()->create([
            'provider' => 'google',
            'provider_id' => '123456789',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/api/v1/auth/linked-accounts');

        $response->assertStatus(200)
                ->assertJson([
                    'linked_accounts' => [
                        [
                            'provider' => 'google',
                            'provider_id' => '123456789',
                            'avatar' => 'https://example.com/avatar.jpg',
                        ]
                    ]
                ]);
    }

    public function test_get_linked_accounts_no_links()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/api/v1/auth/linked-accounts');

        $response->assertStatus(200)
                ->assertJson([
                    'linked_accounts' => []
                ]);
    }

    public function test_unauthenticated_access_to_protected_routes()
    {
        $response = $this->postJson('/api/v1/auth/google/link', [
            'code' => 'test_code'
        ]);

        $response->assertStatus(401);

        $response = $this->deleteJson('/api/v1/auth/google/unlink');

        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/auth/linked-accounts');

        $response->assertStatus(401);
    }
}
