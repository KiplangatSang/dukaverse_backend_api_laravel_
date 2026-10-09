<?php

namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionsService extends BaseService
{
 
    /**
     * List permissions for a given ownerable (user/account), including permissions unlocked via active subscription tiers.
     *
     * @param  mixed  $ownerable
     * @return \Illuminate\Support\Collection
     */
    public function listPermissions($ownerable)
    {
        try {
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
        } catch (\Exception $e) {
            // Since this method returns a collection, we can't return an ApiResource here
            // But we can log the error or handle it appropriately
            // For now, return empty collection
            return collect();
        }
    }
}