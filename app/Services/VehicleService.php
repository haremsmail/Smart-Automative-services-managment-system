<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\Normalize;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * هەموو گۆڕانکارییەکانی ئۆتۆمبێل لێرەدایە.
 * یاساکان: کیلۆمەتر کەم نابێتەوە، خاوەن تەنها بە گواستنەوە دەگۆڕێت، مێژوو هەرگیز ناسڕدرێتەوە.
 */
class VehicleService
{
    private const AUDITED = [
        'vin', 'plate_number', 'make', 'model', 'year', 'color',
        'engine_type', 'transmission', 'current_mileage', 'notes',
    ];

    public function __construct(private AuditService $audit) {}

    public function create(User $actor, Customer $owner, array $data): Vehicle
    {
        $this->authorize($actor, 'vehicles.create');

        if ($owner->trashed()) {
            throw new DomainException('ناتوانرێت ئۆتۆمبێل بۆ کڕیاری ئەرشیفکراو زیاد بکرێت.');
        }

        return DB::transaction(function () use ($actor, $owner, $data) {
            $vehicle = $owner->vehicles()->create($this->prepare($data));
            $this->audit->record($actor, 'vehicle.created', $vehicle, [], $vehicle->only(self::AUDITED) + ['owner' => $owner->full_name]);

            return $vehicle;
        });
    }

    public function update(User $actor, Vehicle $vehicle, array $data): Vehicle
    {
        $this->authorize($actor, 'vehicles.update');

        if ($vehicle->trashed()) {
            throw new DomainException('ئەم ئۆتۆمبێلە ئەرشیف کراوە. پێشتر بیگەڕێنەوە.');
        }

        unset($data['customer_id']); // خاوەن تەنها بە transfer دەگۆڕێت

        if (($data['current_mileage'] ?? '') === '' || $data['current_mileage'] === null) {
            unset($data['current_mileage']); // خانەی بەتاڵ = هیچ گۆڕانێک نییە
        }

        return DB::transaction(function () use ($actor, $vehicle, $data) {
            $vehicle = Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();
            $data = $this->prepare($data);

            $this->guardMileage($vehicle, (int) ($data['current_mileage'] ?? $vehicle->current_mileage));

            $old = $vehicle->only(self::AUDITED);
            $vehicle->update($data);
            $changes = array_diff_assoc(array_map('strval', $vehicle->only(self::AUDITED)), array_map('strval', $old));

            if ($changes) {
                $this->audit->record($actor, 'vehicle.updated', $vehicle, array_intersect_key($old, $changes), $changes);
            }

            return $vehicle;
        });
    }

    /**
     * گواستنەوەی خاوەندارێتی (فرۆشتنی ئۆتۆمبێل). مێژووی چاککردنەوە لەگەڵ ئۆتۆمبێلەکە دەمێنێتەوە.
     */
    public function transfer(User $actor, Vehicle $vehicle, Customer $newOwner, ?int $mileage = null, ?string $note = null): Vehicle
    {
        $this->authorize($actor, 'vehicles.transfer');

        if ($vehicle->trashed()) {
            throw new DomainException('ئۆتۆمبێلی ئەرشیفکراو ناگوازرێتەوە.');
        }
        if ($newOwner->trashed()) {
            throw new DomainException('ناتوانرێت بگوازرێتەوە بۆ کڕیاری ئەرشیفکراو.');
        }
        if ((int) $vehicle->customer_id === (int) $newOwner->id) {
            throw new DomainException('ئەم کڕیارە پێشتر خاوەنی ئەم ئۆتۆمبێلەیە.');
        }

        return DB::transaction(function () use ($actor, $vehicle, $newOwner, $mileage, $note) {
            $vehicle = Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();
            $oldOwner = $vehicle->customer;

            $changes = ['customer_id' => $newOwner->id];

            if ($mileage !== null) {
                $this->guardMileage($vehicle, $mileage);
                $changes['current_mileage'] = $mileage;
            }

            $vehicle->forceFill($changes)->save();

            $this->audit->record($actor, 'vehicle.transferred', $vehicle,
                ['owner' => $oldOwner?->full_name, 'owner_id' => $oldOwner?->id],
                array_filter(['owner' => $newOwner->full_name, 'owner_id' => $newOwner->id, 'mileage' => $mileage, 'note' => $note], fn ($v) => $v !== null && $v !== ''),
            );

            return $vehicle;
        });
    }

    public function archive(User $actor, Vehicle $vehicle): void
    {
        $this->authorize($actor, 'vehicles.delete');

        DB::transaction(function () use ($actor, $vehicle) {
            $vehicle->delete();
            $this->audit->record($actor, 'vehicle.archived', $vehicle, ['plate_number' => $vehicle->plate_number], []);
        });
    }

