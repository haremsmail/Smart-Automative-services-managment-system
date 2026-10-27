<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDiagnostic extends Model
{
    protected $fillable = [
        'vehicle_id', 'work_order_id', 'scanner_device',
        'dtc_code', 'description', 'severity', 'status', 'scanned_at'
    ];

    protected function casts(): array
    {
        return ['scanned_at' => 'datetime'];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}