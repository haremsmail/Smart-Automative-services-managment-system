<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerService;
use App\Support\Normalize;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('کڕیاران')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: false)]
    public bool $archived = false;

    public function mount(): void
    {
        $this->authorize('customers.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'type', 'archived'], true)) {
            $this->resetPage();
        }
    }

    public function restore(int $id, CustomerService $service): void
    {
        /** @var User $actor */
        $actor = Auth::user();

        try {
            $service->restore($actor, Customer::onlyTrashed()->findOrFail($id));
            $this->dispatch('flash', message: 'کڕیار گەڕێندرایەوە.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $term = trim($this->search);
        $phone = Normalize::phone($term);
        $plateKey = Normalize::plateKey($term);

        $customers = Customer::query()
            ->when($this->archived && Auth::user()->can('customers.delete'), fn ($q) => $q->onlyTrashed())
            ->withCount('vehicles')
            ->with(['vehicles' => fn ($q) => $q->select('id', 'customer_id', 'plate_number', 'make', 'model')->limit(50)])
            ->when($term !== '', function ($query) use ($term, $phone, $plateKey) {
                // گەڕان بە ناو، تەلەفۆن، ئیمەیڵ، تابلۆ یان VIN
                $query->where(function ($q) use ($term, $phone, $plateKey) {
                    $q->where('full_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");

                    if ($phone && strlen($phone) >= 4) {
                        $q->orWhere('phone', 'like', "%{$phone}%")
                            ->orWhere('whatsapp', 'like', "%{$phone}%");
                    }

                    $q->orWhereHas('vehicles', fn ($v) => $v
                        ->whereRaw("UPPER(REPLACE(REPLACE(plate_number, ' ', ''), '-', '')) LIKE ?", ["%{$plateKey}%"])
                        ->orWhere('vin', 'like', '%'.strtoupper($term).'%'));
                });
            })
            ->when($this->type !== '', fn ($q) => $q->where('customer_type', $this->type))
            ->latest()
            ->paginate(15);

        return view('livewire.customers.index', [
            'customers' => $customers,
            'types' => Customer::TYPES,
        ]);
    }
}
