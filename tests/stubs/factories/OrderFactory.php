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
            'order_no' => fake()->regexify('[a-z]{4}'),
            'status' => fake()->randomElement(array_column(\Javaabu\Generators\Tests\TestSupport\Enums\OrderStatuses::cases(), 'value')),
        ];
    }

    public function withCategory(): OrderFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'category_id' => fake()->passThrough(random_id_or_generate(\App\Models\Category::class, 'id', generate: true)),
            ];
        });
    }

    public function withProductSlug(): OrderFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'product_slug' => fake()->passThrough(random_id_or_generate(\App\Models\Product::class, 'slug', generate: true)),
            ];
        });
    }

    public function withRequiredRelations(): OrderFactory
    {
        return $this->withCategory()
                    ->withProduct();
    }
}
