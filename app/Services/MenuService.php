<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemCustomization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class MenuService extends BaseService
{
    public function __construct(
        StoreFileResource $storeFileResource,
        private ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    /**
     * Get menu categories for a restaurant
     */
    public function getCategories($restaurantId, $filters = [])
    {
        try {
            $query = MenuCategory::where('restaurant_id', $restaurantId)
                               ->where('ownerable_type', get_class(Auth::user()))
                               ->where('ownerable_id', Auth::id());

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['parent_id'])) {
                $query->where('parent_id', $filters['parent_id']);
            }

            $categories = $query->orderBy('sort_order')
                              ->orderBy('name')
                              ->get()
                              ->toTree();

            return $this->responseHelper->respond($categories, 'Menu categories retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve menu categories', 500, [$e->getMessage()]);
        }
    }

    /**
     * Create menu category
     */
    public function createCategory($restaurantId, $data)
    {
        try {
            $category = MenuCategory::create([
                'restaurant_id' => $restaurantId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'status' => $data['status'] ?? 'active',
                'is_available' => $data['is_available'] ?? true,
                'availability_schedule' => $data['availability_schedule'] ?? null,
                'ownerable_type' => get_class(Auth::user()),
                'ownerable_id' => Auth::id(),
            ]);

            return $this->responseHelper->respond($category, 'Menu category created successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to create menu category', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update menu category
     */
    public function updateCategory($restaurantId, $categoryId, $data)
    {
        try {
            $category = MenuCategory::where('restaurant_id', $restaurantId)
                                  ->where('ownerable_type', get_class(Auth::user()))
                                  ->where('ownerable_id', Auth::id())
                                  ->findOrFail($categoryId);

            $category->update([
                'name' => $data['name'] ?? $category->name,
                'description' => $data['description'] ?? $category->description,
                'image_url' => $data['image_url'] ?? $category->image_url,
                'parent_id' => $data['parent_id'] ?? $category->parent_id,
                'sort_order' => $data['sort_order'] ?? $category->sort_order,
                'status' => $data['status'] ?? $category->status,
                'is_available' => $data['is_available'] ?? $category->is_available,
                'availability_schedule' => $data['availability_schedule'] ?? $category->availability_schedule,
            ]);

            return $this->responseHelper->respond($category, 'Menu category updated successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to update menu category', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get menu items for a restaurant
     */
    public function getItems($restaurantId, $filters = [])
    {
        try {
            $query = MenuItem::with(['category', 'customizations'])
                           ->where('restaurant_id', $restaurantId)
                           ->where('ownerable_type', get_class(Auth::user()))
                           ->where('ownerable_id', Auth::id());

            // Apply filters
            if (isset($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['is_available'])) {
                $query->where('is_available', $filters['is_available']);
            }

            if (isset($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            if (isset($filters['tags'])) {
                $query->whereJsonContains('tags', $filters['tags']);
            }

            $items = $query->orderBy('category_id')
                         ->orderBy('sort_order')
                         ->paginate($filters['limit'] ?? 20);

            return $this->responseHelper->respond($items, 'Menu items retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve menu items', 500, [$e->getMessage()]);
        }
    }

    /**
     * Create menu item
     */
    public function createItem($restaurantId, $data)
    {
        try {
            DB::beginTransaction();

            $item = MenuItem::create([
                'restaurant_id' => $restaurantId,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'cost_price' => $data['cost_price'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'images' => $data['images'] ?? [],
                'sku' => $data['sku'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'status' => $data['status'] ?? 'active',
                'is_available' => $data['is_available'] ?? true,
                'is_featured' => $data['is_featured'] ?? false,
                'sort_order' => $data['sort_order'] ?? 0,
                'preparation_time' => $data['preparation_time'] ?? 15,
                'calories' => $data['calories'] ?? null,
                'allergens' => $data['allergens'] ?? [],
                'dietary_info' => $data['dietary_info'] ?? [],
                'tags' => $data['tags'] ?? [],
                'ingredients' => $data['ingredients'] ?? [],
                'nutritional_info' => $data['nutritional_info'] ?? null,
                'availability_schedule' => $data['availability_schedule'] ?? null,
                'ownerable_type' => get_class(Auth::user()),
                'ownerable_id' => Auth::id(),
            ]);

            // Create customizations if provided
            if (isset($data['customizations'])) {
                foreach ($data['customizations'] as $customizationData) {
                    $this->createCustomization($item->id, $customizationData);
                }
            }

            DB::commit();

            return $this->responseHelper->respond(
                $item->load('customizations'),
                'Menu item created successfully'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseHelper->error('Failed to create menu item', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update menu item
     */
    public function updateItem($restaurantId, $itemId, $data)
    {
        try {
            $item = MenuItem::where('restaurant_id', $restaurantId)
                          ->where('ownerable_type', get_class(Auth::user()))
                          ->where('ownerable_id', Auth::id())
                          ->findOrFail($itemId);

            DB::beginTransaction();

            $item->update([
                'category_id' => $data['category_id'] ?? $item->category_id,
                'name' => $data['name'] ?? $item->name,
                'description' => $data['description'] ?? $item->description,
                'price' => $data['price'] ?? $item->price,
                'cost_price' => $data['cost_price'] ?? $item->cost_price,
                'image_url' => $data['image_url'] ?? $item->image_url,
                'images' => $data['images'] ?? $item->images,
                'sku' => $data['sku'] ?? $item->sku,
                'barcode' => $data['barcode'] ?? $item->barcode,
                'status' => $data['status'] ?? $item->status,
                'is_available' => $data['is_available'] ?? $item->is_available,
                'is_featured' => $data['is_featured'] ?? $item->is_featured,
                'sort_order' => $data['sort_order'] ?? $item->sort_order,
                'preparation_time' => $data['preparation_time'] ?? $item->preparation_time,
                'calories' => $data['calories'] ?? $item->calories,
                'allergens' => $data['allergens'] ?? $item->allergens,
                'dietary_info' => $data['dietary_info'] ?? $item->dietary_info,
                'tags' => $data['tags'] ?? $item->tags,
                'ingredients' => $data['ingredients'] ?? $item->ingredients,
                'nutritional_info' => $data['nutritional_info'] ?? $item->nutritional_info,
                'availability_schedule' => $data['availability_schedule'] ?? $item->availability_schedule,
            ]);

            // Update customizations if provided
            if (isset($data['customizations'])) {
                // Remove existing customizations
                $item->customizations()->delete();

                // Create new customizations
                foreach ($data['customizations'] as $customizationData) {
                    $this->createCustomization($item->id, $customizationData);
                }
            }

            DB::commit();

            return $this->responseHelper->respond(
                $item->fresh(['customizations']),
                'Menu item updated successfully'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseHelper->error('Failed to update menu item', 500, [$e->getMessage()]);
        }
    }

    /**
     * Create customization for menu item
     */
    private function createCustomization($itemId, $data)
    {
        return MenuItemCustomization::create([
            'menu_item_id' => $itemId,
            'name' => $data['name'],
            'type' => $data['type'], // single, multiple, quantity
            'is_required' => $data['is_required'] ?? false,
            'min_selections' => $data['min_selections'] ?? 0,
            'max_selections' => $data['max_selections'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'options' => $data['options'] ?? [],
        ]);
    }

    /**
     * Get menu item customizations
     */
    public function getItemCustomizations($restaurantId, $itemId)
    {
        try {
            $item = MenuItem::where('restaurant_id', $restaurantId)
                          ->where('ownerable_type', get_class(Auth::user()))
                          ->where('ownerable_id', Auth::id())
                          ->findOrFail($itemId);

            $customizations = $item->customizations()
                                 ->orderBy('sort_order')
                                 ->get();

            return $this->responseHelper->respond($customizations, 'Item customizations retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve item customizations', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update item availability
     */
    public function updateItemAvailability($restaurantId, $itemId, $isAvailable)
    {
        try {
            $item = MenuItem::where('restaurant_id', $restaurantId)
                          ->where('ownerable_type', get_class(Auth::user()))
                          ->where('ownerable_id', Auth::id())
                          ->findOrFail($itemId);

            $item->update(['is_available' => $isAvailable]);

            return $this->responseHelper->respond(
                $item,
                'Item availability updated successfully'
            );
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to update item availability', 500, [$e->getMessage()]);
        }
    }

    /**
     * Bulk update item availability
     */
    public function bulkUpdateAvailability($restaurantId, $data)
    {
        try {
            DB::beginTransaction();

            $updated = 0;
            foreach ($data['items'] as $itemData) {
                $item = MenuItem::where('restaurant_id', $restaurantId)
                              ->where('ownerable_type', get_class(Auth::user()))
                              ->where('ownerable_id', Auth::id())
                              ->find($itemData['id']);

                if ($item) {
                    $item->update(['is_available' => $itemData['is_available']]);
                    $updated++;
                }
            }

            DB::commit();

            return $this->responseHelper->respond(
                ['updated_count' => $updated],
                'Bulk availability update completed successfully'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseHelper->error('Failed to bulk update availability', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get complete menu for restaurant
     */
    public function getCompleteMenu($restaurantId, $filters = [])
    {
        try {
            // Get active categories
            $categories = MenuCategory::where('restaurant_id', $restaurantId)
                                    ->where('ownerable_type', get_class(Auth::user()))
                                    ->where('ownerable_id', Auth::id())
                                    ->where('status', 'active')
                                    ->orderBy('sort_order')
                                    ->get();

            $menu = [];
            foreach ($categories as $category) {
                $items = MenuItem::where('category_id', $category->id)
                               ->where('status', 'active')
                               ->where('is_available', true)
                               ->with('customizations')
                               ->orderBy('sort_order')
                               ->get();

                if ($items->count() > 0) {
                    $menu[] = [
                        'category' => $category,
                        'items' => $items,
                    ];
                }
            }

            return $this->responseHelper->respond($menu, 'Complete menu retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve complete menu', 500, [$e->getMessage()]);
        }
    }

    /**
     * Search menu items
     */
    public function searchItems($restaurantId, $query, $filters = [])
    {
        try {
            $searchQuery = MenuItem::where('restaurant_id', $restaurantId)
                                 ->where('ownerable_type', get_class(Auth::user()))
                                 ->where('ownerable_id', Auth::id())
                                 ->where('status', 'active')
                                 ->where('is_available', true)
                                 ->where(function($q) use ($query) {
                                     $q->where('name', 'like', '%' . $query . '%')
                                       ->orWhere('description', 'like', '%' . $query . '%')
                                       ->orWhereJsonContains('tags', $query)
                                       ->orWhereJsonContains('ingredients', $query);
                                 });

            // Apply additional filters
            if (isset($filters['category_id'])) {
                $searchQuery->where('category_id', $filters['category_id']);
            }

            if (isset($filters['max_price'])) {
                $searchQuery->where('price', '<=', $filters['max_price']);
            }

            if (isset($filters['dietary_restrictions'])) {
                foreach ($filters['dietary_restrictions'] as $restriction) {
                    $searchQuery->whereJsonDoesntContain('dietary_info', $restriction);
                }
            }

            $results = $searchQuery->with(['category', 'customizations'])
                                 ->orderBy('name')
                                 ->limit($filters['limit'] ?? 50)
                                 ->get();

            return $this->responseHelper->respond($results, 'Menu search completed successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to search menu items', 500, [$e->getMessage()]);
        }
    }
}
