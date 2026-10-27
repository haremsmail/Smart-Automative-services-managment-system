<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCatalog extends Model
{
    protected $table = 'service_catalog';
    protected $fillable = [
        'category', 'name', 'description', 'standard_hours', 'base_price', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'standard_hours' => 'decimal:2',
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}