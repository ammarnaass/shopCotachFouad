<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'description' => fake()->sentence(),
            'short_description' => null,
            'price' => fake()->randomFloat(2, 100, 50000),
            'sale_price' => null,
            'sku' => 'SKU-'.strtoupper(Str::random(8)),
            'stock' => fake()->numberBetween(0, 100),
            'type' => 'simple',
            'status' => 'active',
            'featured' => false,
        ];
    }

    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_price' => isset($attributes['price'])
                ? round((float) $attributes['price'] * 0.8, 2)
                : fake()->randomFloat(2, 50, 25000),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
