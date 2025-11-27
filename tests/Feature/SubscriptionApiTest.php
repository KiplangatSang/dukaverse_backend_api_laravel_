<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tier;
use App\Models\Coupon;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $testUser;
    protected $testTier;
    protected $testCoupon;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->testUser = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create test tier
        $this->testTier = Tier::create([
            'name' => 'Test Tier',
            'description' => 'Test tier for API testing',
            'price' => 9.99,
            'billing_duration' => 'month',
            'trial_period_days' => 7,
            'is_active' => true,
            'ownerable_type' => 'App\Models\User',
            'ownerable_id' => $this->testUser->id,
        ]);

        // Create test subscription for polymorphic relation
        Subscription::factory()->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'ownerable_type' => 'App\Models\User',
            'ownerable_id' => $this->testUser->id,
            'transaction_id' => null,
        ]);

        // Create test coupon
        $this->testCoupon = Coupon::create([
            'code' => 'TEST10',
            'name' => 'Test 10% Off',
            'description' => '10% discount for testing',
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'minimum_amount' => 5.00,
            'usage_limit' => 100,
            'is_active' => true,
            'applicable_tiers' => [$this->testTier->id],
            'ownerable_type' => 'App\Models\User',
            'ownerable_id' => $this->testUser->id,
        ]);

        // Authenticate the test user
        $this->actingAs($this->testUser, 'sanctum');
    }

    /** @test */
    public function it_can_list_all_subscriptions()
    {
        Subscription::factory()->count(3)->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
        ]);

        $response = $this->getJson('/api/subscriptions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'subscriptions' => [
                            '*' => [
                                'id',
                                'user_id',
                                'tier_id',
                                'status',
                                'is_active',
                                'subscription_price',
                                'discounted_price',
                                'auto_renewal',
                                'expires_at',
                                'tier' => [
                                    'id',
                                    'name',
                                    'price',
                                    'billing_interval',
                                ],
                            ],
                        ],
                    ],
                ]);
    }

    /** @test */
    public function it_can_get_subscription_creation_metadata()
    {
        $response = $this->getJson('/api/subscriptions/create');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'tiers' => [
                            '*' => ['id', 'name', 'price', 'billing_interval'],
                        ],
                        'coupons' => [
                            '*' => ['id', 'code', 'name', 'discount_type', 'discount_value'],
                        ],
                    ],
                ]);
    }

    /** @test */
    public function it_can_create_subscription_without_coupon()
    {
        $subscriptionData = [
            'tier_id' => $this->testTier->id,
            'user_id' => $this->testUser->id,
            'subscription_price' => 9.99,
            'auto_renewal' => true,
        ];

        $response = $this->postJson('/api/subscriptions', $subscriptionData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'subscription' => [
                            'id',
                            'user_id',
                            'tier_id',
                            'subscription_price',
                            'discounted_price',
                            'auto_renewal',
                            'is_active',
                            'status',
                        ],
                    ],
                ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'subscription_price' => 9.99,
            'discounted_price' => 9.99,
        ]);
    }

    /** @test */
    public function it_can_create_subscription_with_valid_coupon()
    {
        $subscriptionData = [
            'tier_id' => $this->testTier->id,
            'user_id' => $this->testUser->id,
            'subscription_price' => 9.99,
            'coupon_code' => 'TEST10',
            'auto_renewal' => true,
        ];

        $response = $this->postJson('/api/subscriptions', $subscriptionData);

        $response->assertStatus(200);

        // Verify discount was applied (10% off 9.99 = 8.991, rounded appropriately)
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'subscription_price' => 9.99,
            'discounted_price' => 8.99, // 10% discount
            'coupon_id' => $this->testCoupon->id,
        ]);
    }

    /** @test */
    public function it_rejects_subscription_creation_with_invalid_coupon()
    {
        $subscriptionData = [
            'tier_id' => $this->testTier->id,
            'user_id' => $this->testUser->id,
            'subscription_price' => 9.99,
            'coupon_code' => 'INVALID',
            'auto_renewal' => true,
        ];

        $response = $this->postJson('/api/subscriptions', $subscriptionData);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid or inapplicable coupon',
                ]);
    }

    /** @test */
    public function it_can_get_specific_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
        ]);

        $response = $this->getJson("/api/subscriptions/{$subscription->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'subscription' => [
                            'id',
                            'user_id',
                            'tier_id',
                            'status',
                            'tier' => ['id', 'name', 'price'],
                            'user' => ['id', 'name', 'email'],
                        ],
                    ],
                ]);
    }

    /** @test */
    public function it_can_update_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'auto_renewal' => true,
        ]);

        $updateData = [
            'auto_renewal' => false,
        ];

        $response = $this->putJson("/api/subscriptions/{$subscription->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'subscription' => [
                            'id' => $subscription->id,
                            'auto_renewal' => false,
                        ],
                    ],
                ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'auto_renewal' => false,
        ]);
    }

    /** @test */
    public function it_can_cancel_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'is_active' => true,
            'status' => 'active',
        ]);

        $response = $this->deleteJson("/api/subscriptions/{$subscription->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Subscription cancelled successfully',
                ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'is_active' => false,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_can_extend_trial()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'status' => 'trial',
            'trial_end_date' => now()->addDays(3),
        ]);

        $response = $this->postJson("/api/subscriptions/{$subscription->id}/extend-trial", [
            'days' => 5,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Trial extended successfully',
                ]);

        $subscription->refresh();
        $this->assertEquals(8, now()->diffInDays($subscription->trial_end_date));
    }

    /** @test */
    public function it_can_reactivate_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'is_active' => false,
            'status' => 'cancelled',
        ]);

        $response = $this->postJson("/api/subscriptions/{$subscription->id}/reactivate");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Subscription reactivated successfully',
                ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_get_user_subscriptions()
    {
        Subscription::factory()->count(2)->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
        ]);

        $response = $this->getJson("/api/subscriptions/user/{$this->testUser->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'subscriptions' => [
                            '*' => ['id', 'tier_id', 'status', 'is_active'],
                        ],
                    ],
                ]);

        $this->assertCount(2, $response->json('data.subscriptions'));
    }

    /** @test */
    public function it_can_get_active_subscriptions()
    {
        Subscription::factory()->count(3)->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'is_active' => true,
            'status' => 'active',
        ]);

        Subscription::factory()->count(2)->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'is_active' => false,
            'status' => 'cancelled',
        ]);

        $response = $this->getJson('/api/subscriptions/active');

        $response->assertStatus(200);

        $subscriptions = $response->json('data.subscriptions');
        $this->assertCount(3, $subscriptions);

        foreach ($subscriptions as $subscription) {
            $this->assertTrue($subscription['is_active']);
            $this->assertEquals('active', $subscription['status']);
        }
    }

    /** @test */
    public function it_can_get_expiring_soon_subscriptions()
    {
        // Create subscription expiring in 5 days
        Subscription::factory()->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'expires_at' => now()->addDays(5),
            'is_active' => true,
            'status' => 'active',
        ]);

        // Create subscription expiring in 15 days (should not be included)
        Subscription::factory()->create([
            'user_id' => $this->testUser->id,
            'tier_id' => $this->testTier->id,
            'expires_at' => now()->addDays(15),
            'is_active' => true,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/subscriptions/expiring-soon?days=7');

        $response->assertStatus(200);

        $subscriptions = $response->json('data.subscriptions');
        $this->assertCount(1, $subscriptions);
    }

    /** @test */
    public function it_can_validate_coupon()
    {
        $validationData = [
            'code' => 'TEST10',
            'tier_id' => $this->testTier->id,
            'amount' => 9.99,
        ];

        $response = $this->postJson('/api/coupons/validate', $validationData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'is_valid' => true,
                        'discount' => 0.99, // 10% of 9.99
                        'final_amount' => 8.99,
                    ],
                ]);
    }

    /** @test */
    public function it_rejects_invalid_coupon_validation()
    {
        $validationData = [
            'code' => 'INVALID',
            'tier_id' => $this->testTier->id,
            'amount' => 9.99,
        ];

        $response = $this->postJson('/api/coupons/validate', $validationData);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid coupon code',
                ]);
    }

    /** @test */
    public function it_validates_required_fields_for_subscription_creation()
    {
        $response = $this->postJson('/api/subscriptions', []);

        $response->assertStatus(400)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'errors' => [
                            'tier_id',
                            'user_id',
                            'subscription_price',
                        ],
                    ],
                ]);
    }

    /** @test */
    public function it_validates_tier_exists()
    {
        $subscriptionData = [
            'tier_id' => 99999, // Non-existent tier
            'user_id' => $this->testUser->id,
            'subscription_price' => 9.99,
        ];

        $response = $this->postJson('/api/subscriptions', $subscriptionData);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Bad request',
                ]);
    }
}
