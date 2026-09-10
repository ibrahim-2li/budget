<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'type' => fake()->randomElement(['income', 'expense']),
            'color' => 'text-'.fake()->randomElement(['red', 'green', 'blue', 'amber', 'teal']).'-500',
            'icon' => 'fa-solid fa-box',
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes): array => ['type' => 'income']);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes): array => ['type' => 'expense']);
    }
}
