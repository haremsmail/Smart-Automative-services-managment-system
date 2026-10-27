<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartCategory extends Model
{
    protected $table = 'part_categories';

    protected $fillable = ['parent_id', 'name', 'slug'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PartCategory::class, 'parent_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class, 'category_id');
    }
}