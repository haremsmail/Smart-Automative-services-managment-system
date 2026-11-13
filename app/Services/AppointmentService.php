<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\ServiceBay;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vehicle;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * هەموو گۆڕانکارییەکانی نۆرە لێرەدایە.
 * یاساکان: دوو نۆرە لە یەک شوێن/یەک وەستا/یەک ئۆتۆمبێل لە یەک کاتدا نابن،
 * وەستا دەبێت لە شەفتدا بێت، دۆخەکان بە ڕیز دەگۆڕێن، هەموو شتێک لە Audit.
 */
class AppointmentService
{
    public function __construct(
        private SchedulingService $scheduling,
        private AuditService $audit,
    ) {}

    /**
     * دروستکردنی نۆرە.
     *
     * $data: customer_id, vehicle_id, branch_id, service_ids[], scheduled_at, bay_id?, technician_id? ('auto' = AI),
     *        source, customer_notes?, internal_notes?
     * $actor = null تەنها بۆ نۆرەی ئۆنلاینی کڕیار (BookingService).
     */
    public function create(?User $actor, array $data): Appointment
    {
        if ($actor) {
            $this->authorize($actor, 'appointments.manage');
        }

        $customer = Customer::findOrFail($data['customer_id']);
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        if ((int) $vehicle->customer_id !== (int) $customer->id) {
            throw new DomainException('ئەم ئۆتۆمبێلە هی ئەم کڕیارە نییە.');
        }

        $services = ServiceCatalog::whereIn('id', $data['service_ids'] ?? [])->where('is_active', true)->get();
        $req = $this->scheduling->requirements($services);
        $start = $this->normalizeStart(Carbon::parse($data['scheduled_at']));
        $end = $start->copy()->addMinutes($req['minutes']);
        $branchId = $data['branch_id'] ?? null;

        return DB::transaction(function () use ($actor, $data, $customer, $vehicle, $services, $req, $start, $end, $branchId) {
            // قوفڵی شوێنەکانی کار: دوو کەس لە یەک کاتدا هەمان شوێن نەگرن
            ServiceBay::when($branchId, fn ($q) => $q->where('branch_id', $branchId))->lockForUpdate()->get();

            $busy = $this->scheduling->busy($start, $end);
            $this->guardVehicle($vehicle, $busy);

            $bay = $this->resolveBay($data['bay_id'] ?? null, $branchId, $req['bay_type'], $start, $end, $busy);
            $technicianId = $this->resolveTechnician($data['technician_id'] ?? null, $branchId, $start, $end, $req['skills'], $busy);

            $isOnline = ($data['source'] ?? null) === 'online';

            $appointment = Appointment::create([
                'public_token' => Str::random(40),
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'branch_id' => $branchId,
                'bay_id' => $bay->id,
                'technician_id' => $technicianId,
                'scheduled_at' => $start,
                'duration_minutes' => $req['minutes'],
                'ends_at' => $end,
                'status' => $isOnline ? 'pending' : 'scheduled',
                'source' => $data['source'] ?? 'phone',
                'requested_services' => $services->pluck('name')->implode('، '),
                'customer_notes' => $data['customer_notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by' => $actor?->id,
                'confirmed_at' => $isOnline ? null : now(),
            ]);

            $appointment->forceFill(['reference' => 'BK-'.str_pad((string) $appointment->id, 5, '0', STR_PAD_LEFT)])->save();

            foreach ($services as $service) {
                $appointment->items()->create([
                    'service_catalog_id' => $service->id,
                    'name' => $service->name,
                    'standard_hours' => $service->standard_hours,
                    'price' => $service->base_price,
                ]);
            }

            $this->audit->record($actor, 'appointment.created', $appointment, [], [
                'reference' => $appointment->reference,
                'scheduled_at' => $start->format('Y-m-d H:i'),
                'bay' => $bay->name,
                'technician_id' => $technicianId,
                'source' => $appointment->source,
            ]);

            return $appointment;
        });
    }

    /** گۆڕینی کات و شوێنی نۆرە (وەستاکەش دووبارە پشکنین دەکرێت) */
    public function reschedule(User $actor, Appointment $appointment, string $scheduledAt, ?int $bayId = null): Appointment
    {
        $this->authorize($actor, 'appointments.manage');

        if (! in_array($appointment->status, ['pending', 'scheduled'], true)) {
            throw new DomainException('تەنها نۆرەی «چاوەڕێ» یان «دانراو» دەگۆڕدرێت.');
        }

        $start = $this->normalizeStart(Carbon::parse($scheduledAt));
        $end = $start->copy()->addMinutes($appointment->duration_minutes);
        $bayType = $appointment->bay?->bay_type ?? 'general';

        return DB::transaction(function () use ($actor, $appointment, $start, $end, $bayId, $bayType) {
            ServiceBay::when($appointment->branch_id, fn ($q) => $q->where('branch_id', $appointment->branch_id))->lockForUpdate()->get();

            $busy = $this->scheduling->busy($start, $end, $appointment->id);
            $this->guardVehicle($appointment->vehicle, $busy);
            $bay = $this->resolveBay($bayId, $appointment->branch_id, $bayType, $start, $end, $busy);

            $technicianId = $appointment->technician_id;
            if ($technicianId) {
                $tech = $this->scheduling->technicians($appointment->branch_id)->firstWhere('id', $technicianId);
                if (! $tech || ! $this->scheduling->onShift($tech, $start, $end) || ! $this->scheduling->techFree($tech, $start, $end, $busy)) {
                    $technicianId = null; // وەستاکە لەو کاتەدا بەتاڵ نییە، دەبێت دووبارە بسپێردرێت
                }
            }

            $old = ['scheduled_at' => $appointment->scheduled_at->format('Y-m-d H:i'), 'bay' => $appointment->bay?->name, 'technician_id' => $appointment->technician_id];

            $appointment->forceFill([
                'scheduled_at' => $start,
                'ends_at' => $end,
                'bay_id' => $bay->id,
                'technician_id' => $technicianId,
            ])->save();

            $this->audit->record($actor, 'appointment.rescheduled', $appointment, $old, [
                'scheduled_at' => $start->format('Y-m-d H:i'), 'bay' => $bay->name, 'technician_id' => $technicianId,
            ]);

            return $appointment;
        });
    }

    /** سپاردنی وەستا (دەستی یان لە پێشنیاری AI) */
    public function assignTechnician(User $actor, Appointment $appointment, ?int $technicianId): void
    {
        $this->authorize($actor, 'appointments.manage');

        if (! $appointment->isActive()) {
            throw new DomainException('ئەم نۆرەیە چالاک نییە.');
        }

        DB::transaction(function () use ($actor, $appointment, $technicianId) {
            $busy = $this->scheduling->busy($appointment->scheduled_at, $appointment->ends_at, $appointment->id);
            $skills = $appointment->items()->with('service')->get()->pluck('service.skill')->filter()->unique()->values()->all();

            $resolved = $technicianId
                ? $this->resolveTechnician($technicianId, $appointment->branch_id, $appointment->scheduled_at, $appointment->ends_at, $skills, $busy)
                : null;

            $old = $appointment->technician?->name;
            $appointment->forceFill(['technician_id' => $resolved])->save();

            $this->audit->record($actor, 'appointment.assigned', $appointment, ['technician' => $old], ['technician' => $appointment->fresh('technician')->technician?->name]);
        });
    }

    public function changeStatus(User $actor, Appointment $appointment, string $status, ?string $reason = null): void
    {
        $this->authorize($actor, 'appointments.manage');

        if (! $appointment->canMoveTo($status)) {
            throw new DomainException('ناتوانرێت لە «'.$appointment->statusLabel().'» بگۆڕدرێت بۆ «'.(Appointment::STATUSES[$status] ?? $status).'».');
        }

        if ($status === 'no_show' && $appointment->scheduled_at->isFuture()) {
            throw new DomainException('کاتی نۆرەکە هێشتا نەهاتووە، ناتوانرێت «نەهات» تۆمار بکرێت.');
        }

        $old = $appointment->status;
        $changes = ['status' => $status];

        if ($status === 'scheduled' && ! $appointment->confirmed_at) {
            $changes['confirmed_at'] = now();
        }
        if ($status === 'cancelled') {
            $changes['cancel_reason'] = $reason;
        }

        $appointment->forceFill($changes)->save();
        $this->audit->record($actor, 'appointment.status', $appointment, ['status' => $old], array_filter(['status' => $status, 'reason' => $reason]));
    }

    /** کڕیار لە لینکی تایبەتی خۆیەوە نۆرەکەی هەڵدەوەشێنێتەوە */
    public function cancelByCustomer(Appointment $appointment): void
    {
        if (! in_array($appointment->status, ['pending', 'scheduled'], true)) {
            throw new DomainException('ئەم نۆرەیە ناتوانرێت هەڵبوەشێنرێتەوە.');
        }
        if ($appointment->scheduled_at->lt(now()->addHour())) {
            throw new DomainException('کەمتر لە کاتژمێرێک ماوە. تکایە پەیوەندی بە گەراجەوە بکە.');
        }

        $appointment->forceFill(['status' => 'cancelled', 'cancel_reason' => 'هەڵوەشاندنەوە لەلایەن کڕیارەوە'])->save();
        $this->audit->record(null, 'appointment.status', $appointment, ['status' => 'scheduled'], ['status' => 'cancelled', 'by' => 'customer']);
    }

    /** ژمارەی نەهاتنەکانی کڕیار (بۆ نیشانەی مەترسی) */
    public function noShowCount(int $customerId): int
    {
        return Appointment::where('customer_id', $customerId)->where('status', 'no_show')->count();
    }

    // ------------------------------------------------------------------

    private function normalizeStart(Carbon $start): Carbon
    {
        $slot = $this->scheduling->slotMinutes();

        if ($start->minute % $slot !== 0 || $start->second !== 0) {
            throw new DomainException("کاتی نۆرە دەبێت لەسەر خانەی {$slot} خولەکی بێت (بۆ نموونە 09:00 یان 09:30).");
        }
        if ($start->lt(now()->subMinutes($slot))) {
            throw new DomainException('ناتوانرێت نۆرە بۆ کاتی ڕابردوو دابنرێت.');
        }

        return $start->copy();
    }

    private function guardVehicle(Vehicle $vehicle, Collection $busy): void
    {
        if ($vehicle->trashed()) {
            throw new DomainException('ئەم ئۆتۆمبێلە ئەرشیف کراوە.');
        }
        if ($busy->contains(fn ($a) => (int) $a->vehicle_id === $vehicle->id)) {
            throw new DomainException('ئەم ئۆتۆمبێلە لەو کاتەدا نۆرەیەکی تری هەیە.');
        }
    }

    private function resolveBay(mixed $bayId, ?int $branchId, string $bayType, Carbon $start, Carbon $end, Collection $busy): ServiceBay
    {
        if ($bayId) {
            $bay = ServiceBay::findOrFail($bayId);

            if (! $bay->isUsable()) {
                throw new DomainException("شوێنی «{$bay->name}» لە چاککردنەوەدایە.");
            }
            if ($bay->bay_type !== $bayType) {
                throw new DomainException("ئەم خزمەتگوزارییە پێویستی بە شوێنی «".config("scheduling.bay_types.{$bayType}")."» هەیە، نەک «{$bay->typeLabel()}».");
            }
            if ($branchId && (int) $bay->branch_id !== (int) $branchId) {
                throw new DomainException('ئەم شوێنە هی ئەم لقە نییە.');
            }
            if (! $this->scheduling->bayFree($bay, $start, $end, $busy)) {
                throw new DomainException("شوێنی «{$bay->name}» لەو کاتەدا گیراوە.");
            }

            return $bay;
        }

        return $this->scheduling->pickBay($branchId, $bayType, $start, $end, $busy)
            ?? throw new DomainException('هیچ شوێنێکی کاری گونجاو لەو کاتەدا بەتاڵ نییە. کاتێکی تر هەڵبژێرە.');
    }

    private function resolveTechnician(mixed $technicianId, ?int $branchId, Carbon $start, Carbon $end, array $skills, Collection $busy): ?int
    {
        if (! $technicianId) {
            return null;
        }

        // AI: باشترین وەستای بەردەست
        if ($technicianId === 'auto') {
            $best = $this->scheduling->rankTechnicians($branchId, $start, $end, $skills)->firstWhere('available', true);

            return $best ? $best['user']->id : null;
        }

        $tech = $this->scheduling->technicians($branchId)->firstWhere('id', (int) $technicianId);

        if (! $tech) {
            throw new DomainException('ئەم کەسە وەستای چالاکی ئەم لقە نییە.');
        }
        if (! $this->scheduling->onShift($tech, $start, $end)) {
            throw new DomainException("{$tech->name} لەو کاتەدا لە شەفتدا نییە.");
        }
        if (! $this->scheduling->techFree($tech, $start, $end, $busy)) {
            throw new DomainException("{$tech->name} لەو کاتەدا نۆرەیەکی تری هەیە.");
        }

        return $tech->id;
    }

    private function authorize(User $actor, string $permission): void
    {
        if (! $actor->hasPermission($permission)) {
            throw new AuthorizationException('مۆڵەتت نییە.');
        }
    }
}
