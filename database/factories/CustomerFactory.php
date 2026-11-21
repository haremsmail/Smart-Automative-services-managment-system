<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'customer_type' => 'retail',
            'full_name' => fake()->name(),
            'phone' => '07'.fake()->randomElement(['50', '51', '70', '71', '72', '73', '74', '75']).fake()->unique()->numerify('#######'),
            'credit_limit' => 0,
        ];
    }

    public function company(): static
    {
        return $this->state(fn () => [
            'customer_type' => 'wholesale',
            'full_name' => fake()->company(),
            'tax_number' => fake()->numerify('TX-#####'),
            'credit_limit' => 5000000,
        ]);
    }
}
