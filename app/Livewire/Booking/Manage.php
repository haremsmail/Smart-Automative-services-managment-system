<?php

namespace App\Livewire\Booking;

use App\Models\Appointment;
use App\Services\AppointmentService;
use DomainException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** کڕیار لە ڕێگەی لینکی تایبەتەوە نۆرەکەی دەبینێت یان هەڵیدەوەشێنێتەوە */
#[Layout('components.layouts.public')]
#[Title('نۆرەکەم')]
class Manage extends Component
{
    public string $token;

    public function mount(string $token): void
    {
        abort_unless(Appointment::where('public_token', $token)->exists(), 404);
        $this->token = $token;
    }

    public function cancel(AppointmentService $service): void
    {
        try {
            $service->cancelByCustomer(Appointment::where('public_token', $this->token)->firstOrFail());
        } catch (DomainException $e) {
            $this->addError('cancel', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.booking.manage', [
            'a' => Appointment::with(['vehicle', 'items', 'branch'])->where('public_token', $this->token)->firstOrFail(),
        ]);
    }
}
