<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ServiceBay;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\SchedulingService;
use App\Support\Normalize;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('نۆرەی نوێ')]
class Form extends Component
{
    #[Url(as: 'customer', except: '')]
    public string $customer_id = '';

    #[Url(as: 'vehicle', except: '')]
    public string $vehicle_id = '';

    public string $customerSearch = '';
    public string $branch_id = '';

    /** @var array<int, string> */
    public array $service_ids = [];

    #[Url(except: '')]
    public string $date = '';

    #[Url(except: '')]
    public string $time = '';

    #[Url(as: 'bay', except: '')]
    public string $bay_id = '';

    /** '' = دواتر، 'auto' = AI، یان ژمارەی وەستا */
    public string $technician_id = 'auto';

    public string $source = 'phone';
    public string $customer_notes = '';
    public string $internal_notes = '';

    public function mount(): void
    {
        $this->authorize('appointments.manage');

        $bay = $this->bay_id !== '' ? ServiceBay::find((int) $this->bay_id) : null;
        $this->branch_id = (string) ($bay?->branch_id ?? Auth::user()->branch_id ?? Branch::where('is_active', true)->value('id') ?? '');
        $this->date = $this->date ?: today()->toDateString();

        if (request()->boolean('walkin')) {
            $this->source = 'walk_in';
            $this->date = today()->toDateString();
        }

        if ($this->vehicle_id !== '' && $this->customer_id === '') {
            $this->customer_id = (string) (\App\Models\Vehicle::find((int) $this->vehicle_id)?->customer_id ?? '');
        }
    }

    public function selectCustomer(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->customer_id = (string) $customer->id;
        $this->customerSearch = '';
        $this->vehicle_id = (string) ($customer->vehicles()->count() === 1 ? $customer->vehicles()->value('id') : '');
    }

    public function clearCustomer(): void
    {
        $this->customer_id = '';
        $this->vehicle_id = '';
    }

    public function pickSlot(string $date, string $time): void
    {
        $this->date = $date;
        $this->time = $time;
    }

    public function updatedDate(): void
    {
        $this->time = '';
    }

    public function updatedServiceIds(): void
    {
        $this->time = '';
    }

    public function updatedBranchId(): void
    {
        $this->time = '';
        $this->bay_id = '';
    }

    public function save(AppointmentService $service): void
    {
        $this->authorize('appointments.manage');

        $this->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:service_catalog,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'source' => ['required', Rule::in(array_keys(Appointment::SOURCES))],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'customer_id.required' => 'کڕیار هەڵبژێرە.',
            'vehicle_id.required' => 'ئۆتۆمبێل هەڵبژێرە.',
            'service_ids.required' => 'لانیکەم یەک خزمەتگوزاری هەڵبژێرە.',
            'time.required' => 'کاتێک هەڵبژێرە.',
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        try {
            $appointment = $service->create($actor, [
                'customer_id' => (int) $this->customer_id,
                'vehicle_id' => (int) $this->vehicle_id,
                'branch_id' => (int) $this->branch_id,
                'service_ids' => array_map('intval', $this->service_ids),
                'scheduled_at' => "{$this->date} {$this->time}:00",
                'bay_id' => $this->bay_id !== '' ? (int) $this->bay_id : null,
                'technician_id' => $this->technician_id === 'auto' ? 'auto' : ($this->technician_id !== '' ? (int) $this->technician_id : null),
                'source' => $this->source,
                'customer_notes' => $this->customer_notes ?: null,
                'internal_notes' => $this->internal_notes ?: null,
            ]);
        } catch (DomainException $e) {
            $this->addError('time', $e->getMessage());

            return;
        }

        session()->flash('success', "نۆرە {$appointment->reference} دانرا.");
        $this->redirectRoute('appointments.show', $appointment);
    }

    public function render(SchedulingService $scheduling, AppointmentService $appointments)
    {
        $branchId = $this->branch_id !== '' ? (int) $this->branch_id : null;
        $customer = $this->customer_id !== '' ? Customer::with('vehicles')->find((int) $this->customer_id) : null;

        $customerResults = collect();
        $term = trim($this->customerSearch);
        if (! $customer && mb_strlen($term) >= 2) {
            $phone = Normalize::phone($term);
            $plateKey = Normalize::plateKey($term);
            $customerResults = Customer::with('vehicles')
                ->where(function ($q) use ($term, $phone, $plateKey) {
                    $q->where('full_name', 'like', "%{$term}%")
                        ->orWhereHas('vehicles', fn ($v) => $v->whereRaw("UPPER(REPLACE(REPLACE(plate_number, ' ', ''), '-', '')) LIKE ?", ["%{$plateKey}%"]));
                    if ($phone && strlen($phone) >= 4) {
                        $q->orWhere('phone', 'like', "%{$phone}%");
                    }
                })
                ->limit(8)->get();
        }

        $services = ServiceCatalog::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $selected = $services->whereIn('id', array_map('intval', $this->service_ids));

        $req = null;
        $reqError = null;
        $slots = [];
        $suggestions = [];
        $ranking = collect();

        if ($selected->isNotEmpty()) {
            try {
                $req = $scheduling->requirements($selected);
                $slots = $scheduling->availableSlots($branchId, Carbon::parse($this->date), $req['minutes'], $req['bay_type']);
                $suggestions = $scheduling->suggestSlots($branchId, $req['minutes'], $req['bay_type']);

                if ($this->time !== '') {
                    $start = Carbon::parse("{$this->date} {$this->time}");
                    $ranking = $scheduling->rankTechnicians($branchId, $start, $start->copy()->addMinutes($req['minutes']), $req['skills']);
                }
            } catch (DomainException $e) {
                $reqError = $e->getMessage();
            }
        }

        // Walk-in: یەکەم کاتی بەتاڵی ئەمڕۆ خۆکارانە هەڵدەبژێردرێت
        if ($this->source === 'walk_in' && $this->time === '' && $slots && $this->date === today()->toDateString()) {
            $this->time = $slots[0]['time'];
        }

        return view('livewire.appointments.form', [
            'customer' => $customer,
            'customerResults' => $customerResults,
            'noShows' => $customer ? $appointments->noShowCount($customer->id) : 0,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'services' => $services->groupBy('category'),
            'selected' => $selected,
            'req' => $req,
            'reqError' => $reqError,
            'slots' => $slots,
            'suggestions' => $suggestions,
            'ranking' => $ranking,
            'bays' => $req ? $scheduling->compatibleBays($branchId, $req['bay_type']) : collect(),
        ]);
    }
}
