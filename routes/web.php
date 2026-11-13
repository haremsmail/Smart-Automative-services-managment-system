<?php

use App\Livewire\Audit\Index as AuditIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Branches\Index as BranchesIndex;
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
});
