<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * کێشە ١: کاتی سیستەم دەبێت کاتی کوردستان بێت (UTC+3).
 * کێشە ٢: پاش چوونەژوورەوە هەر کەسە دەچێتە پەڕەیەک کە دەسەڵاتی هەیە (نەک 403).
 */
class HomeAndTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function user(?string $role): User
    {
        $user = User::create(['name' => 'تاقی', 'email' => fake()->unique()->safeEmail(), 'password' => 'password123', 'status' => 'active']);
        $user->forceFill(['role_id' => $role ? Role::where('name', $role)->value('id') : null])->save();

        return $user;
    }

    public function test_timezone_is_kurdistan(): void
    {
        $this->assertSame('Asia/Baghdad', config('app.timezone'));
        $this->assertSame(3 * 3600, now()->getOffset());
    }

    public function test_each_role_lands_on_a_page_it_can_open(): void
    {
        foreach (['admin' => 'appointments.board', 'advisor' => 'appointments.board', 'technician' => 'appointments.board', 'cashier' => 'appointments.board'] as $role => $route) {
            $user = $this->user($role);

            $this->actingAs($user)->get('/')->assertRedirect(route($route));
            $this->actingAs($user)->get(route($route))->assertOk(); // نەک 403
        }
    }

    public function test_user_without_any_permission_sees_friendly_page(): void
    {
        $this->actingAs($this->user(null))->get('/')->assertOk()->assertSee('هێشتا هیچ دەسەڵاتێکت پێ نەدراوە');
    }

    public function test_technician_login_does_not_end_in_403(): void
    {
        $tech = $this->user('technician');

        Livewire::test(Login::class)
            ->set('email', $tech->email)
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('home'));

        $this->get(route('home'))->assertRedirect(route('appointments.board'));
        $this->get(route('appointments.board'))->assertOk();
    }
}
