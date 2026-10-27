<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCard extends Model
{
    protected $fillable = [
        'work_order_id', 'service_id', 'technician_id',
        'title', 'instructions', 'flat_rate_hours', 'status', 'completed_at'
    ];

    protected function casts(): array
    {
        return [
            'flat_rate_hours' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(JobTimeLog::class);
    }
}