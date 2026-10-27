<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    protected $fillable = [
        'wo_number', 'branch_id', 'appointment_id', 'customer_id',
        'vehicle_id', 'bay_id', 'advisor_id', 'mileage_in', 'status',
        'customer_complaint', 'diagnosis', 'promised_at', 'closed_at'
    ];

    protected function casts(): array
    {
        return [
            'promised_at' => 'datetime',
            'closed_at' => 'datetime',
            'mileage_in' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function bay(): BelongsTo
    {
        return $this->belongsTo(ServiceBay::class, 'bay_id');
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function jobCards(): HasMany
    {
        return $this->hasMany(JobCard::class);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function qcCheck(): HasOne
    {
        return $this->hasOne(QcCheck::class);
    }

    public function diagnostics(): HasMany
    {
        return $this->hasMany(VehicleDiagnostic::class);
    }
}