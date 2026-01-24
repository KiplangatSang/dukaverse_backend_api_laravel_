<?php

namespace App\Services\DeliveryApp;

use App\Models\DeliveryCategory;
use App\Services\BaseService;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class CategoryService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all active delivery categories
     */
    public function getCategories(): Collection
    {
        try {
            return DeliveryCategory::active()
                ->orderBySort()
                ->get();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return collect();
        }
    }

    /**
     * Get category by ID
     */
    public function getCategoryById($categoryId)
    {
        try {
            return DeliveryCategory::findOrFail($categoryId);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }

    /**
     * Create new delivery category
     */
    public function createCategory($data)
    {
        try {
            $category = DeliveryCategory::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'allowed_vehicle_types' => $data['allowed_vehicle_types'] ?? [],
                'max_weight' => $data['max_weight'] ?? null,
                'max_dimensions' => $data['max_dimensions'] ?? [],
                'base_fee' => $data['base_fee'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
            ]);

            return $this->responseHelper->respond($category, 'Delivery category created successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to create delivery category', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update delivery category
     */
    public function updateCategory($categoryId, $data)
    {
        try {
            $category = DeliveryCategory::findOrFail($categoryId);

            $category->update([
                'name' => $data['name'] ?? $category->name,
                'description' => $data['description'] ?? $category->description,
                'allowed_vehicle_types' => $data['allowed_vehicle_types'] ?? $category->allowed_vehicle_types,
                'max_weight' => $data['max_weight'] ?? $category->max_weight,
                'max_dimensions' => $data['max_dimensions'] ?? $category->max_dimensions,
                'base_fee' => $data['base_fee'] ?? $category->base_fee,
                'is_active' => $data['is_active'] ?? $category->is_active,
                'sort_order' => $data['sort_order'] ?? $category->sort_order,
            ]);

            return $this->responseHelper->respond($category, 'Delivery category updated successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to update delivery category', 500, [$e->getMessage()]);
        }
    }

    /**
     * Delete delivery category
     */
    public function deleteCategory($categoryId)
    {
        try {
            $category = DeliveryCategory::findOrFail($categoryId);

            // Check if category has deliveries
            if ($category->deliveries()->count() > 0) {
                return $this->responseHelper->error('Cannot delete category with existing deliveries', 400);
            }

            $category->delete();

            return $this->responseHelper->respond(null, 'Delivery category deleted successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to delete delivery category', 500, [$e->getMessage()]);
        }
    }

    /**
     * Check if vehicle type is allowed for category
     */
    public function isVehicleAllowedForCategory($categoryId, $vehicleType)
    {
        try {
            $category = DeliveryCategory::findOrFail($categoryId);
            return $category->allowsVehicleType($vehicleType);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Get categories compatible with vehicle type
     */
    public function getCategoriesForVehicleType($vehicleType)
    {
        try {
            return DeliveryCategory::active()
                ->whereJsonContains('allowed_vehicle_types', $vehicleType)
                ->orderBySort()
                ->get();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return collect();
        }
    }
}
