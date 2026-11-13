<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ServiceBay;
use App\Models\ServiceCatalog;
use App\Models\Shift;
use App\Models\SystemSetting;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * مێشکی نۆرەبڕین:
 *  - شەفتی وەستاکان (ڕۆژ، شەو، تەواو + مۆڵەت)
 *  - کاتە بەتاڵەکان (شوێنی کار + وەستا + ماوە)
 *  - AI Dispatch: خاڵدانی وەستاکان بە پێی شارەزایی، بەردەستبوون، بارکار و لق
 *
 * هەموو شتێک لە میمۆری حساب دەکرێت بۆ ئەوەی خێرا بێت (یەک query بۆ هەر جۆرە داتایەک).
 */
class SchedulingService
{
    public function slotMinutes(): int
    {
        return (int) config('scheduling.slot_minutes', 30);
    }

    /** کاتی نۆرەی ئۆنلاین کە بەڕێوەبەر دیاری کردووە */
    public function onlineWindow(): array
    {
        return [
            SystemSetting::get('booking.online_start', config('scheduling.online_start')),
            SystemSetting::get('booking.online_end', config('scheduling.online_end')),
        ];
    }

    // ------------------------------------------------------------------
    // پێداویستییەکانی نۆرە
    // ------------------------------------------------------------------

    /**
     * لە لیستی خزمەتگوزارییەکانەوە: ماوە، شارەزایی پێویست و جۆری شوێن.
     *
     * @param  Collection<int, ServiceCatalog>  $services
     * @return array{minutes:int, skills:array<int,string>, bay_type:string}
     */
    public function requirements(Collection $services): array
    {
        if ($services->isEmpty()) {
            throw new DomainException('لانیکەم یەک خزمەتگوزاری هەڵبژێرە.');
        }

        $slot = $this->slotMinutes();
        $minutes = max($slot, (int) ceil($services->sum(fn ($s) => $s->minutes()) / $slot) * $slot);

        $special = $services->pluck('required_bay_type')->filter(fn ($t) => $t && $t !== 'general')->unique()->values();

        if ($special->count() > 1) {
            throw new DomainException('ئەم خزمەتگوزارییانە پێویستیان بە دوو جۆری شوێنی جیاواز هەیە. تکایە بیانکە بە دوو نۆرە.');
        }

        return [
            'minutes' => $minutes,
            'skills' => $services->pluck('skill')->filter()->unique()->values()->all(),
            'bay_type' => $special->first() ?? 'general',
        ];
    }

    // ------------------------------------------------------------------
    // داتا (یەکجار بار دەکرێت)
    // ------------------------------------------------------------------

    /** وەستا چالاکەکانی لقێک (یان ئەوانەی لقیان دیاری نەکراوە) */
    public function technicians(?int $branchId): Collection
    {
        return User::technicians()
            ->with([
                'skills',
                'schedules.shift',
                'timeOff' => fn ($q) => $q->whereDate('date', '>=', now()->subDays(2)->toDateString())->with('shift'),
            ])
            ->when($branchId, fn ($q) => $q->where(fn ($w) => $w->whereNull('branch_id')->orWhere('branch_id', $branchId)))
            ->orderBy('name')
            ->get();
    }

    public function compatibleBays(?int $branchId, string $bayType): Collection
    {
        return ServiceBay::query()
            ->where('status', '!=', 'maintenance')
            ->where('bay_type', $bayType)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();
    }

    /** نۆرە چالاکەکان لە ماوەیەکدا */
    public function busy(Carbon $from, Carbon $to, ?int $ignoreId = null): Collection
    {
        return Appointment::active()
            ->overlapping($from, $to)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->get(['id', 'bay_id', 'technician_id', 'vehicle_id', 'scheduled_at', 'ends_at', 'duration_minutes']);
    }

    // ------------------------------------------------------------------
    // شەفت
    // ------------------------------------------------------------------

    /** شەفتی وەستا لە ڕۆژێکدا (مۆڵەت یان شەفتی جێگرەوە لە پێشترە) */
    public function shiftFor(User $tech, Carbon $day): ?Shift
    {
        $date = $day->toDateString();
        $override = $tech->timeOff->first(fn ($t) => $t->date->toDateString() === $date);

        if ($override) {
            return $override->shift?->is_active ? $override->shift : null;
        }

        $shift = $tech->schedules->firstWhere('weekday', $day->dayOfWeek)?->shift;

        return $shift?->is_active ? $shift : null;
    }

