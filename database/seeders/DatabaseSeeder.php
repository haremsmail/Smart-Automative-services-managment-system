<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $branch = Branch::firstOrCreate(
            ['code' => 'SUL-01'],
            ['name' => 'لقی سلێمانی', 'is_active' => true]
        );

        // پاسوۆردی بەڕێوەبەر پێویستە لە .env دابنرێت. بێ پاسوۆرد seeder ڕانابێت.
        $password = env('ADMIN_PASSWORD');
        if (! $password) {
            throw new \RuntimeException('ADMIN_PASSWORD لە فایلی .env دابنێ پێش seed کردن.');
        }

        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@automaster.test')],
            [
                'name' => 'بەڕێوەبەری سەرەکی',
                'password' => $password,
                'branch_id' => $branch->id,
                'role_id' => Role::where('name', 'admin')->value('id'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
