<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\ServiceBay;
use App\Services\SchedulingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * تابلۆی ڕۆژانەی وۆرکشۆپ: ستوون = شوێنی کار، ڕیز = کاتژمێر.
 * لەگەڵ نەخشەی گەرمی هەفتە و لیستی نۆرە ئۆنلاینە چاوەڕێکراوەکان.
 */
#[Layout('components.layouts.app')]
#[Title('تابلۆی وۆرکشۆپ')]
class Board extends Component
{
    #[Url]
    public string $date = '';

    #[Url(as: 'branch')]
    public string $branch_id = '';

    public function mount(): void
    {
        $this->authorize('appointments.view');
        $this->date = $this->date ?: today()->toDateString();
        $this->branch_id = $this->branch_id ?: (string) (Auth::user()->branch_id ?? Branch::where('is_active', true)->value('id') ?? '');
    }

    public function shiftDay(int $days): void
    {
        $this->date = Carbon::parse($this->date)->addDays($days)->toDateString();
    }

    public function today(): void
    {
        $this->date = today()->toDateString();
    }

    /** ماوەی کارکردنی ڕۆژێک: لە یەکەم شەفتەوە تا دوایین شەفت (ئەگەر شەفت نەبوو: ٠٨–١٨) */
    private function dayWindow(SchedulingService $scheduling, $techs, Carbon $day): array
    {
        $dayStart = $day->copy()->startOfDay();
        $dayEnd = $dayStart->copy()->addDay();
        $from = null;
        $to = null;

        foreach ($techs as $tech) {
            foreach ($scheduling->shiftWindows($tech, $day) as [$s, $e]) {
                $s = $s->max($dayStart);
                $e = $e->min($dayEnd);
                if ($s->lt($e)) {
                    $from = $from ? $from->min($s) : $s;
                    $to = $to ? $to->max($e) : $e;
                }
            }
        }

        return [$from ?? $dayStart->copy()->setTime(8, 0), $to ?? $dayStart->copy()->setTime(18, 0)];
    }

    public function render(SchedulingService $scheduling)
    {
        $branchId = $this->branch_id !== '' ? (int) $this->branch_id : null;
        $day = Carbon::parse($this->date)->startOfDay();
        $slot = $scheduling->slotMinutes();
        $techs = $scheduling->technicians($branchId);

        $bays = ServiceBay::when($branchId, fn ($q) => $q->where('branch_id', $branchId))->orderBy('name')->get();

        $appointments = Appointment::with(['customer', 'vehicle', 'technician', 'items'])
            ->whereNotIn('status', ['cancelled'])
            ->overlapping($day, $day->copy()->addDay())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('scheduled_at')
            ->get();

        [$from, $to] = $this->dayWindow($scheduling, $techs, $day);
        foreach ($appointments as $a) {
            $from = $from->min($a->scheduled_at->max($day));
            $to = $to->max($a->ends_at->min($day->copy()->addDay()));
        }

        // خشتە: [bay_id][slot_index] = appointment | 'covered' | null
        $rows = [];
        for ($t = $from->copy(); $t->lt($to); $t->addMinutes($slot)) {
            $rows[] = $t->copy();
        }
        $grid = [];
        $spans = [];
        foreach ($appointments as $a) {
            foreach ($rows as $i => $rowTime) {
                $rowEnd = $rowTime->copy()->addMinutes($slot);
                if ($a->scheduled_at->lt($rowEnd) && $a->ends_at->gt($rowTime) && ! isset($grid[$a->bay_id][$i])) {
                    $grid[$a->bay_id][$i] = isset($spans[$a->id]) ? 'covered' : $a;
                    $spans[$a->id] = ($spans[$a->id] ?? 0) + 1;
                }
            }
        }

        // نەخشەی گەرمی ٧ ڕۆژ (پڕبوون بە ٪)
        $usableBays = $bays->filter->isUsable()->count();
        $heat = [];
        for ($i = 0; $i < 7; $i++) {
            $d = today()->addDays($i);
            [$ws, $we] = $this->dayWindow($scheduling, $techs, $d);
            $capacity = max(1, $usableBays * $ws->diffInMinutes($we));
            $booked = Appointment::active()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereBetween('scheduled_at', [$d->copy()->startOfDay(), $d->copy()->endOfDay()])->sum('duration_minutes');
            $heat[] = ['date' => $d, 'percent' => (int) min(100, round($booked / $capacity * 100)), 'count' => Appointment::active()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereDate('scheduled_at', $d)->count()];
        }

        return view('livewire.appointments.board', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'bays' => $bays,
            'rows' => $rows,
            'grid' => $grid,
            'spans' => $spans,
            'slot' => $slot,
            'day' => $day,
            'appointments' => $appointments,
            'pending' => Appointment::with(['customer', 'vehicle'])->where('status', 'pending')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->orderBy('scheduled_at')->limit(10)->get(),
            'heat' => $heat,
            'onShift' => $techs->filter(fn ($t) => $scheduling->shiftFor($t, $day))->values(),
        ]);
    }
}
