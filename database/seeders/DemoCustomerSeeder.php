<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

/**
 * داتای نموونە بۆ تاقیکردنەوە. تەنها لە ژینگەی local ڕادەکرێت (بڕوانە DatabaseSeeder).
 */
class DemoCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'customer' => ['customer_type' => 'retail', 'full_name' => 'ئاراس محەمەد', 'phone' => '07501112233', 'address' => 'سلێمانی، سالم'],
                'vehicles' => [
                    ['plate_number' => '22 أ 45871', 'vin' => 'JTMHV05J604123456', 'make' => 'Toyota', 'model' => 'Land Cruiser', 'year' => 2019, 'color' => 'سپی', 'engine_type' => 'petrol', 'transmission' => 'automatic', 'current_mileage' => 98500],
                ],
            ],
            [
                'customer' => ['customer_type' => 'wholesale', 'full_name' => 'کۆمپانیای ڕێبوار بۆ گواستنەوە', 'phone' => '07704445566', 'tax_number' => 'TX-10442', 'credit_limit' => 10000000, 'address' => 'سلێمانی، شەقامی سالم'],
                'vehicles' => [
                    ['plate_number' => '22 ب 10021', 'make' => 'Hyundai', 'model' => 'H-1', 'year' => 2017, 'color' => 'زیوی', 'engine_type' => 'diesel', 'transmission' => 'manual', 'current_mileage' => 241000],
                    ['plate_number' => '22 ب 10022', 'make' => 'Hyundai', 'model' => 'H-1', 'year' => 2018, 'color' => 'زیوی', 'engine_type' => 'diesel', 'transmission' => 'manual', 'current_mileage' => 198300],
                ],
            ],
            [
                'customer' => ['customer_type' => 'retail', 'full_name' => 'شیلان ئەحمەد', 'phone' => '07517770899', 'whatsapp' => '07517770899'],
                'vehicles' => [
                    ['plate_number' => '22 ج 77310', 'vin' => 'KNDPM3AC5K7512345', 'make' => 'Kia', 'model' => 'Sportage', 'year' => 2021, 'color' => 'سوور', 'engine_type' => 'petrol', 'transmission' => 'automatic', 'current_mileage' => 42000],
                ],
            ],
        ];

        foreach ($customers as $row) {
            $customer = Customer::firstOrCreate(['phone' => $row['customer']['phone']], $row['customer']);

            foreach ($row['vehicles'] as $vehicle) {
                $customer->vehicles()->firstOrCreate(['plate_number' => $vehicle['plate_number']], $vehicle);
            }
        }
    }
}
