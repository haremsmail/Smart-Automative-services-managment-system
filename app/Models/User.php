<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * role_id لێرە نییە. ڕۆڵ تەنها لە ڕێگەی AccessService ەوە دەگۆڕدرێت،
     * بۆ ئەوەی کارمەند نەتوانێت خۆی بکات بە بەڕێوەبەر.
     */
    protected $fillable = [
        'branch_id', 'name', 'email', 'phone',
        'password', 'hourly_rate', 'status', 'avatar',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function extraPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('granted_by')
            ->withTimestamps();
    }

    public function jobCards(): HasMany
    {
        return $this->hasMany(JobCard::class, 'technician_id');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(JobTimeLog::class, 'technician_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(TechnicianSkill::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(TechnicianSchedule::class);
    }

    public function timeOff(): HasMany
    {
        return $this->hasMany(TechnicianTimeOff::class);
    }

    public function assignedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'technician_id');
    }

    /** وەستا چالاکەکان (ڕۆڵی technician) */
    public function scopeTechnicians(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->where('name', 'technician'));
    }

    /**
     * یەکەم پەڕە کە ئەم کارمەندە دەسەڵاتی هەیە، بۆ پاش چوونەژوورەوە.
     * ڕیزبەندی بە پێی گرنگی کاری ڕۆژانەیە. null = هیچ دەسەڵاتێکی نییە.
     */
    public function homeRoute(): ?string
    {
        $pages = [
            'appointments.view' => 'appointments.board',
            'customers.view' => 'customers.index',
            'vehicles.view' => 'vehicles.index',
            'users.view' => 'users.index',
            'technicians.manage' => 'technicians.index',
            'roles.manage' => 'roles.index',
            'branches.manage' => 'branches.index',
            'audit.view' => 'audit.index',
        ];

        foreach ($pages as $permission => $route) {
            if ($this->hasPermission($permission)) {
                return $route;
            }
        }

        return null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * ئایا ئەم کارمەندە ئەم دەسەڵاتەی هەیە؟
     * لە ڕۆڵەکەی یان لە دەسەڵاتی تاکەکەسییەکەی دەگەڕێت.
     */
    public function hasPermission(string $name): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $fromRole = $this->role
            ? $this->role->permissions()->where('permissions.name', $name)->exists()
            : false;

        return $fromRole
            || $this->extraPermissions()->where('permissions.name', $name)->exists();
    }
}

