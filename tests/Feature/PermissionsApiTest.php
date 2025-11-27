<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Tier;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $authHeader;
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user and assign role for account type
        $this->user = User::factory()->create([
            'role' => User::ADMIN_ACCOUNT_TYPE,  // Adjust role to match Office account or relevant type for your app
        ]);

        // Create Office account linked to user
        $this->account = Office::factory()->create([
            'user_id' => $this->user->id,
            'ownerable_type' => get_class($this->user),
            'ownerable_id' => $this->user->id,
        ]);

        // Authenticate user with Sanctum token
        $token = $this->user->createToken('TestToken')->plainTextToken;
        $this->authHeader = 'Bearer ' . $token;
    }

    /** @test */
    public function it_lists_permissions_for_authenticated_user()
    {
        $permission = Permission::factory()->create([
            'ownerable_id' => $this->account->id,
            'ownerable_type' => get_class($this->account),
            'name' => 'direct_permission',
        ]);

        $tier = Tier::factory()->create();
        $tierPermission = Permission::factory()->create();
        $tier->permissions()->attach($tierPermission);

        Subscription::factory()->create([
            'ownerable_id' => $this->account->id,
            'ownerable_type' => get_class($this->account),
            'tier_id' => $tier->id,
            'user_id' => $this->user->id,
            'status' => Subscription::STATUS_ACTIVE,
            'expires_at' => now()->addDays(10),
        ]);

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->getJson('/api/v1/user/permissions-list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'permissions' => [
                    '*' => ['id', 'name', 'ownerable_type', 'ownerable_id']
                ]
            ])
            ->assertJsonFragment(['name' => 'direct_permission'])
            ->assertJsonFragment(['id' => $tierPermission->id]);
    }

    /** @test */
    public function it_denies_permissions_list_to_unauthenticated()
    {
        $response = $this->getJson('/api/v1/user/permissions-list');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_creates_permission_for_authenticated_user()
    {
        $payload = ['name' => 'new_permission', 'description' => 'desc'];

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->postJson('/api/v1/user/permissions', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'new_permission']);
        $this->assertDatabaseHas('permissions', ['name' => 'new_permission', 'ownerable_id' => $this->account->id]);
    }

    /** @test */
    public function it_validates_permission_creation()
    {
        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->postJson('/api/v1/user/permissions', []);

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_denies_permission_creation_to_unauthenticated()
    {
        $response = $this->postJson('/api/v1/user/permissions', ['name' => 'test']);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_fetches_specific_permission_for_authenticated_user()
    {
        $permission = Permission::factory()->create([
            'ownerable_id' => $this->account->id,
            'ownerable_type' => get_class($this->account),
            'name' => 'fetch_permission'
        ]);

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->getJson("/api/v1/user/permissions/{$permission->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'fetch_permission']);
    }

    /** @test */
    public function it_returns_404_when_permission_not_found_for_show()
    {
        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->getJson("/api/v1/user/permissions/999999");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_denies_show_permission_to_unauthenticated()
    {
        $permission = Permission::factory()->create();

        $response = $this->getJson("/api/v1/user/permissions/{$permission->id}");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_updates_permission_for_authenticated_user()
    {
        $permission = Permission::factory()->create([
            'ownerable_id' => $this->account->id,
            'ownerable_type' => get_class($this->account),
            'name' => 'old_name'
        ]);

        $payload = ['name' => 'updated_name'];

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->putJson("/api/v1/user/permissions/{$permission->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'updated_name']);
        $this->assertDatabaseHas('permissions', ['id' => $permission->id, 'name' => 'updated_name']);
    }

    /** @test */
    public function it_validates_permission_update()
    {
        $permission = Permission::factory()->create([
            'ownerable_id' => $this->account->id,
            'ownerable_type' => get_class($this->account)
        ]);

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->putJson("/api/v1/user/permissions/{$permission->id}", ['name' => '']);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_returns_404_when_permission_not_found_for_update()
    {
        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->putJson("/api/v1/user/permissions/999999", ['name' => 'test']);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_denies_update_permission_to_unauthenticated()
    {
        $permission = Permission::factory()->create();

        $response = $this->putJson("/api/v1/user/permissions/{$permission->id}", ['name' => 'test']);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_deletes_permission_for_authenticated_user()
    {
        $permission = Permission::factory()->create([
            'ownerable_id' => $this->account->id,
            'ownerable_type' => get_class($this->account)
        ]);

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->deleteJson("/api/v1/user/permissions/{$permission->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    /** @test */
    public function it_returns_404_when_permission_not_found_for_delete()
    {
        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->deleteJson("/api/v1/user/permissions/999999");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_denies_delete_permission_to_unauthenticated()
    {
        $permission = Permission::factory()->create();

        $response = $this->deleteJson("/api/v1/user/permissions/{$permission->id}");
        $response->assertStatus(401);
    }

    // Tier-Permission linking APIs tests

    /** @test */
    public function it_lists_permissions_assigned_to_tier()
    {
        $tier = Tier::factory()->create();
        $permission = Permission::factory()->create();
        $tier->permissions()->attach($permission);

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->getJson("/api/v1/tiers/{$tier->id}/permissions");

        $response->assertStatus(200)
            ->assertJsonStructure(['permissions'])
            ->assertJsonFragment(['id' => $permission->id]);
    }

    /** @test */
    public function it_returns_404_when_tier_not_found_for_listing_permissions()
    {
        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->getJson("/api/v1/tiers/999999/permissions");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_denies_listing_tier_permissions_to_unauthenticated()
    {
        $tier = Tier::factory()->create();

        $response = $this->getJson("/api/v1/tiers/{$tier->id}/permissions");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_assigns_permissions_to_tier()
    {
        $tier = Tier::factory()->create();
        $permission1 = Permission::factory()->create();
        $permission2 = Permission::factory()->create();

        $payload = ['permission_ids' => [$permission1->id, $permission2->id]];

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->postJson("/api/v1/tiers/{$tier->id}/permissions", $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['id' => $permission1->id])
            ->assertJsonFragment(['id' => $permission2->id]);

        $this->assertDatabaseHas('permission_tier', ['tier_id' => $tier->id, 'permission_id' => $permission1->id]);
        $this->assertDatabaseHas('permission_tier', ['tier_id' => $tier->id, 'permission_id' => $permission2->id]);
    }

    /** @test */
    public function it_validates_permission_assignment_to_tier()
    {
        $tier = Tier::factory()->create();

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->postJson("/api/v1/tiers/{$tier->id}/permissions", ['permission_ids' => ['invalid']]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_returns_404_when_tier_not_found_for_permission_assignment()
    {
        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->postJson("/api/v1/tiers/999999/permissions", ['permission_ids' => [1]]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_denies_permission_assignment_to_tier_to_unauthenticated()
    {
        $tier = Tier::factory()->create();
        $permission = Permission::factory()->create();

        $response = $this->postJson("/api/v1/tiers/{$tier->id}/permissions", ['permission_ids' => [$permission->id]]);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_removes_permission_from_tier()
    {
        $tier = Tier::factory()->create();
        $permission = Permission::factory()->create();
        $tier->permissions()->attach($permission);

        $response = $this->withHeaders(['Authorization' => $this->authHeader])
            ->deleteJson("/api/v1/tiers/{$tier->id}/permissions/{$permission->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('permission_tier', ['tier_id' => $tier->id, 'permission_id' => $permission->id]);
    }

    /** @test */
    public function it_returns_404_when_tier_or_permission_not_found_for_removal()
    {
        $tier = Tier::factory()->create();
        $permission = Permission::factory()->create();

        $response1 = $this->withHeaders(['Authorization' => $this->authHeader])
            ->deleteJson("/api/v1/tiers/999999/permissions/{$permission->id}");
        $response1->assertStatus(404);

        $response2 = $this->withHeaders(['Authorization' => $this->authHeader])
            ->deleteJson("/api/v1/tiers/{$tier->id}/permissions/999999");
        $response2->assertStatus(404);
    }

    /** @test */
    public function it_denies_permission_removal_from_tier_to_unauthenticated()
    {
        $tier = Tier::factory()->create();
        $permission = Permission::factory()->create();
        $tier->permissions()->attach($permission);

        $response = $this->deleteJson("/api/v1/tiers/{$tier->id}/permissions/{$permission->id}");
        $response->assertStatus(401);
    }
}

?>
