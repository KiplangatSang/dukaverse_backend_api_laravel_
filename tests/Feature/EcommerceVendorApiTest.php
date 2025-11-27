<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ecommerce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceVendorApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user and authenticate
        $this->user = User::factory()->create();
        $token = $this->user->createToken('auth_token')->plainTextToken;
        $this->headers = ['Authorization' => "Bearer $token"];
    }

    /** @test */
    public function it_lists_vendors()
    {
        Ecommerce::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/ecommerce/vendors', $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'user_id', 'created_at', 'updated_at']],
            ]);
    }

    /** @test */
    public function it_creates_a_vendor_when_user_has_permission()
    {
        // Assume user has 'ecommerce_access' permission granted in your setup for test

        $data = [
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'phone' => '1234567890',
            // Add other required fields for creation
        ];

        $response = $this->postJson('/api/v1/ecommerce/vendors', $data, $this->headers);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Vendor']);
    }

    /** @test */
    public function it_fails_to_create_vendor_without_permission()
    {
        // Simulate user without ecommerce_access permission
        // You may need to mock permissionsService or set user permissions accordingly

        $this->markTestIncomplete('Permission mocking to be implemented.');

        $data = [
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'phone' => '1234567890',
        ];

        $response = $this->postJson('/api/v1/ecommerce/vendors', $data, $this->headers);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_shows_vendor_details()
    {
        $vendor = Ecommerce::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/ecommerce/vendors/{$vendor->id}", $this->headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $vendor->id]);
    }

    /** @test */
    public function it_updates_a_vendor()
    {
        $vendor = Ecommerce::factory()->create(['user_id' => $this->user->id]);

        $data = ['name' => 'Updated Vendor Name'];

        $response = $this->putJson("/api/v1/ecommerce/vendors/{$vendor->id}", $data, $this->headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Vendor Name']);
    }

    /** @test */
    public function it_deletes_a_vendor()
    {
        $vendor = Ecommerce::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/ecommerce/vendors/{$vendor->id}", [], $this->headers);

        $response->assertStatus(200);
    }
}
