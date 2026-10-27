<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateLine extends Model
{
    protected $fillable = [
        'estimate_id', 'line_type', 'service_id', 'part_id',
        'description', 'quantity', 'unit_price', 'discount', 'subtotal',
        'approval_status', 'decided_at'
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}