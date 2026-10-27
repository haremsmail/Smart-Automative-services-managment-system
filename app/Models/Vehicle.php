<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'customer_id', 'vin', 'plate_number', 'make', 'model',
        'year', 'color', 'engine_type', 'transmission', 'current_mileage', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'current_mileage' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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