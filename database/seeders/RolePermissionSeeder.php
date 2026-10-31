<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * دەسەڵاتەکان و ڕۆڵە سەرەکییەکان دروست دەکات.
     * دەکرێت چەند جار بکرێت بێ ئەوەی داتا دووبارە بێت.
     */
    public function run(): void
    {
        $allNames = [];

        foreach (config('permissions.groups') as $module => $permissions) {
            foreach ($permissions as $name => $label) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    ['display_name' => $label, 'module' => $module, 'description' => $label]
                );
                $allNames[] = $name;
            }
        }

        $roles = [
            'admin' => ['display_name' => 'بەڕێوەبەر', 'permissions' => $allNames],
            'advisor' => ['display_name' => 'ڕاوێژکار', 'permissions' => ['users.view']],
            'technician' => ['display_name' => 'وەستا', 'permissions' => []],
            'cashier' => ['display_name' => 'کاشێر', 'permissions' => ['payments.create', 'pos.open_session']],
            'customer' => ['display_name' => 'کڕیار', 'permissions' => []],
        ];

        foreach ($roles as $name => $config) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['display_name' => $config['display_name'], 'is_system' => true]
            );

            $ids = Permission::whereIn('name', $config['permissions'])->pluck('id');
            $role->permissions()->sync($ids);
        }
    }
}
