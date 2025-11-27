<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Tier;
use App\Models\Coupon;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateApiTestingScenarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:create-testing-scenarios {--clean : Clean existing test data first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create comprehensive testing scenarios for subscription API endpoints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('clean')) {
            $this->cleanExistingTestData();
        }

        $this->info('Creating subscription API testing scenarios...');

        // Create test users
        $users = $this->createTestUsers();

        // Create test tiers
        $tiers = $this->createTestTiers();

        // Create test coupons
        $coupons = $this->createTestCoupons($tiers);

        // Create various subscription scenarios
        $this->createSubscriptionScenarios($users, $tiers, $coupons);

        $this->info('Testing scenarios created successfully!');
        $this->displayTestingGuide();
    }

    private function cleanExistingTestData()
    {
        $this->info('Cleaning existing test data...');

        // Delete test subscriptions
        Subscription::where('user_id', '>=', 1000)->delete();

        // Delete test coupons
        Coupon::where('code', 'like', 'TEST%')->delete();

        // Delete test tiers
        Tier::where('name', 'like', 'Test%')->delete();

        // Delete test users
        User::where('email', 'like', 'test%@%')->delete();

        $this->info('Test data cleaned.');
    }

    private function createTestUsers()
    {
        $this->info('Creating test users...');

        $users = [
            [
                'id' => 1001,
                'name' => 'Test User Active',
                'username' => 'testuseractive',
                'email' => 'test.active@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
            [
                'id' => 1002,
                'name' => 'Test User Trial',
                'username' => 'testusertrial',
                'email' => 'test.trial@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
            [
                'id' => 1003,
                'name' => 'Test User Expired',
                'username' => 'testuserexpired',
                'email' => 'test.expired@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
            [
                'id' => 1004,
                'name' => 'Test User Cancelled',
                'username' => 'testusercancelled',
                'email' => 'test.cancelled@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(['id' => $userData['id']], $userData);
        }

        $this->info('Created 4 test users.');
        return collect($users)->pluck('id')->toArray();
    }

    private function createTestTiers()
    {
        $this->info('Creating test tiers...');

        $tiers = [
            [
                'id' => 1001,
                'name' => 'Test Basic',
                'description' => 'Basic test tier',
                'price' => 9.99,
                'billing_duration' => 'month',
                'trial_period_days' => 7,
                'is_active' => true,
            ],
            [
                'id' => 1002,
                'name' => 'Test Premium',
                'description' => 'Premium test tier',
                'price' => 19.99,
                'billing_duration' => 'month',
                'trial_period_days' => 14,
                'is_active' => true,
            ],
            [
                'id' => 1003,
                'name' => 'Test Enterprise',
                'description' => 'Enterprise test tier',
                'price' => 49.99,
                'billing_duration' => 'year',
                'trial_period_days' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tierData) {
            Tier::updateOrCreate(['id' => $tierData['id']], $tierData);
        }

        $this->info('Created 3 test tiers.');
        return collect($tiers)->pluck('id')->toArray();
    }

    private function createTestCoupons($tiers)
    {
        $this->info('Creating test coupons...');

        $coupons = [
            [
                'code' => 'TEST10PERCENT',
                'name' => 'Test 10% Off',
                'description' => '10% discount for testing',
                'discount_type' => 'percentage',
                'discount_value' => 10.0,
                'minimum_amount' => 10.00,
                'usage_limit' => 100,
                'is_active' => true,
                'applicable_tiers' => $tiers,
            ],
            [
                'code' => 'TEST5FIXED',
                'name' => 'Test $5 Off',
                'description' => '$5 fixed discount for testing',
                'discount_type' => 'fixed',
                'discount_value' => 5.00,
                'minimum_amount' => 15.00,
                'usage_limit' => 50,
                'is_active' => true,
                'applicable_tiers' => $tiers,
            ],
            [
                'code' => 'TESTEXPIRED',
                'name' => 'Test Expired Coupon',
                'description' => 'Expired coupon for testing',
                'discount_type' => 'percentage',
                'discount_value' => 20.0,
                'minimum_amount' => 5.00,
                'usage_limit' => 10,
                'starts_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->subDays(1),
                'is_active' => true,
                'applicable_tiers' => $tiers,
            ],
        ];

        foreach ($coupons as $couponData) {
            $coupon = Coupon::updateOrCreate(
                ['code' => $couponData['code']],
                $couponData
            );
            $coupon->applicable_tiers = $couponData['applicable_tiers'];
            $coupon->save();
        }

        $this->info('Created 3 test coupons.');
        return collect($coupons)->pluck('code')->toArray();
    }

    private function createSubscriptionScenarios($users, $tiers, $coupons)
    {
        $this->info('Creating subscription testing scenarios...');

        $scenarios = [
            // Active subscription with auto-renewal
            [
                'user_id' => 1001,
                'tier_id' => 1001,
                'subscription_price' => 9.99,
                'discounted_price' => 9.99,
                'auto_renewal' => true,
                'is_active' => true,
                'status' => 'active',
                'expires_at' => Carbon::now()->addDays(20),
                'coupon_code' => null,
            ],
            // Trial subscription
            [
                'user_id' => 1002,
                'tier_id' => 1002,
                'subscription_price' => 19.99,
                'discounted_price' => 19.99,
                'auto_renewal' => true,
                'is_active' => true,
                'status' => 'trial',
                'trial_end_date' => Carbon::now()->addDays(5),
                'coupon_code' => null,
            ],
            // Expired subscription
            [
                'user_id' => 1003,
                'tier_id' => 1001,
                'subscription_price' => 9.99,
                'discounted_price' => 9.99,
                'auto_renewal' => false,
                'is_active' => false,
                'status' => 'expired',
                'expires_at' => Carbon::now()->subDays(5),
                'coupon_code' => null,
            ],
            // Cancelled subscription
            [
                'user_id' => 1004,
                'tier_id' => 1003,
                'subscription_price' => 49.99,
                'discounted_price' => 49.99,
                'auto_renewal' => false,
                'is_active' => false,
                'status' => 'cancelled',
                'expires_at' => Carbon::now()->addDays(100),
                'cancelled_at' => Carbon::now()->subDays(1),
                'coupon_code' => null,
            ],
            // Subscription with coupon discount
            [
                'user_id' => 1001,
                'tier_id' => 1002,
                'subscription_price' => 19.99,
                'discounted_price' => 17.99, // 10% off
                'auto_renewal' => true,
                'is_active' => true,
                'status' => 'active',
                'expires_at' => Carbon::now()->addDays(15),
                'coupon_code' => 'TEST10PERCENT',
            ],
            // Payment failed subscription
            [
                'user_id' => 1002,
                'tier_id' => 1001,
                'subscription_price' => 9.99,
                'discounted_price' => 9.99,
                'auto_renewal' => true,
                'is_active' => true,
                'status' => 'payment_failed',
                'expires_at' => Carbon::now()->subDays(1),
                'retry_count' => 1,
                'coupon_code' => null,
            ],
        ];

        foreach ($scenarios as $scenario) {
            $couponId = null;
            if ($scenario['coupon_code']) {
                $coupon = Coupon::where('code', $scenario['coupon_code'])->first();
                $couponId = $coupon ? $coupon->id : null;
            }

            Subscription::updateOrCreate(
                [
                    'user_id' => $scenario['user_id'],
                    'tier_id' => $scenario['tier_id'],
                ],
                array_merge($scenario, ['coupon_id' => $couponId])
            );
        }

        $this->info('Created 6 subscription testing scenarios.');
    }

    private function displayTestingGuide()
    {
        $this->info("\n" . str_repeat('=', 60));
        $this->info('SUBSCRIPTION API TESTING GUIDE');
        $this->info(str_repeat('=', 60));

        $this->line("\n📋 TEST USERS:");
        $this->line("• test.active@example.com (ID: 1001) - Active subscriptions");
        $this->line("• test.trial@example.com (ID: 1002) - Trial subscriptions");
        $this->line("• test.expired@example.com (ID: 1003) - Expired subscriptions");
        $this->line("• test.cancelled@example.com (ID: 1004) - Cancelled subscriptions");

        $this->line("\n🏷️  TEST COUPONS:");
        $this->line("• TEST10PERCENT - 10% off (min $10)");
        $this->line("• TEST5FIXED - $5 off (min $15)");
        $this->line("• TESTEXPIRED - Expired coupon");

        $this->line("\n🔧 API ENDPOINTS TO TEST:");

        $this->line("\n1. SUBSCRIPTION MANAGEMENT:");
        $this->line("   GET    /api/subscriptions - List all subscriptions");
        $this->line("   GET    /api/subscriptions/create - Get creation metadata");
        $this->line("   POST   /api/subscriptions - Create subscription");
        $this->line("   GET    /api/subscriptions/{id} - Get specific subscription");
        $this->line("   PUT    /api/subscriptions/{id} - Update subscription");
        $this->line("   DELETE /api/subscriptions/{id} - Cancel subscription");

        $this->line("\n2. SUBSCRIPTION OPERATIONS:");
        $this->line("   POST   /api/subscriptions/{id}/extend-trial - Extend trial");
        $this->line("   POST   /api/subscriptions/{id}/reactivate - Reactivate subscription");
        $this->line("   GET    /api/subscriptions/user/{userId} - Get user subscriptions");
        $this->line("   GET    /api/subscriptions/active - Get active subscriptions");
        $this->line("   GET    /api/subscriptions/expiring-soon - Get expiring subscriptions");
        $this->line("   GET    /api/subscriptions/trial-ending-soon - Get trial ending subscriptions");

        $this->line("\n3. COUPON MANAGEMENT:");
        $this->line("   GET    /api/coupons - List all coupons");
        $this->line("   POST   /api/coupons/validate - Validate coupon");

        $this->line("\n🧪 TESTING SCENARIOS:");

        $this->line("\n• Create subscription with valid coupon");
        $this->line("• Create subscription with invalid/expired coupon");
        $this->line("• Update subscription tier (upgrade/downgrade)");
        $this->line("• Cancel and reactivate subscription");
        $this->line("• Extend trial period");
        $this->line("• Test validation errors (invalid tier, coupon, etc.)");
        $this->line("• Test authorization (wrong user, missing permissions)");

        $this->line("\n💡 COMMAND EXAMPLES:");

        $this->line("\n# Create subscription with coupon:");
        $this->line('curl -X POST /api/subscriptions \\');
        $this->line('  -H "Authorization: Bearer {token}" \\');
        $this->line('  -d "tier_id=1001&user_id=1001&coupon_code=TEST10PERCENT&subscription_price=9.99"');

        $this->line("\n# Validate coupon:");
        $this->line('curl -X POST /api/coupons/validate \\');
        $this->line('  -H "Authorization: Bearer {token}" \\');
        $this->line('  -d "code=TEST10PERCENT&tier_id=1001&amount=9.99"');

        $this->line("\n# Get expiring subscriptions:");
        $this->line('curl -X GET "/api/subscriptions/expiring-soon?days=7" \\');
        $this->line('  -H "Authorization: Bearer {token}"');

        $this->info(str_repeat('=', 60));
    }
}
