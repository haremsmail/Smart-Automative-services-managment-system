<?php

namespace App\Livewire\Vehicles;

use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleService;
use App\Support\Normalize;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('ئۆتۆمبێلەکان')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $make = '';

    #[Url(except: false)]
    public bool $archived = false;

    public function mount(): void
    {
        $this->authorize('vehicles.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'make', 'archived'], true)) {
            $this->resetPage();
        }
    }

    public function restore(int $id, VehicleService $service): void
    {
        /** @var User $actor */
        $actor = Auth::user();

        try {
            $service->restore($actor, Vehicle::onlyTrashed()->findOrFail($id));
            $this->dispatch('flash', message: 'ئۆتۆمبێل گەڕێندرایەوە.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $term = trim($this->search);
        $plateKey = Normalize::plateKey($term);
        $phone = Normalize::phone($term);

        $vehicles = Vehicle::query()
            ->when($this->archived && Auth::user()->can('vehicles.delete'), fn ($q) => $q->onlyTrashed())
            ->with('customer')
            ->when($term !== '', function ($query) use ($term, $plateKey, $phone) {
                $query->where(function ($q) use ($term, $plateKey, $phone) {
                    $q->whereRaw("UPPER(REPLACE(REPLACE(plate_number, ' ', ''), '-', '')) LIKE ?", ["%{$plateKey}%"])
                        ->orWhere('vin', 'like', '%'.strtoupper($term).'%')
                        ->orWhere('make', 'like', "%{$term}%")
                        ->orWhere('model', 'like', "%{$term}%")
                        ->orWhereHas('customer', function ($c) use ($term, $phone) {
                            $c->where('full_name', 'like', "%{$term}%");
                            if ($phone && strlen($phone) >= 4) {
                                $c->orWhere('phone', 'like', "%{$phone}%");
                            }
                        });
                });
            })
            ->when($this->make !== '', fn ($q) => $q->where('make', $this->make))
            ->latest()
            ->paginate(15);

        return view('livewire.vehicles.index', [
            'vehicles' => $vehicles,
            'makes' => Vehicle::query()->distinct()->orderBy('make')->pluck('make'),
        ]);
    }
}
