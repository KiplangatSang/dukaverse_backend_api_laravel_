<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

use App\Models\Tier;

class PermissionsService
{
    /**
     * List permissions for a given ownerable (user/account), including permissions unlocked via active subscription tiers.
     *
     * @param  mixed  $ownerable
     * @return \Illuminate\Support\Collection
     */
    public function listPermissions($ownerable)
    {
        // Fetch direct permissions owned by ownerable
        $directPermissions = Permission::where('ownerable_id', $ownerable->getKey())
            ->where('ownerable_type', get_class($ownerable))
            ->get();

        // Fetch active subscriptions for ownerable with related tiers and permissions
        $activeSubscriptions = \App\Models\Subscription::where('ownerable_id', $ownerable->getKey())
            ->where('ownerable_type', get_class($ownerable))
            ->active()
            ->with('tier.permissions')
            ->get();

        $tierPermissions = collect();
        foreach ($activeSubscriptions as $subscription) {
            if ($subscription->tier) {
                $tierPermissions = $tierPermissions->merge($subscription->tier->permissions);
            }
        }

        // Merge direct and tier permissions without duplicates
        return $directPermissions->merge($tierPermissions)->unique('id')->values();
    }

    /**
     * Check if the given ownerable has ecommerce access permission
     *
     * @param mixed $ownerable
     * @return bool
     */
    public function hasEcommercePermission($ownerable): bool
    {
        $permissions = $this->listPermissions($ownerable);

        if ($permissions->isEmpty()) {
            // Allow if no permissions found
            return true;
        }

        return $permissions->contains(function ($permission) {
            return $permission->name === 'ecommerce_access';
        });
    }

    /**
     * Create a new permission for the given ownerable.
     *
     * @param  mixed  $ownerable
     * @param  array  $data
     * @return Permission
     */
    public function createPermission($ownerable, array $data): Permission
    {
        $data['ownerable_id'] = $ownerable->getKey();
        $data['ownerable_type'] = get_class($ownerable);

        return Permission::create($data);
    }

    /**
     * Update permission by id.
     *
     * @param  int  $id
     * @param  array  $data
     * @return Permission
     *
     * @throws ModelNotFoundException
     */
    public function updatePermission(int $id, array $data): Permission
    {
        $permission = Permission::findOrFail($id);
        $permission->fill($data);
        $permission->save();

        return $permission;
    }

    /**
     * Delete permission by id.
     *
     * @param  int  $id
     * @return bool
     *
     * @throws ModelNotFoundException
     */
    public function deletePermission(int $id): bool
    {
        $permission = Permission::findOrFail($id);
        return $permission->delete();
    }

    /**
     * Assign permissions to a tier
     */
    public function assignPermissionsToTier(Tier $tier, array $permissionIds): void
    {
        $tier->permissions()->syncWithoutDetaching($permissionIds);
    }

    /**
     * Remove permission from a tier
     */
    public function removePermissionFromTier(Tier $tier, int $permissionId): void
    {
        $tier->permissions()->detach($permissionId);
    }

    /**
     * List permissions assigned to a tier
     */
    public function listPermissionsByTier(Tier $tier)
    {
        return $tier->permissions()->get();
    }
}
