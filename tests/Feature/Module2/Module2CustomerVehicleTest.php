<?php

namespace Tests\Feature\Module2;

use App\Livewire\Customers\Form as CustomerForm;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Customers\Show as CustomerShow;
use App\Livewire\Vehicles\Form as VehicleForm;
use App\Livewire\Vehicles\Show as VehicleShow;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Support\Normalize;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Module2CustomerVehicleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function makeUser(string $roleName): User
    {
        $user = User::create([
            'name' => 'کارمەند '.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'status' => 'active',
        ]);
        $user->forceFill(['role_id' => Role::where('name', $roleName)->value('id')])->save();

        return $user;
    }

    // ---------- یەکسانکردنی داتا ----------

    public function test_phone_numbers_are_normalized(): void
    {
        $this->assertSame('07501112233', Normalize::phone('+964 750 111 2233'));
        $this->assertSame('07501112233', Normalize::phone('00964-750-111-2233'));
        $this->assertSame('07501112233', Normalize::phone('750 111 2233'));
        $this->assertSame('07501112233', Normalize::phone('0750 111 2233'));
        $this->assertNull(Normalize::phone(''));
    }

    // ---------- کڕیار ----------

    public function test_advisor_creates_customer_with_normalized_phone_and_audit(): void
    {
        $this->actingAs($this->makeUser('advisor'));

        Livewire::test(CustomerForm::class)
            ->set('full_name', 'ئاراس محەمەد')
            ->set('phone', '+964 750 111 2233')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $customer = Customer::firstOrFail();
        $this->assertSame('07501112233', $customer->phone);
        $this->assertDatabaseHas('audit_logs', ['action' => 'customer.created', 'model_type' => 'Customer', 'model_id' => $customer->id]);
    }

    public function test_duplicate_phone_shows_warning(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        Customer::factory()->create(['full_name' => 'کڕیاری کۆن', 'phone' => '07501112233']);

        Livewire::test(CustomerForm::class)
            ->set('phone', '0750 111 2233')
            ->assertSet('duplicate.name', 'کڕیاری کۆن')
            ->assertSee('پێشتر بۆ');
    }

    public function test_retail_customer_cannot_keep_credit_limit(): void
    {
        $this->actingAs($this->makeUser('admin'));

        Livewire::test(CustomerForm::class)
            ->set('customer_type', 'retail')
            ->set('full_name', 'تاقیکردنەوە')
            ->set('phone', '07701234567')
            ->set('credit_limit', '500000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(0, Customer::firstOrFail()->credit_limit);
    }

    public function test_customer_search_finds_owner_by_plate_without_spaces(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        $owner = Customer::factory()->create(['full_name' => 'خاوەنی تابلۆ']);
        Vehicle::factory()->for($owner)->create(['plate_number' => '22 أ 45871']);
        Customer::factory()->create(['full_name' => 'کەسێکی تر']);

        Livewire::test(CustomersIndex::class)
            ->set('search', '22أ45871')
            ->assertSee('خاوەنی تابلۆ')
            ->assertDontSee('کەسێکی تر');
    }

    public function test_customer_with_vehicles_cannot_be_archived(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);
        $customer = Customer::factory()->has(Vehicle::factory())->create();

        Livewire::test(CustomerShow::class, ['customer' => $customer->id])
            ->call('archive')
            ->assertDispatched('flash', type: 'error');

        $this->assertNotSoftDeleted($customer);
    }

    public function test_customer_without_vehicles_is_archived_and_restored(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $customer = Customer::factory()->create();

        Livewire::test(CustomerShow::class, ['customer' => $customer->id])->call('archive');
        $this->assertSoftDeleted($customer);

        Livewire::test(CustomerShow::class, ['customer' => $customer->id])->call('restore');
        $this->assertNotSoftDeleted($customer);
        $this->assertDatabaseHas('audit_logs', ['action' => 'customer.archived']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'customer.restored']);
    }

    // ---------- ئۆتۆمبێل ----------

    public function test_vehicle_is_created_with_uppercase_vin(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        $owner = Customer::factory()->create();

        Livewire::test(VehicleForm::class)
            ->set('customer_id', (string) $owner->id)
            ->set('plate_number', '  22   ب   10021 ')
            ->set('vin', 'jtmhv05j604123456')
            ->set('make', 'Toyota')
            ->set('model', 'Land Cruiser')
            ->set('current_mileage', '98000')
            ->call('save')
            ->assertHasNoErrors();

        $vehicle = Vehicle::firstOrFail();
        $this->assertSame('JTMHV05J604123456', $vehicle->vin);
        $this->assertSame('22 ب 10021', $vehicle->plate_number);
        $this->assertSame($owner->id, $vehicle->customer_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'vehicle.created', 'model_id' => $vehicle->id]);
    }

    public function test_invalid_vin_is_rejected(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        $owner = Customer::factory()->create();

        // پیتی O و I لە VIN ڕێگەپێنەدراون
        Livewire::test(VehicleForm::class)
            ->set('customer_id', (string) $owner->id)
            ->set('plate_number', '22 أ 1')
            ->set('vin', 'JTMHV05J6O4I23456')
            ->set('make', 'Toyota')
            ->set('model', 'Camry')
            ->call('save')
            ->assertHasErrors(['vin']);

        // کەمتر لە ١٧ پیت
        Livewire::test(VehicleForm::class)
            ->set('customer_id', (string) $owner->id)
            ->set('plate_number', '22 أ 1')
            ->set('vin', 'ABC123')
            ->set('make', 'Toyota')
            ->set('model', 'Camry')
            ->call('save')
            ->assertHasErrors(['vin']);

        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_vehicle_requires_owner(): void
    {
        $this->actingAs($this->makeUser('advisor'));

        Livewire::test(VehicleForm::class)
            ->set('plate_number', '22 أ 1')
            ->set('make', 'Toyota')
            ->set('model', 'Camry')
            ->call('save')
            ->assertHasErrors(['customer_id']);
    }

    public function test_duplicate_plate_shows_warning(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        Vehicle::factory()->create(['plate_number' => '22 أ 45871']);

        Livewire::test(VehicleForm::class)
            ->set('plate_number', '22أ45871')
            ->assertNotSet('duplicatePlate', null);
    }

    public function test_mileage_cannot_go_down(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        $vehicle = Vehicle::factory()->create(['current_mileage' => 100000]);

        Livewire::test(VehicleForm::class, ['vehicle' => $vehicle])
            ->set('current_mileage', '90000')
            ->call('save')
            ->assertHasErrors(['current_mileage']);

        $this->assertSame(100000, $vehicle->fresh()->current_mileage);

        Livewire::test(VehicleForm::class, ['vehicle' => $vehicle])
            ->set('current_mileage', '105000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(105000, $vehicle->fresh()->current_mileage);
    }

    public function test_vehicle_ownership_transfer_keeps_history(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        $oldOwner = Customer::factory()->create(['full_name' => 'خاوەنی کۆن']);
        $newOwner = Customer::factory()->create(['full_name' => 'خاوەنی نوێ']);
        $vehicle = Vehicle::factory()->for($oldOwner)->create(['current_mileage' => 50000]);

        $branch = Branch::create(['name' => 'لقی تاقی', 'code' => 'T-1', 'is_active' => true]);
        WorkOrder::create([
            'wo_number' => 'WO-0001', 'branch_id' => $branch->id, 'customer_id' => $oldOwner->id,
            'vehicle_id' => $vehicle->id, 'status' => 'delivered', 'mileage_in' => 48000,
            'customer_complaint' => 'دەنگی بریک',
        ]);

        Livewire::test(VehicleShow::class, ['vehicle' => $vehicle->id])
            ->call('openTransfer')
            ->call('pickTransferCustomer', $newOwner->id)
            ->set('transfer_mileage', '52000')
            ->call('transfer')
            ->assertHasNoErrors();

        $vehicle->refresh();
        $this->assertSame($newOwner->id, $vehicle->customer_id);
        $this->assertSame(52000, $vehicle->current_mileage);
        $this->assertDatabaseHas('audit_logs', ['action' => 'vehicle.transferred', 'model_id' => $vehicle->id]);

        // مێژووی چاککردنەوە لەگەڵ ئۆتۆمبێلەکە ماوەتەوە
        Livewire::test(VehicleShow::class, ['vehicle' => $vehicle->id])
            ->assertSee('WO-0001')
            ->assertSee('دەنگی بریک')
            ->assertSee('خاوەنی کۆن');
    }

    public function test_cannot_transfer_to_same_owner(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        $vehicle = Vehicle::factory()->create();

        Livewire::test(VehicleShow::class, ['vehicle' => $vehicle->id])
            ->call('openTransfer')
            ->set('transfer_customer_id', (string) $vehicle->customer_id)
            ->call('transfer')
            ->assertHasErrors(['transfer_customer_id']);
    }

    public function test_archived_vehicle_is_hidden_but_history_is_kept(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $vehicle = Vehicle::factory()->create();

        Livewire::test(VehicleShow::class, ['vehicle' => $vehicle->id])->call('archive');

        $this->assertSoftDeleted($vehicle);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);

        // هێشتا دەکرێتەوە
        $this->get(route('vehicles.show', $vehicle->id))->assertOk()->assertSee('ئەرشیف کراوە');
    }

    // ---------- دەسەڵات ----------

    public function test_technician_can_view_vehicles_but_not_customers(): void
    {
        $this->actingAs($this->makeUser('technician'));
        $vehicle = Vehicle::factory()->create();

        $this->get(route('vehicles.index'))->assertOk();
        $this->get(route('vehicles.show', $vehicle->id))->assertOk();
        $this->get(route('vehicles.create'))->assertForbidden();
        $this->get(route('customers.index'))->assertForbidden();
    }

    public function test_advisor_cannot_archive(): void
    {
        $this->actingAs($this->makeUser('advisor'));
        $customer = Customer::factory()->create();

        Livewire::test(CustomerShow::class, ['customer' => $customer->id])
            ->call('archive')
            ->assertForbidden();

        $this->assertNotSoftDeleted($customer);
    }

    public function test_pages_render_for_admin(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $vehicle = Vehicle::factory()->create();

        $this->get(route('customers.index'))->assertOk();
        $this->get(route('customers.create'))->assertOk();
        $this->get(route('customers.show', $vehicle->customer_id))->assertOk()->assertSee($vehicle->plate_number);
        $this->get(route('customers.edit', $vehicle->customer_id))->assertOk();
        $this->get(route('vehicles.index'))->assertOk()->assertSee($vehicle->plate_number);
        $this->get(route('vehicles.create', ['customer' => $vehicle->customer_id]))->assertOk();
        $this->get(route('vehicles.edit', $vehicle))->assertOk();
    }
}
