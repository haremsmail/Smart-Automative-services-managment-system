<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCatalog extends Model
{
    protected $table = 'service_catalog';

    protected $fillable = [
        'category', 'skill', 'required_bay_type', 'name', 'description',
        'standard_hours', 'base_price', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'standard_hours' => 'decimal:2',
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function minutes(): int
    {
        return (int) round((float) $this->standard_hours * 60);
    }

    public function skillLabel(): ?string
    {
        return $this->skill ? (config("scheduling.skills.{$this->skill}") ?? $this->skill) : null;
    }

    public function bayTypeLabel(): string
    {
        return config("scheduling.bay_types.{$this->required_bay_type}") ?? $this->required_bay_type;
    }
}
