<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->text(fake()->numberBetween(5, 255)),
            'address' => fake()->address(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->optional()->sentences(3, true),
            'price' => fake()->randomFloat(2, 0, 999999999999),
            'stock' => fake()->numberBetween(0, 4294967295),
            'on_sale' => fake()->boolean(),
            'features' => fake()->words(),
            'published_at' => fake()->dateTime()?->format('Y-m-d H:i'),
            'expire_at' => fake()->dateTime()?->format('Y-m-d H:i'),
            'released_on' => fake()->date(),
            'sale_time' => fake()->time(),
            'status' => fake()->randomElement(['draft', 'published']),
            'manufactured_year' => fake()->year(2100),
        ];
    }

    public function withCategory(): ProductFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'category_id' => fake()->passThrough(random_id_or_generate(\App\Models\Category::class, 'id', generate: true)),
            ];
        });
    }
}
