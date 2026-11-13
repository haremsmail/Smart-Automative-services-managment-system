<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // هەر دەسەڵاتێک لە config/permissions.php دەبێتە Gate
        foreach (config('permissions.groups') as $permissions) {
            foreach (array_keys($permissions) as $name) {
                Gate::define($name, fn (User $user) => $user->hasPermission($name));
            }
        }
    }
}
