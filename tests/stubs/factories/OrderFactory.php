<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_no' => $this->faker->unique()->passThrough(ucfirst($this->faker->text(255))),
        ];
    }

    public function withCategory(): OrderFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'category_id' => $this->faker->passThrough(random_id_or_generate(\App\Models\Category::class, 'id')),
            ];
        });
    }

    public function withProduct(): OrderFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'product_slug' => $this->faker->passThrough(random_id_or_generate(\App\Models\Product::class, 'slug')),
            ];
        });
    }
}