<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warranty extends Model
{
    protected $table = 'warranties';

    protected $fillable = [
        'invoice_line_id', 'vehicle_id', 'start_date', 'end_date',
        'mileage_limit', 'terms', 'status'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'mileage_limit' => 'integer',
        ];
    }

    public function invoiceLine(): BelongsTo
    {
        return $this->belongsTo(InvoiceLine::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }
}