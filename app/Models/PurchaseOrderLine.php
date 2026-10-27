<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model
{
    protected $fillable = [
        'purchase_order_id', 'part_id', 'ordered_quantity',
        'received_quantity', 'unit_cost', 'subtotal'
    ];

    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'decimal:2',
            'received_quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}