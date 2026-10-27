<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id', 'line_type', 'service_id', 'part_id',
        'description', 'quantity', 'unit_price', 'tax_amount', 'subtotal'
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }
}