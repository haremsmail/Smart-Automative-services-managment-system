<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcCheck extends Model
{
    protected $fillable = [
        'work_order_id', 'inspector_id', 'road_test_done', 'passed',
        'findings', 'notes', 'checked_at'
    ];

    protected function casts(): array
    {
        return [
            'road_test_done' => 'boolean',
            'passed' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}