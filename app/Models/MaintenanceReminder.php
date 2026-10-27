<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceReminder extends Model
{
    protected $fillable = [
        'vehicle_id', 'customer_id', 'service_type', 'due_date',
        'due_mileage', 'status', 'sent_at'
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'due_mileage' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}