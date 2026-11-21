<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceBay extends Model
{
    public const STATUSES = [
        'available' => 'بەردەست',
        'maintenance' => 'لە چاککردنەوەدا',
    ];

    protected $fillable = ['branch_id', 'name', 'bay_type', 'status'];

    public function isUsable(): bool
    {
        return $this->status !== 'maintenance';
    }

    public function typeLabel(): string
    {
        return config("scheduling.bay_types.{$this->bay_type}") ?? $this->bay_type;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'bay_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'bay_id');
    }
}
