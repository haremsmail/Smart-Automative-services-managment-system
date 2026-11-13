<?php

namespace Tests\Feature\Module1;

use App\Livewire\Audit\Index as AuditIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Branches\Index as BranchesIndex;
use App\Livewire\Roles\Form as RoleForm;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Users\Form as UserForm;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Users\Show as UserShow;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Module1LivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /** کارمەندێکی تاقیکاری دروست دەکات، بە ڕۆڵ و دەسەڵاتی تاکەکەسی */
    private function makeUser(?string $roleName = null, array $extra = [], string $status = 'active'): User
    {
        $user = User::create([
            'name' => 'کارمەند '.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'status' => $status,
        ]);

        // role_id لە fillable نییە، بۆیە forceFill
        $user->forceFill([
            'role_id' => $roleName ? Role::where('name', $roleName)->value('id') : null,
        ])->save();

        foreach ($extra as $permissionName) {
            $user->extraPermissions()->attach(Permission::where('name', $permissionName)->value('id'));
        }

        return $user;
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get(route('users.index'))->assertRedirect('/login');
    }

    public function test_user_can_login_with_correct_password(): void
    {
        $user = $this->makeUser('admin');

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', ['action' => 'login.success', 'user_id' => $user->id]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->makeUser('admin', [], 'inactive');

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_without_users_view_gets_403(): void
    {
        $technician = $this->makeUser('technician');

        $this->actingAs($technician)->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_creates_user_and_audit_is_written(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);

        Livewire::test(UserForm::class)
            ->set('name', 'عەلی')
            ->set('email', 'ali@example.test')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->set('role_id', (string) Role::where('name', 'technician')->value('id'))
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $created = User::where('email', 'ali@example.test')->firstOrFail();

        $this->assertSame('technician', $created->role->name);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.created',
            'model_type' => 'User',
            'model_id' => $created->id,
        ]);
    }

    public function test_user_without_assign_role_cannot_set_role(): void
    {
        // ڕاوێژکار users.create هەیە بەڵام users.assign_role نییە
        $advisor = $this->makeUser('advisor', ['users.create']);
        $this->actingAs($advisor);

        Livewire::test(UserForm::class)
            ->set('name', 'نموونە')
            ->set('email', 'x@example.test')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->set('role_id', (string) Role::where('name', 'admin')->value('id'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(User::where('email', 'x@example.test')->value('role_id'));
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);

        Livewire::test(UsersIndex::class)->call('toggleStatus', $admin->id);

        $this->assertSame('active', $admin->fresh()->status);
    }

    public function test_admin_grants_extra_permission_from_user_page(): void
    {
        $admin = $this->makeUser('admin');
        $technician = $this->makeUser('technician');
        $this->actingAs($admin);

        Livewire::test(UserShow::class, ['user' => $technician])
            ->set('grant_permission_id', (string) Permission::where('name', 'payments.create')->value('id'))
            ->call('grantSelected')
            ->assertHasNoErrors();

        $this->assertTrue($technician->fresh()->hasPermission('payments.create'));
    }

    public function test_admin_creates_custom_role_with_permissions(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);

        Livewire::test(RoleForm::class)
            ->set('name', 'receptionist')
            ->set('display_name', 'ڕیسێپشن')
            ->set('permission_ids', [(string) Permission::where('name', 'audit.view')->value('id')])
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::where('name', 'receptionist')->firstOrFail();

        $this->assertFalse($role->is_system);
        $this->assertTrue($role->permissions->contains('name', 'audit.view'));
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);
        $advisorId = Role::where('name', 'advisor')->value('id');

        Livewire::test(RolesIndex::class)->call('delete', $advisorId);

        $this->assertDatabaseHas('roles', ['id' => $advisorId]);
    }

    public function test_branch_code_must_be_unique(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);
        Branch::create(['name' => 'یەکەم', 'code' => 'DUP-1', 'is_active' => true]);

        Livewire::test(BranchesIndex::class)
            ->call('create')
            ->set('name', 'دووەم')
            ->set('code', 'DUP-1')
            ->call('save')
            ->assertHasErrors(['code']);
    }

    public function test_audit_page_lists_login_success(): void
    {
        $admin = $this->makeUser('admin');
        AuditLog::create(['user_id' => $admin->id, 'action' => 'login.success', 'created_at' => now()]);
        $this->actingAs($admin);

        Livewire::test(AuditIndex::class)->assertSee('login.success');
    }

    public function test_audit_log_cannot_be_updated(): void
    {
        $log = AuditLog::create(['action' => 'test.action', 'created_at' => now()]);

        $this->expectException(\LogicException::class);
        $log->update(['action' => 'changed']);
    }
}
