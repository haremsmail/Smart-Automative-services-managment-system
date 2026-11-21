<?php

namespace App\Livewire\Customers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\CustomerService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('پرۆفایلی کڕیار')]
class Show extends Component
{
    public int $customerId;

    public function mount(int $customer): void
    {
        $this->authorize('customers.view');
        $this->customerId = Customer::withTrashed()->findOrFail($customer)->id;
    }

    public function archive(CustomerService $service): void
    {
        /** @var User $actor */
        $actor = Auth::user();

        try {
            $service->archive($actor, $this->customer());
            $this->dispatch('flash', message: 'کڕیار ئەرشیف کرا.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    public function restore(CustomerService $service): void
    {
        /** @var User $actor */
        $actor = Auth::user();

        try {
            $service->restore($actor, $this->customer());
            $this->dispatch('flash', message: 'کڕیار گەڕێندرایەوە.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    private function customer(): Customer
    {
        return Customer::withTrashed()->findOrFail($this->customerId);
    }

    public function render()
    {
        $customer = $this->customer();
        $customer->load(['vehicles' => fn ($q) => $q->latest()]);

        $archivedVehicles = $customer->vehicles()->onlyTrashed()->latest('deleted_at')->get();

        $invoices = $customer->invoices()->where('status', '!=', 'void');

        $stats = [
            'vehicles' => $customer->vehicles->count(),
            'visits' => $customer->workOrders()->count(),
            'total_spent' => (float) (clone $invoices)->sum('grand_total'),
            'balance' => (float) (clone $invoices)->whereIn('status', ['unpaid', 'partial'])
                ->selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) as due')->value('due'),
            'last_visit' => $customer->workOrders()->max('created_at'),
        ];

        $recentWorkOrders = WorkOrder::with('vehicle')
            ->where('customer_id', $customer->id)
            ->latest()
            ->limit(10)
            ->get();

        $history = AuditLog::with('user')
            ->where('model_type', 'Customer')
            ->where('model_id', $customer->id)
            ->latest('created_at')
            ->limit(20)
            ->get();

        $upcoming = \App\Models\Appointment::with('vehicle')
            ->where('customer_id', $customer->id)
            ->active()
            ->orderBy('scheduled_at')
            ->get();

        return view('livewire.customers.show', [
            'upcoming' => $upcoming,
            'noShows' => \App\Models\Appointment::where('customer_id', $customer->id)->where('status', 'no_show')->count(),
            'customer' => $customer,
            'archivedVehicles' => $archivedVehicles,
            'stats' => $stats,
            'recentWorkOrders' => $recentWorkOrders,
            'history' => $history,
        ])->title($customer->full_name);
    }
}
