<?php

use App\Livewire\Audit\Index as AuditIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Branches\Index as BranchesIndex;
use App\Livewire\Customers\Form as CustomerForm;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Customers\Show as CustomerShow;
use App\Livewire\Vehicles\Form as VehicleForm;
use App\Livewire\Vehicles\Index as VehiclesIndex;
use App\Livewire\Vehicles\Show as VehicleShow;
use App\Livewire\Roles\Form as RoleForm;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Users\Form as UserForm;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Users\Show as UserShow;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserIsActive;

Route::get('/', fn () => redirect()->route('users.index'));

// پەڕەی چوونەژوورەوە (Livewire)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function (AuditService $audit) {
  $audit->record(Auth::user(), 'logout');

    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

Route::middleware('auth')->group(function () {
    // ئاگاداری: /users/create دەبێت پێش /users/{user} بێت
    Route::get('/users', UsersIndex::class)->name('users.index')->middleware('can:users.view');
    Route::get('/users/create', UserForm::class)->name('users.create')->middleware('can:users.create');
    Route::get('/users/{user}', UserShow::class)->name('users.show')->middleware('can:users.view');
    Route::get('/users/{user}/edit', UserForm::class)->name('users.edit')->middleware('can:users.update');

    Route::get('/roles', RolesIndex::class)->name('roles.index')->middleware('can:roles.manage');
    Route::get('/roles/create', RoleForm::class)->name('roles.create')->middleware('can:roles.manage');
    Route::get('/roles/{role}/edit', RoleForm::class)->name('roles.edit')->middleware('can:roles.manage');

    Route::get('/branches', BranchesIndex::class)->name('branches.index')->middleware('can:branches.manage');

    Route::get('/audit', AuditIndex::class)->name('audit.index')->middleware('can:audit.view');

    // مۆدیوڵی ٢: کڕیار و ئۆتۆمبێل
    // show ـەکان ژمارە وەردەگرن (نەک model) تا تۆماری ئەرشیفکراویش ببینرێت
    Route::get('/customers', CustomersIndex::class)->name('customers.index')->middleware('can:customers.view');
    Route::get('/customers/create', CustomerForm::class)->name('customers.create')->middleware('can:customers.create');
    Route::get('/customers/{customer}', CustomerShow::class)->name('customers.show')->whereNumber('customer')->middleware('can:customers.view');
    Route::get('/customers/{customer}/edit', CustomerForm::class)->name('customers.edit')->middleware('can:customers.update');

    Route::get('/vehicles', VehiclesIndex::class)->name('vehicles.index')->middleware('can:vehicles.view');
    Route::get('/vehicles/create', VehicleForm::class)->name('vehicles.create')->middleware('can:vehicles.create');
    Route::get('/vehicles/{vehicle}', VehicleShow::class)->name('vehicles.show')->whereNumber('vehicle')->middleware('can:vehicles.view');
    Route::get('/vehicles/{vehicle}/edit', VehicleForm::class)->name('vehicles.edit')->middleware('can:vehicles.update');
});
