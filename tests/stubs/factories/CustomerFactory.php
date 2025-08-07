<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends \Javaabu\Auth\Factories\UserFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $definitions = parent::definition();

        $definitions['password'] = 'Customer@123456';

        $definitions += [
            'designation' => fake()->unique()->text(fake()->numberBetween(5, 255)),
            'address' => fake()->address(),
            'on_sale' => fake()->boolean(),
            'expire_at' => fake()->dateTime()?->format('Y-m-d H:i'),
        ];

        return $definitions;
    }

    public function withCategory(): CustomerFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'category_id' => fake()->passThrough(random_id_or_generate(\App\Models\Category::class, 'id', generate: true)),
            ];
        });
    }
}
