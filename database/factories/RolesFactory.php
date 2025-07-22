<?php

namespace Database\Factories;

use App\Modules\Roles\Models\Roles;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Roles\Models\Roles>
 */
class RolesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Roles::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Role_' . $this->faker->unique()->word();
        
        return [
            'name' => $name,
            'guard_name' => 'web',
            'level' => $this->faker->numberBetween(1, 5),
            'active' => $this->faker->boolean(80), // 80% chance of being active
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
    /**
     * Configure the model factory.
     */
    public function configure()
    {
        return $this->afterCreating(function (Roles $role) {
            // Optional: Add permissions or other related data after creation
        });
    }
}
