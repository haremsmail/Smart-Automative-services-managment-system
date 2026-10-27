<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\ServiceBay;
use App\Models\ServiceCatalog;
use App\Models\PartCategory;
use App\Models\Part;
use App\Models\StockLevel;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\JobCard;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Branch
        $branch = Branch::updateOrCreate(
            ['code' => 'HQ-001'],
            [
                'name'      => 'AutoMaster Headquarters',
                'phone'     => '+966-11-1234567',
                'email'     => 'hq@automaster.com',
                'address'   => '123 King Fahd Road, Riyadh, Saudi Arabia',
                'is_active' => true,
            ]
        );

        // 2. Roles
        $roles = [
            ['name' => 'admin',      'display_name' => 'Administrator',      'description' => 'Full system access'],
            ['name' => 'advisor',    'display_name' => 'Service Advisor',     'description' => 'Manages customer interactions and work orders'],
            ['name' => 'technician', 'display_name' => 'Technician',          'description' => 'Performs vehicle repairs and maintenance'],
            ['name' => 'cashier',    'display_name' => 'Cashier',             'description' => 'Handles payments and invoicing'],
            ['name' => 'customer',   'display_name' => 'Customer',            'description' => 'Customer portal access'],
        ];

        $roleMap = [];
        foreach ($roles as $roleData) {
            $roleMap[$roleData['name']] = Role::updateOrCreate(
                ['name' => $roleData['name']],
                ['display_name' => $roleData['display_name'], 'description' => $roleData['description']]
            );
        }

        // 3. Permissions per module
        $modules = [
            'users'       => ['view', 'create', 'edit', 'delete'],
            'customers'   => ['view', 'create', 'edit', 'delete'],
            'vehicles'    => ['view', 'create', 'edit', 'delete'],
            'work_orders' => ['view', 'create', 'edit', 'delete', 'close'],
            'inventory'   => ['view', 'create', 'edit', 'delete'],
            'invoices'    => ['view', 'create', 'edit', 'void'],
            'reports'     => ['view', 'export'],
            'settings'    => ['view', 'edit'],
        ];

        $permMap = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                $permMap[$name] = Permission::updateOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => ucfirst($action) . ' ' . ucwords(str_replace('_', ' ', $module)),
                        'module'       => $module,
                        'description'  => "Can {$action} {$module}",
                    ]
                );
            }
        }

        // 4. Role-Permission links
        // Admin gets everything
        $roleMap['admin']->permissions()->syncWithoutDetaching(
            collect($permMap)->pluck('id')->toArray()
        );

        // Advisor: customers, vehicles, work_orders (view/create/edit), invoices view
        $advisorPerms = collect($permMap)->filter(fn ($p, $k) =>
            str_starts_with($k, 'customers.') ||
            str_starts_with($k, 'vehicles.') ||
            in_array($k, ['work_orders.view', 'work_orders.create', 'work_orders.edit', 'invoices.view'])
        )->pluck('id')->toArray();
        $roleMap['advisor']->permissions()->syncWithoutDetaching($advisorPerms);

        // Technician: work_orders view/edit, vehicles view
        $techPerms = collect($permMap)->filter(fn ($p, $k) =>
            in_array($k, ['work_orders.view', 'work_orders.edit', 'vehicles.view'])
        )->pluck('id')->toArray();
        $roleMap['technician']->permissions()->syncWithoutDetaching($techPerms);

        // Cashier: invoices all, payments
        $cashierPerms = collect($permMap)->filter(fn ($p, $k) =>
            str_starts_with($k, 'invoices.')
        )->pluck('id')->toArray();
        $roleMap['cashier']->permissions()->syncWithoutDetaching($cashierPerms);

        // Customer: vehicles view, invoices view
        $customerPerms = collect($permMap)->filter(fn ($p, $k) =>
            in_array($k, ['vehicles.view', 'invoices.view'])
        )->pluck('id')->toArray();
        $roleMap['customer']->permissions()->syncWithoutDetaching($customerPerms);

        // 5. One user per role
        $users = [
            'admin' => [
                'name'  => 'Ahmed Al-Admin',
                'email' => 'admin@automaster.com',
            ],
            'advisor' => [
                'name'  => 'Khalid Al-Advisor',
                'email' => 'advisor@automaster.com',
            ],
            'technician' => [
                'name'  => 'Mohammed Al-Tech',
                'email' => 'tech@automaster.com',
            ],
            'cashier' => [
                'name'  => 'Sara Al-Cashier',
                'email' => 'cashier@automaster.com',
            ],
            'customer' => [
                'name'  => 'Ali Al-Customer',
                'email' => 'customer@automaster.com',
            ],
        ];

        $userMap = [];
        foreach ($users as $roleName => $userData) {
            $userMap[$roleName] = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name'              => $userData['name'],
                    'password'          => Hash::make('password'),
                    'branch_id'         => $branch->id,
                    'role_id'           => $roleMap[$roleName]->id,
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );
        }

        // 6. Warehouse
        $warehouse = Warehouse::updateOrCreate(
            ['code' => 'WH-MAIN'],
            [
                'branch_id' => $branch->id,
                'name'      => 'Main Parts Warehouse',
                'location'  => 'Building A, Ground Floor',
                'is_active' => true,
            ]
        );

        // 7. Service Bay
        $bay = ServiceBay::updateOrCreate(
            ['name' => 'Bay 1', 'branch_id' => $branch->id],
            [
                'bay_type' => 'general',
                'status'   => 'available',
            ]
        );

        // 8. Service Catalog
        $services = [
            ['category' => 'Oil Service',    'name' => 'Engine Oil Change',          'standard_hours' => 0.50, 'base_price' => 150.00],
            ['category' => 'Brakes',         'name' => 'Front Brake Pad Replacement', 'standard_hours' => 1.50, 'base_price' => 350.00],
            ['category' => 'Tyres',          'name' => 'Tyre Rotation',              'standard_hours' => 0.75, 'base_price' => 100.00],
            ['category' => 'Electrical',     'name' => 'Battery Replacement',        'standard_hours' => 0.50, 'base_price' => 200.00],
            ['category' => 'Diagnostics',    'name' => 'Full Vehicle Scan',          'standard_hours' => 1.00, 'base_price' => 120.00],
        ];

        foreach ($services as $svc) {
            ServiceCatalog::updateOrCreate(
                ['name' => $svc['name']],
                array_merge($svc, ['is_active' => true])
            );
        }

        // 9. Part Categories
        $catEngine = PartCategory::updateOrCreate(
            ['name' => 'Engine Parts', 'parent_id' => null],
            ['slug' => 'engine-parts']
        );
        $catFilters = PartCategory::updateOrCreate(
            ['name' => 'Filters', 'parent_id' => $catEngine->id],
            ['slug' => 'filters']
        );
        $catBrakes = PartCategory::updateOrCreate(
            ['name' => 'Brakes', 'parent_id' => null],
            ['slug' => 'brakes']
        );

        // 10. Parts with stock_levels
        $parts = [
            [
                'category_id'     => $catFilters->id,
                'sku'             => 'OIL-FILT-001',
                'name'            => 'Engine Oil Filter (Standard)',
                'unit'            => 'piece',
                'cost_price'      => 15.00,
                'selling_price'   => 35.00,
                'min_stock_level' => 10,
                'reorder_quantity' => 50,
                'quantity'        => 120,
            ],
            [
                'category_id'     => $catBrakes->id,
                'sku'             => 'BRK-PAD-F001',
                'name'            => 'Front Brake Pads (Ceramic)',
                'unit'            => 'set',
                'cost_price'      => 80.00,
                'selling_price'   => 180.00,
                'min_stock_level' => 5,
                'reorder_quantity' => 20,
                'quantity'        => 30,
            ],
            [
                'category_id'     => $catEngine->id,
                'sku'             => 'SPARK-PLG-001',
                'name'            => 'Spark Plug (Iridium)',
                'unit'            => 'piece',
                'cost_price'      => 25.00,
                'selling_price'   => 55.00,
                'min_stock_level' => 20,
                'reorder_quantity' => 100,
                'quantity'        => 200,
            ],
        ];

        foreach ($parts as $partData) {
            $stockQty = $partData['quantity'];
            unset($partData['quantity']);

            $part = Part::updateOrCreate(
                ['sku' => $partData['sku']],
                array_merge($partData, ['is_active' => true])
            );

            StockLevel::updateOrCreate(
                ['part_id' => $part->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => $stockQty]
            );
        }

        // 11. Customer with Vehicle
        $customer = Customer::updateOrCreate(
            ['phone' => '+966-50-1234567'],
            [
                'user_id'       => $userMap['customer']->id,
                'customer_type' => 'retail',
                'full_name'     => 'Abdullah Al-Fahad',
                'email'         => 'abdullah@example.com',
                'address'       => '456 Olaya Street, Riyadh',
                'credit_limit'  => 5000.00,
            ]
        );

        $vehicle = Vehicle::updateOrCreate(
            ['plate_number' => 'ABC-1234'],
            [
                'customer_id'     => $customer->id,
                'vin'             => 'WBAJB0C51BC613742',
                'make'            => 'Toyota',
                'model'           => 'Camry',
                'year'            => 2022,
                'color'           => 'White',
                'engine_type'     => '2.5L 4-Cylinder',
                'transmission'    => 'Automatic',
                'current_mileage' => 45000,
            ]
        );

        // 12. Work Order with Job Card
        $workOrder = WorkOrder::updateOrCreate(
            ['wo_number' => 'WO-2024-0001'],
            [
                'branch_id'          => $branch->id,
                'customer_id'        => $customer->id,
                'vehicle_id'         => $vehicle->id,
                'bay_id'             => $bay->id,
                'advisor_id'         => $userMap['advisor']->id,
                'mileage_in'         => 45000,
                'status'             => 'open',
                'customer_complaint' => 'Oil change required and brake inspection requested.',
                'diagnosis'          => 'Engine oil due for replacement. Front brake pads at 30% remaining.',
                'promised_at'        => now()->addHours(4),
            ]
        );

        $oilChangeService = ServiceCatalog::where('name', 'Engine Oil Change')->first();
        JobCard::updateOrCreate(
            ['work_order_id' => $workOrder->id, 'title' => 'Engine Oil Change'],
            [
                'service_id'      => $oilChangeService?->id,
                'technician_id'   => $userMap['technician']->id,
                'instructions'    => 'Drain old oil, replace filter, refill with 5W-30 synthetic oil.',
                'flat_rate_hours' => 0.50,
                'status'          => 'in_progress',
            ]
        );
    }
}
