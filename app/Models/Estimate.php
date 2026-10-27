<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estimate extends Model
{
    protected $fillable = [
        'estimate_number', 'work_order_id', 'customer_id', 'vehicle_id',
        'inspection_id', 'subtotal', 'tax_amount', 'discount_amount',
        'grand_total', 'status', 'public_token', 'customer_ip',
        'approved_at', 'expires_at', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EstimateLine::class);
    }
}