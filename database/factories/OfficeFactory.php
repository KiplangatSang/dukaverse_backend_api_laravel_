<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'name_id' => $this->faker->unique()->word,
            'user_id' => 1, // default user id, override in tests if needed
            'ownerable_type' => 'App\\Models\\User', // default ownerable type
            'ownerable_id' => 1, // default ownerable id
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
