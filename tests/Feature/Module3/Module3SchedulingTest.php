<?php

namespace Tests\Feature\Module3;

use App\Livewire\Appointments\Form as AppointmentForm;
use App\Livewire\Appointments\Show as AppointmentShow;
use App\Livewire\Booking\Create as BookingCreate;
use App\Livewire\Booking\Manage as BookingManage;
use App\Livewire\Scheduling\Technicians;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\ServiceBay;
use App\Models\ServiceCatalog;
use App\Models\Shift;
use App\Models\TechnicianSchedule;
use App\Models\TechnicianSkill;
use App\Models\TechnicianTimeOff;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AppointmentService;
use App\Services\SchedulingService;
use Database\Seeders\RolePermissionSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * کاتی تاقیکردنەوە: دووشەممە 2026-11-16 کاتژمێر 07:00.
 * کاروان (بریک ⭐⭐⭐⭐⭐) و ئاری (بریک ⭐⭐) شەفتی ڕۆژ (08–16)، هێمن شەفتی شەو (20–04).
 */
class Module3SchedulingTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private ServiceBay $lift;
    private ServiceBay $alignmentBay;
    private Shift $day;
    private Shift $night;
    private User $karwan;
    private User $ari;
    private User $hemn;
    private ServiceCatalog $oil;
    private ServiceCatalog $brakes;
    private ServiceCatalog $alignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->travelTo(Carbon::parse('2026-11-16 07:00:00')); // دووشەممە

        $this->branch = Branch::create(['name' => 'لقی تاقی', 'code' => 'T-1', 'is_active' => true]);
        $this->lift = ServiceBay::create(['branch_id' => $this->branch->id, 'name' => 'لیفتی ١', 'bay_type' => 'general', 'status' => 'available']);
        $this->alignmentBay = ServiceBay::create(['branch_id' => $this->branch->id, 'name' => 'Alignment', 'bay_type' => 'alignment', 'status' => 'available']);

        $this->day = Shift::create(['name' => 'ڕۆژ', 'start_time' => '08:00', 'end_time' => '16:00', 'color' => 'amber']);
        $this->night = Shift::create(['name' => 'شەو', 'start_time' => '20:00', 'end_time' => '04:00', 'color' => 'blue']);

        $this->karwan = $this->technician('کاروان', ['brakes' => 5, 'maintenance' => 4], $this->day);
        $this->ari = $this->technician('ئاری', ['brakes' => 2, 'alignment' => 5], $this->day);
        $this->hemn = $this->technician('هێمن', ['engine' => 5], $this->night);

        $this->oil = ServiceCatalog::create(['category' => 'خولی', 'skill' => 'maintenance', 'required_bay_type' => 'general', 'name' => 'گۆڕینی ڕۆن', 'standard_hours' => 0.5, 'base_price' => 25000]);
        $this->brakes = ServiceCatalog::create(['category' => 'بریک', 'skill' => 'brakes', 'required_bay_type' => 'general', 'name' => 'پاتی بریک', 'standard_hours' => 1.5, 'base_price' => 40000]);
        $this->alignment = ServiceCatalog::create(['category' => 'تایە', 'skill' => 'alignment', 'required_bay_type' => 'alignment', 'name' => 'Alignment', 'standard_hours' => 1, 'base_price' => 30000]);
    }

    private function technician(string $name, array $skills, Shift $shift): User
    {
        $user = User::create(['name' => $name, 'email' => fake()->unique()->safeEmail(), 'password' => 'password123', 'status' => 'active', 'branch_id' => $this->branch->id]);
        $user->forceFill(['role_id' => Role::where('name', 'technician')->value('id')])->save();

        foreach ($skills as $skill => $level) {
            TechnicianSkill::create(['user_id' => $user->id, 'skill' => $skill, 'level' => $level]);
        }
        foreach ([6, 0, 1, 2, 3, 4] as $weekday) { // شەممە تا پێنجشەممە
            TechnicianSchedule::create(['user_id' => $user->id, 'weekday' => $weekday, 'shift_id' => $shift->id]);
        }

        return $user;
    }

    private function staff(string $role = 'admin'): User
    {
        $user = User::create(['name' => 'کارمەند', 'email' => fake()->unique()->safeEmail(), 'password' => 'password123', 'status' => 'active', 'branch_id' => $this->branch->id]);
        $user->forceFill(['role_id' => Role::where('name', $role)->value('id')])->save();

        return $user;
    }

    private function vehicle(): Vehicle
    {
        return Vehicle::factory()->for(Customer::factory())->create();
    }

    private function book(User $actor, Vehicle $vehicle, array $services, string $at, $tech = null, $bay = null): Appointment
    {
        return app(AppointmentService::class)->create($actor, [
            'customer_id' => $vehicle->customer_id, 'vehicle_id' => $vehicle->id, 'branch_id' => $this->branch->id,
            'service_ids' => collect($services)->pluck('id')->all(), 'scheduled_at' => $at,
            'technician_id' => $tech, 'bay_id' => $bay, 'source' => 'phone',
        ]);
    }

    // ================================================================ پێداویستی و شەفت

    public function test_duration_and_bay_type_come_from_services(): void
    {
        $s = app(SchedulingService::class);

        $req = $s->requirements(collect([$this->oil, $this->brakes]));
        $this->assertSame(120, $req['minutes']); // 30 + 90
        $this->assertSame('general', $req['bay_type']);
        $this->assertEqualsCanonicalizing(['maintenance', 'brakes'], $req['skills']);

        $this->assertSame('alignment', $s->requirements(collect([$this->oil, $this->alignment]))['bay_type']);
    }

    public function test_services_needing_two_special_bays_must_be_split(): void
    {
        $diag = ServiceCatalog::create(['category' => 'x', 'required_bay_type' => 'diagnostics', 'name' => 'OBD', 'standard_hours' => 0.5, 'base_price' => 0]);

        $this->expectException(DomainException::class);
        app(SchedulingService::class)->requirements(collect([$this->alignment, $diag]));
    }

    public function test_night_shift_crosses_midnight(): void
    {
        $s = app(SchedulingService::class);
        $hemn = $s->technicians($this->branch->id)->firstWhere('id', $this->hemn->id);

        $this->assertTrue($s->onShift($hemn, Carbon::parse('2026-11-16 23:00'), Carbon::parse('2026-11-17 00:00')));
        $this->assertTrue($s->onShift($hemn, Carbon::parse('2026-11-17 02:00'), Carbon::parse('2026-11-17 03:30'))); // لە شەفتی دووشەممە شەوەوە
        $this->assertFalse($s->onShift($hemn, Carbon::parse('2026-11-16 10:00'), Carbon::parse('2026-11-16 11:00')));
        $this->assertFalse($s->onShift($hemn, Carbon::parse('2026-11-17 03:30'), Carbon::parse('2026-11-17 04:30'))); // کۆتایی شەفت تێدەپەڕێت
    }

    public function test_time_off_removes_technician_for_that_day(): void
    {
        TechnicianTimeOff::create(['user_id' => $this->karwan->id, 'date' => '2026-11-16', 'shift_id' => null, 'reason' => 'نەخۆشی']);

        $s = app(SchedulingService::class);
        $karwan = $s->technicians($this->branch->id)->firstWhere('id', $this->karwan->id);

        $this->assertFalse($s->onShift($karwan, Carbon::parse('2026-11-16 09:00'), Carbon::parse('2026-11-16 10:00')));
        $this->assertTrue($s->onShift($karwan, Carbon::parse('2026-11-17 09:00'), Carbon::parse('2026-11-17 10:00')));
    }

    public function test_available_slots_follow_shifts_day_and_night(): void
    {
        $times = collect(app(SchedulingService::class)->availableSlots($this->branch->id, Carbon::parse('2026-11-16'), 30, 'general'))->pluck('time');

        $this->assertContains('08:00', $times);
        $this->assertContains('15:30', $times);
        $this->assertNotContains('16:00', $times); // شەفتی ڕۆژ تەواو بووە
        $this->assertNotContains('18:00', $times); // کەس لە شەفتدا نییە
        $this->assertContains('22:00', $times);    // هێمن شەفتی شەوە
        $this->assertNotContains('06:30', $times); // ڕابردوو
    }

    public function test_online_slots_respect_online_window(): void
    {
        $times = collect(app(SchedulingService::class)->availableSlots($this->branch->id, Carbon::parse('2026-11-16'), 30, 'general', online: true))->pluck('time');

        $this->assertContains('09:00', $times);
        $this->assertContains('21:00', $times);
        $this->assertNotContains('22:30', $times); // دوای 22:00
    }

    // ================================================================ AI Dispatch

    public function test_ai_dispatch_ranks_by_skill_and_explains_why(): void
    {
        $ranking = app(SchedulingService::class)->rankTechnicians($this->branch->id, Carbon::parse('2026-11-16 09:00'), Carbon::parse('2026-11-16 10:30'), ['brakes']);

        $this->assertSame($this->karwan->id, $ranking[0]['user']->id); // بریک ⭐⭐⭐⭐⭐
        $this->assertSame($this->ari->id, $ranking[1]['user']->id);    // بریک ⭐⭐
        $this->assertGreaterThan($ranking[1]['score'], $ranking[0]['score']);
        $this->assertFalse($ranking[2]['available']); // هێمن شەفتی شەوە
        $this->assertCount(4, $ranking[0]['reasons']);
    }

    public function test_ai_dispatch_balances_workload(): void
    {
        $admin = $this->staff();
        // کاروان ٦ کاتژمێر کاری ئەمڕۆی هەیە
        $this->book($admin, $this->vehicle(), [$this->brakes, $this->brakes, $this->brakes, $this->brakes], '2026-11-16 08:00', $this->karwan->id);

        $ranking = app(SchedulingService::class)->rankTechnicians($this->branch->id, Carbon::parse('2026-11-16 14:30'), Carbon::parse('2026-11-16 15:00'), ['maintenance']);
        $karwan = $ranking->firstWhere('user.id', $this->karwan->id);
        $ari = $ranking->firstWhere('user.id', $this->ari->id);

        $this->assertLessThan(20, $karwan['reasons'][2]['points']);
        $this->assertSame(20, $ari['reasons'][2]['points']);
    }

    // ================================================================ دانانی نۆرە

    public function test_advisor_creates_appointment_with_auto_technician(): void
    {
        $advisor = $this->staff('advisor');
        $vehicle = $this->vehicle();
        $this->actingAs($advisor);

        Livewire::test(AppointmentForm::class)
            ->set('customer_id', (string) $vehicle->customer_id)
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('branch_id', (string) $this->branch->id)
            ->set('service_ids', [(string) $this->brakes->id])
            ->set('date', '2026-11-16')
            ->set('time', '09:00')
            ->set('technician_id', 'auto')
            ->call('save')
            ->assertHasNoErrors();

        $a = Appointment::firstOrFail();
        $this->assertSame('BK-00001', $a->reference);
        $this->assertSame('scheduled', $a->status);
        $this->assertSame($this->karwan->id, $a->technician_id); // AI باشترینی هەڵبژارد
        $this->assertSame($this->lift->id, $a->bay_id);
        $this->assertSame(90, $a->duration_minutes);
        $this->assertSame('2026-11-16 10:30', $a->ends_at->format('Y-m-d H:i'));
        $this->assertDatabaseHas('appointment_services', ['appointment_id' => $a->id, 'price' => 40000]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'appointment.created', 'model_id' => $a->id]);
    }

    public function test_same_bay_cannot_be_double_booked(): void
    {
        $admin = $this->staff();
        $this->book($admin, $this->vehicle(), [$this->brakes], '2026-11-16 09:00');

        $this->expectExceptionMessage('هیچ شوێنێکی کاری گونجاو');
        $this->book($admin, $this->vehicle(), [$this->oil], '2026-11-16 10:00'); // 10:00 لەناو 09:00–10:30
    }

    public function test_same_technician_cannot_be_double_booked(): void
    {
        ServiceBay::create(['branch_id' => $this->branch->id, 'name' => 'لیفتی ٢', 'bay_type' => 'general', 'status' => 'available']);
        $admin = $this->staff();
        $this->book($admin, $this->vehicle(), [$this->brakes], '2026-11-16 09:00', $this->karwan->id);

        $this->expectExceptionMessage('نۆرەیەکی تری هەیە');
        $this->book($admin, $this->vehicle(), [$this->oil], '2026-11-16 09:30', $this->karwan->id);
    }

    public function test_technician_must_be_on_shift(): void
    {
        $this->expectExceptionMessage('لە شەفتدا نییە');
        $this->book($this->staff(), $this->vehicle(), [$this->oil], '2026-11-16 10:00', $this->hemn->id);
    }

    public function test_bay_in_maintenance_is_not_used(): void
    {
        $this->lift->update(['status' => 'maintenance']);

        $this->assertSame([], app(SchedulingService::class)->availableSlots($this->branch->id, Carbon::parse('2026-11-16'), 30, 'general'));
        $this->expectException(DomainException::class);
        $this->book($this->staff(), $this->vehicle(), [$this->oil], '2026-11-16 09:00');
    }

    public function test_appointment_must_be_on_slot_and_not_in_past(): void
    {
        try {
            $this->book($this->staff(), $this->vehicle(), [$this->oil], '2026-11-16 09:10');
            $this->fail('Expected exception');
        } catch (DomainException $e) {
            $this->assertStringContainsString('خانەی', $e->getMessage());
        }

        $this->expectExceptionMessage('ڕابردوو');
        $this->book($this->staff(), $this->vehicle(), [$this->oil], '2026-11-15 09:00');
    }

    // ================================================================ دۆخ و گۆڕین

    public function test_status_flow_is_enforced(): void
    {
        $admin = $this->staff();
        $a = $this->book($admin, $this->vehicle(), [$this->oil], '2026-11-16 09:00');
        $service = app(AppointmentService::class);

        try {
            $service->changeStatus($admin, $a, 'completed');
            $this->fail('scheduled → completed should be blocked');
        } catch (DomainException) {
        }

        try {
            $service->changeStatus($admin, $a, 'no_show');
            $this->fail('no_show before time should be blocked');
        } catch (DomainException) {
        }

        $service->changeStatus($admin, $a, 'arrived');
        $service->changeStatus($admin, $a->fresh(), 'completed');
        $this->assertSame('completed', $a->fresh()->status);
    }

    public function test_reschedule_moves_time_and_frees_old_slot(): void
    {
        $admin = $this->staff();
        $this->actingAs($admin);
        $a = $this->book($admin, $this->vehicle(), [$this->brakes], '2026-11-16 09:00', $this->karwan->id);

        Livewire::test(AppointmentShow::class, ['appointment' => $a])
            ->set('showReschedule', true)
            ->set('new_date', '2026-11-17')
            ->set('new_time', '11:00')
            ->call('reschedule')
            ->assertHasNoErrors();

        $a->refresh();
        $this->assertSame('2026-11-17 11:00', $a->scheduled_at->format('Y-m-d H:i'));
        $this->assertSame($this->karwan->id, $a->technician_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'appointment.rescheduled']);

        // کاتی کۆن ئێستا بەتاڵە
        $this->book($admin, $this->vehicle(), [$this->oil], '2026-11-16 09:00');
        $this->assertSame(2, Appointment::count());
    }

    // ================================================================ نۆرەی ئۆنلاین

    public function test_customer_books_online_and_is_matched_by_phone(): void
    {
        $existing = Customer::factory()->create(['phone' => '07501112233']);

        Livewire::test(BookingCreate::class)
            ->set('branch_id', (string) $this->branch->id)
            ->set('service_ids', [(string) $this->oil->id])
            ->call('next')
            ->set('date', '2026-11-16')
            ->set('time', '10:00')
            ->call('next')
            ->set('full_name', 'ئاراس')
            ->set('phone', '+964 750 111 2233')
            ->set('plate_number', '22 أ 99999')
            ->set('make', 'Toyota')
            ->set('model', 'Camry')
            ->call('book')
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $a = Appointment::firstOrFail();
        $this->assertSame('pending', $a->status);
        $this->assertSame('online', $a->source);
        $this->assertSame($existing->id, $a->customer_id); // کڕیاری دووبارە دروست نەبوو
        $this->assertSame(1, Customer::count());
        $this->assertSame('22 أ 99999', $a->vehicle->plate_number);
        $this->assertNull($a->technician_id);
    }

    public function test_online_booking_outside_window_is_rejected(): void
    {
        $this->expectExceptionMessage('ئۆنلاین');

        app(\App\Services\BookingService::class)->book([
            'branch_id' => $this->branch->id, 'service_ids' => [$this->oil->id], 'scheduled_at' => '2026-11-16 23:00:00',
            'full_name' => 'x', 'phone' => '07700000000', 'plate_number' => '1', 'make' => 'a', 'model' => 'b',
        ]);
    }

    public function test_customer_can_cancel_from_private_link(): void
    {
        $a = $this->book($this->staff(), $this->vehicle(), [$this->oil], '2026-11-16 12:00');

        $this->get(route('booking.manage', $a->public_token))->assertOk()->assertSee($a->reference);

        Livewire::test(BookingManage::class, ['token' => $a->public_token])->call('cancel')->assertHasNoErrors();
        $this->assertSame('cancelled', $a->fresh()->status);
    }

    public function test_advisor_confirms_pending_online_booking(): void
    {
        $advisor = $this->staff('advisor');
        $this->actingAs($advisor);
        $a = app(\App\Services\BookingService::class)->book([
            'branch_id' => $this->branch->id, 'service_ids' => [$this->oil->id], 'scheduled_at' => '2026-11-16 10:00:00',
            'full_name' => 'نوێ', 'phone' => '07709998877', 'plate_number' => '22 ب 1', 'make' => 'Kia', 'model' => 'Rio',
        ]);

        Livewire::test(AppointmentShow::class, ['appointment' => $a])->call('setStatus', 'scheduled');

        $this->assertSame('scheduled', $a->fresh()->status);
        $this->assertNotNull($a->fresh()->confirmed_at);
    }

    // ================================================================ بەڕێوەبەر و دەسەڵات

    public function test_manager_sets_weekly_shift_and_skill(): void
    {
        $this->actingAs($this->staff('admin'));

        Livewire::test(Technicians::class)
            ->call('setSchedule', $this->hemn->id, 5, (string) $this->day->id)  // هەینی: ڕۆژ
            ->call('setSchedule', $this->hemn->id, 1, '')                       // دووشەممە: پشوو
            ->call('setSkill', $this->hemn->id, 'brakes', '3');

        $this->assertDatabaseHas('technician_schedules', ['user_id' => $this->hemn->id, 'weekday' => 5, 'shift_id' => $this->day->id]);
        $this->assertDatabaseMissing('technician_schedules', ['user_id' => $this->hemn->id, 'weekday' => 1]);
        $this->assertDatabaseHas('technician_skills', ['user_id' => $this->hemn->id, 'skill' => 'brakes', 'level' => 3]);
    }

    public function test_only_admin_manages_technicians_services_and_bays(): void
    {
        $this->actingAs($this->staff('advisor'));
        $this->get(route('technicians.index'))->assertForbidden();
        $this->get(route('services.index'))->assertForbidden();
        $this->get(route('bays.index'))->assertForbidden();
        $this->get(route('appointments.board'))->assertOk();

        $this->actingAs($this->karwan);
        $this->get(route('appointments.board'))->assertOk();
        $this->get(route('appointments.create'))->assertForbidden();
    }

    public function test_all_pages_render(): void
    {
        $admin = $this->staff('admin');
        $a = $this->book($admin, $this->vehicle(), [$this->brakes], '2026-11-16 09:00', $this->karwan->id);
        $this->actingAs($admin);

        $this->get(route('appointments.board', ['date' => '2026-11-16']))->assertOk()->assertSee($a->vehicle->plate_number);
        $this->get(route('appointments.create'))->assertOk();
        $this->get(route('appointments.show', $a))->assertOk()->assertSee('BK-00001');
        $this->get(route('services.index'))->assertOk()->assertSee('پاتی بریک');
        $this->get(route('bays.index'))->assertOk()->assertSee('لیفتی ١');
        $this->get(route('technicians.index'))->assertOk()->assertSee('کاروان');
        $this->get(route('vehicles.show', $a->vehicle_id))->assertOk()->assertSee('BK-00001');
        $this->get(route('booking.create'))->assertOk();
    }
}
