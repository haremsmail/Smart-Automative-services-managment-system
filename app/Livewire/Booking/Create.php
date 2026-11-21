<?php

namespace App\Livewire\Booking;

use App\Models\Branch;
use App\Models\ServiceCatalog;
use App\Services\BookingService;
use App\Services\SchedulingService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * پەڕەی گشتی نۆرەی ئۆنلاین بۆ کڕیار (بەبێ چوونەژوورەوە).
 * تەنها کاتە بەتاڵە ڕاستەقینەکان پیشان دەدرێن، لە ماوەی کاتی ئۆنلایندا.
 */
#[Layout('components.layouts.public')]
#[Title('نۆرەی ئۆنلاین')]
class Create extends Component
{
    public int $step = 1;

    public string $branch_id = '';
    /** @var array<int, string> */
    public array $service_ids = [];
    public string $date = '';
    public string $time = '';

    public string $full_name = '';
    public string $phone = '';
    public string $plate_number = '';
    public string $make = '';
    public string $model = '';
    public string $customer_notes = '';

    /** پاش نۆرەگرتن */
    public ?string $reference = null;
    public ?string $token = null;

    public function mount(): void
    {
        $this->branch_id = (string) (Branch::where('is_active', true)->orderBy('name')->value('id') ?? '');
        $this->date = today()->toDateString();
    }

    public function updatedDate(): void
    {
        $this->time = '';
    }

    public function updatedServiceIds(): void
    {
        $this->time = '';
    }

    public function next(): void
    {
        if ($this->step === 1) {
            $this->validate(['branch_id' => 'required|exists:branches,id', 'service_ids' => 'required|array|min:1'], ['service_ids.required' => 'لانیکەم یەک خزمەتگوزاری هەڵبژێرە.']);
        }
        if ($this->step === 2) {
            $this->validate(['time' => 'required'], ['time.required' => 'کاتێک هەڵبژێرە.']);
        }
        $this->step++;
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function book(BookingService $booking): void
    {
        $this->validate([
            'full_name' => ['required', 'string', 'min:2', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^[\d\s\+\-\(\)]{7,30}$/'],
            'plate_number' => ['required', 'string', 'max:30'],
            'make' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'full_name.required' => 'ناوت بنووسە.',
            'phone.required' => 'ژمارەی تەلەفۆن بنووسە.',
            'phone.regex' => 'ژمارەی تەلەفۆن دروست نییە.',
            'plate_number.required' => 'ژمارەی تابلۆ بنووسە.',
            'make.required' => 'جۆری ئۆتۆمبێل بنووسە.',
            'model.required' => 'مۆدێل بنووسە.',
        ]);

        // پاراستن لە سپام: ٣ نۆرە لە کاتژمێرێکدا بۆ هەر IP
        $key = 'booking:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('phone', 'زۆر هەوڵدرا. تکایە دواتر هەوڵ بدەرەوە یان پەیوەندی بە گەراجەوە بکە.');

            return;
        }

        try {
            $appointment = $booking->book([
                'branch_id' => (int) $this->branch_id,
                'service_ids' => array_map('intval', $this->service_ids),
                'scheduled_at' => "{$this->date} {$this->time}:00",
                'full_name' => $this->full_name,
                'phone' => $this->phone,
                'plate_number' => $this->plate_number,
                'make' => $this->make,
                'model' => $this->model,
                'customer_notes' => $this->customer_notes ?: null,
            ]);
        } catch (DomainException $e) {
            $this->addError('phone', $e->getMessage());

            return;
        }

        RateLimiter::hit($key, 3600);
        $this->reference = $appointment->reference;
        $this->token = $appointment->public_token;
        $this->step = 4;
    }

    public function render(SchedulingService $scheduling)
    {
        $services = ServiceCatalog::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $selected = $services->whereIn('id', array_map('intval', $this->service_ids));
        [$onlineStart, $onlineEnd] = $scheduling->onlineWindow();

        $req = null;
        $slots = [];
        $error = null;

        if ($selected->isNotEmpty() && $this->step === 2) {
            try {
                $req = $scheduling->requirements($selected);
                $slots = $scheduling->availableSlots((int) $this->branch_id, Carbon::parse($this->date), $req['minutes'], $req['bay_type'], online: true);
            } catch (DomainException $e) {
                $error = $e->getMessage();
            }
        }

        return view('livewire.booking.create', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'services' => $services->groupBy('category'),
            'selected' => $selected,
            'slots' => $slots,
            'error' => $error,
            'onlineStart' => $onlineStart,
            'onlineEnd' => $onlineEnd,
            'maxDate' => today()->addDays((int) config('scheduling.online_days_ahead', 14))->toDateString(),
        ]);
    }
}
