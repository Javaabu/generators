---
title: Introduction
sidebar_position: 1.0
---

# Generators

[Generators](https://github.com/Javaabu/generators) provide Laravel generators on steroids based on database schemas.
For example, if you have a `products` table with the following schema:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('address');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->unsignedDecimal('price', 14, 2);
    $table->unsignedInteger('stock');
    $table->boolean('on_sale')->default(false);
    $table->json('features');
    $table->dateTime('published_at');
    $table->timestamp('expire_at');
    $table->date('released_on');
    $table->time('sale_time');
    $table->enum('status', ['draft', 'published']);
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->year('manufactured_year');
    $table->timestamps();
    $table->softDeletes();
});
```

and you run the artisan command `php artisan generate:factory products --create` it will output the code for a `ProductFactory` like below:

```php
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

```

