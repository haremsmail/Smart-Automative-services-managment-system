<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    protected $fillable = [
        'category_id', 'sku', 'barcode', 'oem_number', 'name',
        'description', 'unit', 'cost_price', 'selling_price',
        'min_stock_level', 'reorder_quantity', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'min_stock_level' => 'decimal:2',
            'reorder_quantity' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'category_id');
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'stock_levels')
            ->withPivot('quantity', 'bin_location')
            ->withTimestamps();
    }
}