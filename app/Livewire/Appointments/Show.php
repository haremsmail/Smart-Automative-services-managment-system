<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\SchedulingService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('نۆرە')]
class Show extends Component
{
    public int $appointmentId;

    public string $cancel_reason = '';
    public bool $showReschedule = false;
    public string $new_date = '';
    public string $new_time = '';
    public bool $showAssign = false;

    public function mount(Appointment $appointment): void
    {
        $this->authorize('appointments.view');
        $this->appointmentId = $appointment->id;
        $this->new_date = $appointment->scheduled_at->toDateString();
    }

    private function appointment(): Appointment
    {
        return Appointment::with(['customer', 'vehicle', 'branch', 'bay', 'technician', 'items.service', 'creator'])->findOrFail($this->appointmentId);
    }

    private function actor(): User
    {
        /** @var User $u */
        $u = Auth::user();

        return $u;
    }

    public function setStatus(string $status, AppointmentService $service): void
    {
        try {
            $service->changeStatus($this->actor(), $this->appointment(), $status, $status === 'cancelled' ? ($this->cancel_reason ?: null) : null);
            $this->dispatch('flash', message: 'دۆخ گۆڕدرا: '.Appointment::STATUSES[$status], type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    public function assign(string $technicianId, AppointmentService $service): void
    {
        try {
            $service->assignTechnician($this->actor(), $this->appointment(), $technicianId !== '' ? (int) $technicianId : null);
            $this->showAssign = false;
            $this->dispatch('flash', message: 'وەستا سپێردرا.', type: 'ok');
        } catch (DomainException $e) {
            $this->dispatch('flash', message: $e->getMessage(), type: 'error');
        }
    }

    public function reschedule(AppointmentService $service): void
    {
        $this->validate(['new_date' => ['required', 'date'], 'new_time' => ['required', 'date_format:H:i']], ['new_time.required' => 'کاتێک هەڵبژێرە.']);

        try {
            $a = $service->reschedule($this->actor(), $this->appointment(), "{$this->new_date} {$this->new_time}:00");
            $this->showReschedule = false;
            $this->dispatch('flash', message: 'کاتی نۆرە گۆڕدرا.'.($a->technician_id ? '' : ' ⚠️ وەستاکە لەو کاتەدا بەتاڵ نەبوو، دووبارە بیسپێرە.'), type: 'ok');
        } catch (DomainException $e) {
            $this->addError('new_time', $e->getMessage());
        }
    }

    public function render(SchedulingService $scheduling, AppointmentService $service)
    {
        $a = $this->appointment();
        $skills = $a->items->pluck('service.skill')->filter()->unique()->values()->all();

        $ranking = $this->showAssign && $a->isActive()
            ? $scheduling->rankTechnicians($a->branch_id, $a->scheduled_at, $a->ends_at, $skills, $a->id)
            : collect();

        $slots = $this->showReschedule
            ? $scheduling->availableSlots($a->branch_id, Carbon::parse($this->new_date), $a->duration_minutes, $a->bay?->bay_type ?? 'general', false, $a->id)
            : [];

        $wa = preg_replace('/^0/', '964', (string) ($a->customer?->whatsapp ?: $a->customer?->phone));
        $message = "سڵاو {$a->customer?->full_name} 👋\nبیرخستنەوەی نۆرەکەت لە ".config('app.name').":\n📅 ".$a->scheduled_at->format('Y-m-d')." ⏰ ".$a->scheduled_at->format('H:i')."\n🚗 {$a->vehicle?->plate_number}\n🔧 {$a->requested_services}\nژمارەی نۆرە: {$a->reference}\n".route('booking.manage', $a->public_token);

        return view('livewire.appointments.show', [
            'a' => $a,
            'noShows' => $service->noShowCount($a->customer_id),
            'ranking' => $ranking,
            'slots' => $slots,
            'whatsappUrl' => 'https://wa.me/'.$wa.'?text='.rawurlencode($message),
            'history' => AuditLog::with('user')->where('model_type', 'Appointment')->where('model_id', $a->id)->latest('created_at')->get(),
        ])->title($a->reference);
    }
}
