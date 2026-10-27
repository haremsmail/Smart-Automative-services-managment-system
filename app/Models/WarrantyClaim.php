<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaim extends Model
{
    protected $fillable = [
        'warranty_id', 'work_order_id', 'claim_date', 'defect_description',
        'reimbursement_amount', 'status', 'resolved_at', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'claim_date' => 'date',
            'reimbursement_amount' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}