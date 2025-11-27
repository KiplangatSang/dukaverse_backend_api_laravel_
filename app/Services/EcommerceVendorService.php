<?php

namespace App\Services;

use App\Models\Ecommerce;
use App\Services\PermissionsService;
use Illuminate\Support\Facades\Auth;

class EcommerceVendorService
{
    protected $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    public function listVendors()
    {
        $user = Auth::user();
        return Ecommerce::where('user_id', $user->id)->get();
    }

    public function createVendor(array $data)
    {
        $user = Auth::user();

        if (! $this->permissionsService->hasEcommercePermission($user)) {
            return null; // Unauthorized due to missing ecommerce permission
        }

        $data['user_id'] = $user->id;

        return Ecommerce::create($data);
    }

    public function getVendor(string $id)
    {
        $user = Auth::user();

        return Ecommerce::where('user_id', $user->id)->findOrFail($id);
    }

    public function updateVendor(string $id, array $data)
    {
        $user = Auth::user();
        $vendor = Ecommerce::where('user_id', $user->id)->findOrFail($id);

        $vendor->update($data);

        return $vendor;
    }

    public function deleteVendor(string $id)
    {
        $user = Auth::user();
        $vendor = Ecommerce::where('user_id', $user->id)->findOrFail($id);

        $vendor->delete();

        return true;
    }
}