    public function restore(User $actor, Vehicle $vehicle): void
    {
        $this->authorize($actor, 'vehicles.delete');

        if ($vehicle->customer?->trashed()) {
            throw new DomainException('خاوەنی ئەم ئۆتۆمبێلە ئەرشیف کراوە. پێشتر کڕیارەکە بگەڕێنەوە.');
        }

        DB::transaction(function () use ($actor, $vehicle) {
            $vehicle->restore();
            $this->audit->record($actor, 'vehicle.restored', $vehicle, [], ['plate_number' => $vehicle->plate_number]);
        });
    }

    /**
     * ئۆتۆمبێلێکی تر بە هەمان تابلۆ (بەبێ گوێدانە بۆشایی). بۆ ئاگادارکردنەوە.
     */
    public function findDuplicatePlate(?string $plate, ?int $ignoreId = null): ?Vehicle
    {
        $key = Normalize::plateKey($plate);

        if ($key === '') {
            return null;
        }

        return Vehicle::withTrashed()
            ->with('customer')
            ->whereRaw("UPPER(REPLACE(REPLACE(plate_number, ' ', ''), '-', '')) = ?", [$key])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->first();
    }

    /**
     * مێژووی دیجیتاڵی چاککردنەوە: کارنامە، پشکنین و گەرەنتی، بە ڕیزبەندی کات (نوێترین سەرەتا).
     * کاتێک مۆدیوڵەکانی کارنامە و پشکنین دروست کران، ئەم Timeline ـە خۆکارانە پڕ دەبێتەوە.
     *
     * @return Collection<int, array{type:string, date:\Illuminate\Support\Carbon, title:string, status:?string, mileage:?int, details:?string, extra:?string}>
     */
    public function timeline(Vehicle $vehicle): Collection
    {
        $workOrders = $vehicle->workOrders()->with(['customer', 'advisor', 'branch'])->latest()->get()
            ->map(fn ($wo) => [
                'type' => 'work_order',
                'date' => $wo->created_at,
                'title' => "کارنامە {$wo->wo_number}",
                'status' => $wo->status,
                'mileage' => $wo->mileage_in,
                'details' => collect([$wo->customer_complaint, $wo->diagnosis])->filter()->implode(' — ') ?: null,
                'extra' => collect([$wo->branch?->name, $wo->advisor?->name, $wo->customer?->full_name])->filter()->implode(' · ') ?: null,
            ]);

        $inspections = $vehicle->inspections()->with('inspector')->latest()->get()
            ->map(fn ($in) => [
                'type' => 'inspection',
                'date' => $in->created_at,
                'title' => 'پشکنینی دیجیتاڵی',
                'status' => $in->status,
                'mileage' => $in->odometer_reading,
                'details' => $in->summary,
                'extra' => $in->inspector?->name,
            ]);

        $warranties = $vehicle->warranties()->latest()->get()
            ->map(fn ($w) => [
                'type' => 'warranty',
                'date' => $w->created_at,
                'title' => 'گەرەنتی',
                'status' => $w->status,
                'mileage' => $w->mileage_limit,
                'details' => $w->terms,
                'extra' => optional($w->start_date)->format('Y-m-d').' → '.optional($w->end_date)->format('Y-m-d'),
            ]);

        return $workOrders->concat($inspections)->concat($warranties)->sortByDesc('date')->values();
    }

    private function guardMileage(Vehicle $vehicle, int $newMileage): void
    {
        if ($newMileage < (int) $vehicle->current_mileage) {
            throw new DomainException(
                'کیلۆمەتر ناتوانێت کەم بێتەوە. ئێستا: '.number_format($vehicle->current_mileage).' کم.'
            );
        }
    }

    private function prepare(array $data): array
    {
        if (array_key_exists('plate_number', $data)) {
            $data['plate_number'] = Normalize::plate($data['plate_number']);
        }
        if (array_key_exists('vin', $data)) {
            $data['vin'] = Normalize::vin($data['vin']);
        }

        foreach (['year', 'color', 'engine_type', 'transmission', 'notes'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if (array_key_exists('current_mileage', $data)) {
            $data['current_mileage'] = (int) ($data['current_mileage'] ?: 0);
        }

        foreach (['make', 'model', 'color'] as $field) {
            if (! empty($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        return $data;
    }

    private function authorize(User $actor, string $permission): void
    {
        if (! $actor->hasPermission($permission)) {
            throw new AuthorizationException('مۆڵەتت نییە.');
        }
    }
}
