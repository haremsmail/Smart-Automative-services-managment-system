<?php

namespace App\Livewire\Vehicles;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleService;
use App\Support\Normalize;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('پرۆفایلی ئۆتۆمبێل')]
class Show extends Component
{
    public int $vehicleId;

    // پانێڵی گواستنەوەی خاوەندارێتی
    public bool $showTransfer = false;
    public string $transferSearch = '';
    public string $transfer_customer_id = '';
    public string $transfer_mileage = '';
    public string $transfer_note = '';

    public function mount(int $vehicle): void
    {
        $this->authorize('vehicles.view');
        $this->vehicleId = Vehicle::withTrashed()->findOrFail($vehicle)->id;
    }

    public function openTransfer(): void
    {
        $this->authorize('vehicles.transfer');
        $this->reset('transferSearch', 'transfer_customer_id', 'transfer_mileage', 'transfer_note');
        $this->resetErrorBag();
        $this->showTransfer = true;
    }

    public function pickTransferCustomer(int $id): void
    {
        $this->transfer_customer_id = (string) Customer::findOrFail($id)->id;
        $this->transferSearch = '';
    }

    public function transfer(VehicleService $service): void
    {
        $this->authorize('vehicles.transfer');

        $this->validate([
            'transfer_customer_id' => ['required', 'integer', 'exists:customers,id'],
            'transfer_mileage' => ['nullable', 'integer', 'min:0', 'max:4000000'],
            'transfer_note' => ['nullable', 'string', 'max:500'],
        ], [
            'transfer_customer_id.required' => 'تکایە خاوەنی نوێ هەڵبژێرە.',
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        try {
            $service->transfer(
                $actor,
                $this->vehicle(),
                Customer::withTrashed()->findOrFail((int) $this->transfer_customer_id),
                $this->transfer_mileage !== '' ? (int) $this->transfer_mileage : null,
                $this->transfer_note ?: null,
            );
        } catch (DomainException $e) {
            $this->addError('transfer_customer_id', $e->getMessage());

            return;
        }

        $this->showTransfer = false;
        $this->dispatch('flash', message: 'خاوەندارێتی گوازرایەوە. مێژووی ئۆتۆمبێلەکە پارێزراوە.', type: 'ok');
    }

    public function archive(VehicleService $service): void
    {
        /** @var User $actor */
        $actor = Auth::user();

        try {
            $service->archive($actor, $this->vehicle());
            $this->dispatch('flash', message: 'ئۆتۆمبێل ئەرشیف کرا.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    public function restore(VehicleService $service): void
    {
        /** @var User $actor */
        $actor = Auth::user();

        try {
            $service->restore($actor, $this->vehicle());
            $this->dispatch('flash', message: 'ئۆتۆمبێل گەڕێندرایەوە.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    private function vehicle(): Vehicle
    {
        return Vehicle::withTrashed()->findOrFail($this->vehicleId);
    }

    public function render(VehicleService $service)
    {
        $vehicle = $this->vehicle()->load('customer');

        $transferResults = collect();
        $term = trim($this->transferSearch);

        if ($this->showTransfer && $this->transfer_customer_id === '' && mb_strlen($term) >= 2) {
            $phone = Normalize::phone($term);
            $transferResults = Customer::query()
                ->whereKeyNot($vehicle->customer_id)
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

        $history = AuditLog::with('user')
            ->where('model_type', 'Vehicle')
            ->where('model_id', $vehicle->id)
            ->latest('created_at')
            ->limit(20)
            ->get();

        // خاوەنەکانی پێشوو لە تۆماری گواستنەوە
        $previousOwners = AuditLog::where('model_type', 'Vehicle')
            ->where('model_id', $vehicle->id)
            ->where('action', 'vehicle.transferred')
            ->latest('created_at')
            ->get();

        return view('livewire.vehicles.show', [
            'vehicle' => $vehicle,
            'timeline' => $service->timeline($vehicle),
            'history' => $history,
            'previousOwners' => $previousOwners,
            'transferTarget' => $this->transfer_customer_id !== '' ? Customer::find((int) $this->transfer_customer_id) : null,
            'transferResults' => $transferResults,
        ])->title($vehicle->plate_number.' · '.$vehicle->title());
    }
}