    /**
     * ماوەکانی کارکردن کە دەکرێت ڕۆژی $date بگرنەوە.
     * شەفتی شەوی دوێنێ (بۆ نموونە ٢٠:٠٠ – ٠٤:٠٠) بەشێکی ئەمڕۆش دەگرێتەوە.
     */
    public function shiftWindows(User $tech, Carbon $date): array
    {
        $windows = [];

        foreach ([$date->copy()->subDay(), $date->copy()] as $day) {
            if ($shift = $this->shiftFor($tech, $day)) {
                $windows[] = $shift->windowOn($day);
            }
        }

        return $windows;
    }

    public function onShift(User $tech, Carbon $start, Carbon $end): bool
    {
        foreach ($this->shiftWindows($tech, $start->copy()->startOfDay()) as [$from, $to]) {
            if ($start->gte($from) && $end->lte($to)) {
                return true;
            }
        }

        return false;
    }

    public function techFree(User $tech, Carbon $start, Carbon $end, Collection $busy): bool
    {
        return ! $busy->contains(fn ($a) => (int) $a->technician_id === $tech->id
            && $a->scheduled_at->lt($end) && $a->ends_at->gt($start));
    }

    public function bayFree(ServiceBay $bay, Carbon $start, Carbon $end, Collection $busy): bool
    {
        return ! $busy->contains(fn ($a) => (int) $a->bay_id === $bay->id
            && $a->scheduled_at->lt($end) && $a->ends_at->gt($start));
    }

    // ------------------------------------------------------------------
    // کاتە بەتاڵەکان
    // ------------------------------------------------------------------

    /**
     * هەموو کاتە بەتاڵەکانی ڕۆژێک. کاتێک بەتاڵە ئەگەر:
     * شوێنێکی گونجاو بەتاڵ بێت + لانیکەم یەک وەستا لە شەفتدا و بەتاڵ بێت.
     *
     * @return array<int, array{time:string, start:Carbon, technicians:int, bays:int}>
     */
    public function availableSlots(?int $branchId, Carbon $date, int $minutes, string $bayType, bool $online = false, ?int $ignoreId = null): array
    {
        $bays = $this->compatibleBays($branchId, $bayType);
        if ($bays->isEmpty()) {
            return [];
        }

        $techs = $this->technicians($branchId);
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $dayStart->copy()->addDay();
        $busy = $this->busy($dayStart, $dayEnd->copy()->addMinutes($minutes), $ignoreId);
        $now = now();

        [$onlineStart, $onlineEnd] = $this->onlineWindow();
        $slots = [];

        for ($start = $dayStart->copy(); $start->lt($dayEnd); $start->addMinutes($this->slotMinutes())) {
            $end = $start->copy()->addMinutes($minutes);

            if ($start->lt($now)) {
                continue;
            }
            if ($online && ($start->format('H:i') < $onlineStart || $end->format('H:i') > $onlineEnd || ! $end->isSameDay($start))) {
                continue;
            }

            $freeBays = $bays->filter(fn ($bay) => $this->bayFree($bay, $start, $end, $busy))->count();
            if ($freeBays === 0) {
                continue;
            }

            $freeTechs = $techs->filter(fn ($t) => $this->onShift($t, $start, $end) && $this->techFree($t, $start, $end, $busy))->count();
            if ($freeTechs === 0) {
                continue;
            }

            $slots[] = ['time' => $start->format('H:i'), 'start' => $start->copy(), 'technicians' => $freeTechs, 'bays' => $freeBays];
        }

        return $slots;
    }

    /**
     * نزیکترین کاتە بەتاڵەکان (پێشنیاری زیرەک).
     *
     * @return array<int, array{time:string, start:Carbon, technicians:int, bays:int}>
     */
    public function suggestSlots(?int $branchId, int $minutes, string $bayType, int $count = 3, bool $online = false): array
    {
        $found = [];
        $days = (int) config('scheduling.online_days_ahead', 14);

        for ($i = 0; $i < $days && count($found) < $count; $i++) {
            foreach ($this->availableSlots($branchId, now()->addDays($i), $minutes, $bayType, $online) as $slot) {
                $found[] = $slot;
                if (count($found) >= $count) {
                    break;
                }
            }
        }

        return $found;
    }

    public function pickBay(?int $branchId, string $bayType, Carbon $start, Carbon $end, Collection $busy): ?ServiceBay
    {
        return $this->compatibleBays($branchId, $bayType)
            ->first(fn ($bay) => $this->bayFree($bay, $start, $end, $busy));
    }

