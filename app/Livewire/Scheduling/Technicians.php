<?php

namespace App\Livewire\Scheduling;

use App\Models\Shift;
use App\Models\SystemSetting;
use App\Models\TechnicianSchedule;
use App\Models\TechnicianSkill;
use App\Models\TechnicianTimeOff;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * بەڕێوەبەر لێرە کۆنتڕۆڵی وەستاکان دەکات:
 * خشتەی شەفتی هەفتانە (وەک تەکسی: ڕۆژ/شەو/تەواو)، شارەزایی ⭐، مۆڵەت، و کاتی نۆرەی ئۆنلاین.
 */
#[Layout('components.layouts.app')]
#[Title('وەستاکان و شەفت')]
class Technicians extends Component
{
    #[Url(except: 'roster')]
    public string $tab = 'roster';

    // جۆری شەفت
    public ?int $shiftId = null;
    public string $shift_name = '';
    public string $shift_start = '08:00';
    public string $shift_end = '16:00';
    public string $shift_color = 'amber';

    // مۆڵەت / شەفتی جێگرەوە
    public string $off_user_id = '';
    public string $off_from = '';
    public string $off_to = '';
    public string $off_shift_id = '';
    public string $off_reason = '';

    // کاتی نۆرەی ئۆنلاین
    public string $online_start = '';
    public string $online_end = '';

    public function mount(): void
    {
        $this->authorize('technicians.manage');
        $this->online_start = SystemSetting::get('booking.online_start', config('scheduling.online_start'));
        $this->online_end = SystemSetting::get('booking.online_end', config('scheduling.online_end'));
        $this->off_from = $this->off_to = now()->addDay()->toDateString();
    }

    // ------------------------------------------------------------------ خشتەی هەفتانە

    public function setSchedule(int $userId, int $weekday, string $shiftId, AuditService $audit): void
    {
        $this->authorize('technicians.manage');
        $tech = User::technicians()->findOrFail($userId);
        abort_unless(array_key_exists($weekday, TechnicianSchedule::WEEKDAYS), 422);

        if ($shiftId === '') {
            TechnicianSchedule::where('user_id', $tech->id)->where('weekday', $weekday)->delete();
        } else {
            $shift = Shift::findOrFail((int) $shiftId);
            TechnicianSchedule::updateOrCreate(['user_id' => $tech->id, 'weekday' => $weekday], ['shift_id' => $shift->id]);
        }

        $audit->record(Auth::user(), 'schedule.updated', $tech, [], [
            'weekday' => TechnicianSchedule::WEEKDAYS[$weekday],
            'shift' => $shiftId === '' ? 'پشوو' : Shift::find((int) $shiftId)?->name,
        ]);
    }

    // ------------------------------------------------------------------ شارەزایی

    public function setSkill(int $userId, string $skill, string $level, AuditService $audit): void
    {
        $this->authorize('technicians.manage');
        $tech = User::technicians()->findOrFail($userId);
        abort_unless(array_key_exists($skill, config('scheduling.skills')), 422);
        $level = max(0, min(5, (int) $level));

        if ($level === 0) {
            TechnicianSkill::where('user_id', $tech->id)->where('skill', $skill)->delete();
        } else {
            TechnicianSkill::updateOrCreate(['user_id' => $tech->id, 'skill' => $skill], ['level' => $level]);
        }

        $audit->record(Auth::user(), 'skill.updated', $tech, [], ['skill' => $skill, 'level' => $level]);
    }

    // ------------------------------------------------------------------ جۆری شەفت

    public function editShift(int $id): void
    {
        $s = Shift::findOrFail($id);
        $this->shiftId = $s->id;
        $this->shift_name = $s->name;
        $this->shift_start = substr($s->start_time, 0, 5);
        $this->shift_end = substr($s->end_time, 0, 5);
        $this->shift_color = $s->color;
    }

