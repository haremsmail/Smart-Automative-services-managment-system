<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobTimeLog extends Model
{
    protected $fillable = [
        'job_card_id', 'technician_id', 'started_at', 'ended_at',
        'duration_minutes', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}