    // ------------------------------------------------------------------
    // AI Dispatch
    // ------------------------------------------------------------------

    /**
     * هەموو وەستاکان خاڵ دەدرێن و ڕیز دەکرێن. هەر خاڵێک ڕوونکردنەوەی خۆی هەیە (Explainable).
     *
     * @return Collection<int, array{user:User, score:int, available:bool, reasons:array}>
     */
    public function rankTechnicians(?int $branchId, Carbon $start, Carbon $end, array $skills, ?int $ignoreId = null): Collection
    {
        $w = config('scheduling.dispatch_weights');
        $skillLabels = config('scheduling.skills');
        $fullDay = (int) config('scheduling.full_day_minutes', 480);

        $busy = $this->busy($start, $end, $ignoreId);
        $dayLoad = Appointment::active()
            ->whereBetween('scheduled_at', [$start->copy()->startOfDay(), $start->copy()->endOfDay()])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereNotNull('technician_id')
            ->get(['technician_id', 'duration_minutes'])
            ->groupBy('technician_id')
            ->map(fn ($rows) => $rows->sum('duration_minutes'));

        return $this->technicians($branchId)->map(function (User $tech) use ($w, $skillLabels, $fullDay, $busy, $dayLoad, $skills, $start, $end, $branchId) {
            $reasons = [];

            // ١. شارەزایی
            if (empty($skills)) {
                $skillScore = $w['skill'];
                $reasons[] = ['ok' => true, 'text' => 'هیچ شارەزایی تایبەتی پێویست نییە', 'points' => $skillScore, 'max' => $w['skill']];
            } else {
                $levels = [];
                $parts = [];
                foreach ($skills as $skill) {
                    $level = (int) ($tech->skills->firstWhere('skill', $skill)?->level ?? 0);
                    $levels[] = $level;
                    $parts[] = ($skillLabels[$skill] ?? $skill).': '.($level ? str_repeat('⭐', $level) : 'شارەزایی نییە');
                }
                $skillScore = (int) round($w['skill'] * (array_sum($levels) / (count($levels) * 5)));
                $reasons[] = ['ok' => $skillScore >= $w['skill'] * 0.8 ? true : ($skillScore > 0 ? 'partial' : false), 'text' => implode(' · ', $parts), 'points' => $skillScore, 'max' => $w['skill']];
            }

            // ٢. بەردەستبوون
            $onShift = $this->onShift($tech, $start, $end);
            $free = $this->techFree($tech, $start, $end, $busy);
            $available = $onShift && $free;
            $reasons[] = [
                'ok' => $available,
                'text' => ! $onShift ? 'لەو کاتەدا لە شەفتدا نییە' : (! $free ? 'لەو کاتەدا نۆرەیەکی تری هەیە' : 'لە '.$start->format('H:i').'–'.$end->format('H:i').' بەتاڵە'),
                'points' => $available ? $w['availability'] : 0,
                'max' => $w['availability'],
            ];

            // ٣. دابەشکردنی دادپەروەرانە (ئەوەی کاری کەمترە، خاڵی زیاترە)
            $loaded = (int) ($dayLoad[$tech->id] ?? 0);
            $workloadScore = (int) round($w['workload'] * max(0, 1 - $loaded / $fullDay));
            $reasons[] = [
                'ok' => $workloadScore >= $w['workload'] * 0.7 ? true : 'partial',
                'text' => $loaded ? 'ئەو ڕۆژە '.round($loaded / 60, 1).' کاتژمێر کاری هەیە' : 'ئەو ڕۆژە هیچ کاری نییە',
                'points' => $workloadScore,
                'max' => $w['workload'],
            ];

            // ٤. لق
            $sameBranch = $branchId && (int) $tech->branch_id === (int) $branchId;
            $branchScore = $sameBranch ? $w['branch'] : (int) round($w['branch'] / 2);
            $reasons[] = ['ok' => $sameBranch ?: 'partial', 'text' => $sameBranch ? 'هەمان لق' : 'لە هەموو لقەکان کاردەکات', 'points' => $branchScore, 'max' => $w['branch']];

            return [
                'user' => $tech,
                'available' => $available,
                'score' => $available ? $skillScore + $w['availability'] + $workloadScore + $branchScore : 0,
                'reasons' => $reasons,
            ];
        })
            ->sortBy([['available', 'desc'], ['score', 'desc']])
            ->values();
    }
}
