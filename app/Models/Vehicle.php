<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    public const ENGINE_TYPES = [
        'petrol' => 'بەنزین',
        'diesel' => 'گاز (دیزڵ)',
        'hybrid' => 'هایبرید',
        'electric' => 'کارەبایی',
        'lpg' => 'غاز (LPG)',
    ];

    public const TRANSMISSIONS = [
        'automatic' => 'ئۆتۆماتیک',
        'manual' => 'گێڕی دەستی',
        'cvt' => 'CVT',
    ];

    /**
     * customer_id لێرە هەیە بۆ دروستکردن، بەڵام گۆڕینی خاوەن تەنها لە ڕێگەی
     * VehicleService::transfer ەوە دەکرێت بۆ ئەوەی لە Audit تۆمار بکرێت.
     */
    protected $fillable = [
        'customer_id', 'vin', 'plate_number', 'make', 'model',
        'year', 'color', 'engine_type', 'transmission', 'current_mileage', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'current_mileage' => 'integer',
        ];
    }

    public function title(): string
    {
        return trim("{$this->make} {$this->model} {$this->year}");
    }

    public function engineLabel(): ?string
    {
        return $this->engine_type ? (self::ENGINE_TYPES[$this->engine_type] ?? $this->engine_type) : null;
    }

    public function transmissionLabel(): ?string
    {
        return $this->transmission ? (self::TRANSMISSIONS[$this->transmission] ?? $this->transmission) : null;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function diagnostics(): HasMany
    {
        return $this->hasMany(VehicleDiagnostic::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(MaintenanceReminder::class);
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }
}