    public function saveShift(AuditService $audit): void
    {
        $this->authorize('technicians.manage');

        $data = $this->validate([
            'shift_name' => ['required', 'string', 'max:50'],
            'shift_start' => ['required', 'date_format:H:i'],
            'shift_end' => ['required', 'date_format:H:i', 'different:shift_start'],
            'shift_color' => ['required', Rule::in(array_keys(Shift::COLORS))],
        ], ['shift_end.different' => 'کاتی دەستپێک و کۆتایی نابێت وەک یەک بن.']);

        $values = ['name' => $data['shift_name'], 'start_time' => $data['shift_start'], 'end_time' => $data['shift_end'], 'color' => $data['shift_color']];
        $shift = Shift::updateOrCreate(['id' => $this->shiftId], $values);
        $audit->record(Auth::user(), $this->shiftId ? 'shift.updated' : 'shift.created', $shift, [], $values);

        $this->reset('shiftId', 'shift_name');
        $this->dispatch('flash', message: 'شەفت پاشەکەوتکرا.', type: 'ok');
    }

    public function toggleShift(int $id): void
    {
        $this->authorize('technicians.manage');
        $shift = Shift::findOrFail($id);
        $shift->update(['is_active' => ! $shift->is_active]);
    }

    // ------------------------------------------------------------------ مۆڵەت

    public function addTimeOff(AuditService $audit): void
    {
        $this->authorize('technicians.manage');

        $this->validate([
            'off_user_id' => ['required', 'integer', 'exists:users,id'],
            'off_from' => ['required', 'date', 'after_or_equal:today'],
            'off_to' => ['required', 'date', 'after_or_equal:off_from'],
            'off_shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'off_reason' => ['nullable', 'string', 'max:200'],
        ], ['off_user_id.required' => 'وەستا هەڵبژێرە.', 'off_from.after_or_equal' => 'بەروار نابێت لە ڕابردوودا بێت.']);

        $from = Carbon::parse($this->off_from);
        $to = Carbon::parse($this->off_to);

        if ($from->diffInDays($to) > 60) {
            $this->addError('off_to', 'زۆرترین ماوە ٦٠ ڕۆژە.');

            return;
        }

        $tech = User::technicians()->findOrFail((int) $this->off_user_id);

        DB::transaction(function () use ($from, $to, $tech) {
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                TechnicianTimeOff::updateOrCreate(
                    ['user_id' => $tech->id, 'date' => $d->toDateString()],
                    ['shift_id' => $this->off_shift_id ?: null, 'reason' => $this->off_reason ?: null, 'created_by' => Auth::id()],
                );
            }
        });

        $audit->record(Auth::user(), 'timeoff.created', $tech, [], [
            'from' => $from->toDateString(), 'to' => $to->toDateString(),
            'type' => $this->off_shift_id ? 'شەفتی جێگرەوە' : 'مۆڵەت', 'reason' => $this->off_reason,
        ]);

        $this->reset('off_reason', 'off_shift_id');
        $this->dispatch('flash', message: 'تۆمارکرا.', type: 'ok');
    }

    public function deleteTimeOff(int $id): void
    {
        $this->authorize('technicians.manage');
        TechnicianTimeOff::findOrFail($id)->delete();
    }

    // ------------------------------------------------------------------ کاتی نۆرەی ئۆنلاین

    public function saveOnlineWindow(AuditService $audit): void
    {
        $this->authorize('technicians.manage');

        $this->validate([
            'online_start' => ['required', 'date_format:H:i'],
            'online_end' => ['required', 'date_format:H:i', 'after:online_start'],
        ], ['online_end.after' => 'کاتی کۆتایی دەبێت دوای کاتی دەستپێک بێت.']);

        SystemSetting::put('booking.online_start', $this->online_start, 'booking');
        SystemSetting::put('booking.online_end', $this->online_end, 'booking');
        $audit->record(Auth::user(), 'settings.updated', null, [], ['online_start' => $this->online_start, 'online_end' => $this->online_end]);

        $this->dispatch('flash', message: 'کاتی نۆرەی ئۆنلاین پاشەکەوتکرا.', type: 'ok');
    }

    public function render()
    {
        $technicians = User::technicians()
            ->with(['branch', 'skills', 'schedules', 'timeOff' => fn ($q) => $q->whereDate('date', '>=', today())->orderBy('date')->with('shift')])
            ->orderBy('name')
            ->get();

        return view('livewire.scheduling.technicians', [
            'technicians' => $technicians,
            'shifts' => Shift::orderBy('start_time')->get(),
            'weekdays' => TechnicianSchedule::WEEKDAYS,
            'skills' => config('scheduling.skills'),
        ]);
    }
}
