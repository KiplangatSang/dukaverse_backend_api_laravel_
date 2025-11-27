<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition()
    {
        return [
            'ownerable_id' => 1, // default, can be overridden
            'ownerable_type' => 'App\\Models\\User', // default type, can be overridden
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
