<?php

namespace App\Livewire\Vehicles;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleService;
use App\Support\Normalize;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('فۆڕمی ئۆتۆمبێل')]
class Form extends Component
{
    public ?int $vehicleId = null;

    /** خاوەن: لە ?customer=ID دێت یان لە گەڕانەوە هەڵدەبژێردرێت */
    #[Url(as: 'customer', except: '')]
    public string $customer_id = '';

    public string $ownerSearch = '';

    public string $plate_number = '';
    public string $vin = '';
    public string $make = '';
    public string $model = '';
    public string $year = '';
    public string $color = '';
    public string $engine_type = '';
    public string $transmission = '';
    public string $current_mileage = '';
    public string $notes = '';

    /** کیلۆمەتری ئێستا لە بنکەی داتا، بۆ پیشاندانی کەمترین بەها */
    public int $minMileage = 0;

    public ?array $duplicatePlate = null;

    public function mount(?Vehicle $vehicle = null): void
    {
        $this->authorize($vehicle?->exists ? 'vehicles.update' : 'vehicles.create');

        if ($vehicle?->exists) {
            $this->vehicleId = $vehicle->id;
            $this->customer_id = (string) $vehicle->customer_id;
            $this->plate_number = $vehicle->plate_number;
            $this->vin = (string) $vehicle->vin;
            $this->make = $vehicle->make;
            $this->model = $vehicle->model;
            $this->year = (string) $vehicle->year;
            $this->color = (string) $vehicle->color;
            $this->engine_type = (string) $vehicle->engine_type;
            $this->transmission = (string) $vehicle->transmission;
            $this->current_mileage = (string) $vehicle->current_mileage;
            $this->notes = (string) $vehicle->notes;
            $this->minMileage = (int) $vehicle->current_mileage;
        }
    }

    public function selectOwner(int $id): void
    {
        $this->customer_id = (string) Customer::findOrFail($id)->id;
        $this->ownerSearch = '';
    }

    public function clearOwner(): void
    {
        if ($this->vehicleId === null) {
            $this->customer_id = '';
        }
    }

    public function updatedPlateNumber(VehicleService $service): void
    {
        $match = $service->findDuplicatePlate($this->plate_number, $this->vehicleId);

        $this->duplicatePlate = $match ? [
            'id' => $match->id,
            'title' => $match->title(),
            'owner' => $match->customer?->full_name,
            'archived' => $match->trashed(),
        ] : null;
    }

    public function updatedVin(): void
    {
        $this->vin = (string) Normalize::vin($this->vin);
    }

    public function save(VehicleService $service): void
    {
        $isNew = $this->vehicleId === null;
        $this->authorize($isNew ? 'vehicles.create' : 'vehicles.update');

        $this->vin = (string) Normalize::vin($this->vin);
        $maxYear = (int) date('Y') + 1;

        $data = $this->validate([
            'customer_id' => [$isNew ? 'required' : 'nullable', 'integer', Rule::exists('customers', 'id')->whereNull('deleted_at')],
            'plate_number' => ['required', 'string', 'max:30'],
            // VIN: ١٧ پیت، بەبێ I و O و Q (ستانداردی ISO 3779)
            'vin' => ['nullable', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/', Rule::unique('vehicles', 'vin')->ignore($this->vehicleId)],
            'make' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'year' => ['nullable', 'integer', 'min:1950', "max:{$maxYear}"],
            'color' => ['nullable', 'string', 'max:40'],
            'engine_type' => ['nullable', Rule::in(array_keys(Vehicle::ENGINE_TYPES))],
            'transmission' => ['nullable', Rule::in(array_keys(Vehicle::TRANSMISSIONS))],
            'current_mileage' => ['nullable', 'integer', 'min:'.$this->minMileage, 'max:4000000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'customer_id.required' => 'تکایە خاوەنی ئۆتۆمبێل هەڵبژێرە.',
            'vin.size' => 'VIN دەبێت ڕێک ١٧ پیت بێت.',
            'vin.regex' => 'VIN دروست نییە. تەنها ژمارە و پیتی ئینگلیزی، بەبێ I و O و Q.',
            'vin.unique' => 'ئەم VIN ـە پێشتر بۆ ئۆتۆمبێلێکی تر تۆمارکراوە.',
            'current_mileage.min' => 'کیلۆمەتر ناتوانێت کەم بێتەوە. ئێستا: '.number_format($this->minMileage).' کم.',
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        try {
            $vehicle = $isNew
                ? $service->create($actor, Customer::findOrFail($data['customer_id']), $data)
                : $service->update($actor, Vehicle::findOrFail($this->vehicleId), $data);
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');

            return;
        }

        session()->flash('success', $isNew ? 'ئۆتۆمبێل زیادکرا.' : 'زانیارییەکانی ئۆتۆمبێل نوێکرانەوە.');
        $this->redirectRoute('vehicles.show', $vehicle);
    }

    public function render()
    {
        $owner = $this->customer_id !== '' ? Customer::find((int) $this->customer_id) : null;

        $ownerResults = collect();
        $term = trim($this->ownerSearch);

        if (! $owner && mb_strlen($term) >= 2) {
            $phone = Normalize::phone($term);
            $ownerResults = Customer::query()
                ->where(function ($q) use ($term, $phone) {
                    $q->where('full_name', 'like', "%{$term}%");
                    if ($phone && strlen($phone) >= 4) {
                        $q->orWhere('phone', 'like', "%{$phone}%");
                    }
                })
                ->orderBy('full_name')
                ->limit(8)
                ->get();
        }

        return view('livewire.vehicles.form', [
            'owner' => $owner,
            'ownerResults' => $ownerResults,
            'engineTypes' => Vehicle::ENGINE_TYPES,
            'transmissions' => Vehicle::TRANSMISSIONS,
            'makes' => Vehicle::query()->distinct()->orderBy('make')->pluck('make'),
        ]);
    }
}
