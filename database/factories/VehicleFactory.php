<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $cars = [
            ['Toyota', 'Land Cruiser'], ['Toyota', 'Camry'], ['Hyundai', 'Tucson'],
            ['Kia', 'Sportage'], ['Nissan', 'Patrol'], ['Chevrolet', 'Tahoe'],
        ];
        [$make, $model] = fake()->randomElement($cars);

        return [
            'customer_id' => Customer::factory(),
            'plate_number' => fake()->numberBetween(11, 99).' '.fake()->unique()->numerify('#####'),
            // VIN ی دروست: ١٧ پیت بەبێ I/O/Q
            'vin' => strtoupper(fake()->unique()->regexify('[A-HJ-NPR-Z0-9]{17}')),
            'make' => $make,
            'model' => $model,
            'year' => fake()->numberBetween(2008, (int) date('Y')),
            'color' => fake()->randomElement(['سپی', 'ڕەش', 'زیوی', 'خۆڵەمێشی']),
            'engine_type' => 'petrol',
            'transmission' => 'automatic',
            'current_mileage' => fake()->numberBetween(5000, 250000),
        ];
    }
}
