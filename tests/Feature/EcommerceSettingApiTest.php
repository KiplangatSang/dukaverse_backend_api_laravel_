<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceSettingApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $token = $this->user->createToken('auth_token')->plainTextToken;
        $this->headers = ['Authorization' => "Bearer $token"];
    }

    /** @test */
    public function it_saves_ecommerce_settings()
    {
        $data = [
            'allow_discounts' => true,
            'allow_payments' => true,
            'is_age_restricted' => false,
            'connect_all_retails' => false,
            'show_all_products' => true,
            'show_support_contact' => true,
            'remove_products_in_low_stock' => false,
        ];

        $response = $this->postJson('/api/v1/ecommerce/settings', $data, $this->headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Settings upated successfully']);
    }

    /** @test */
    public function it_validates_ecommerce_settings_request()
    {
        // All required fields missing
        $data = [];

        $response = $this->postJson('/api/v1/ecommerce/settings', $data, $this->headers);

        $response->assertStatus(400); // validation errors
        $response->assertJsonStructure(['errors']);
    }
}
