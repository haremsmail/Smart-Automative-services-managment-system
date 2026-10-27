<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'customer_id', 'vehicle_id', 'branch_id', 'bay_id',
        'scheduled_at', 'status', 'requested_services', 'customer_notes', 'internal_notes'
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bay(): BelongsTo
    {
        return $this->belongsTo(ServiceBay::class, 'bay_id');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }
}