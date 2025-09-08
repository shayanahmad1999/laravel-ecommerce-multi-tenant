<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * User Model
 *
 * Represents a user in the multi-tenant e-commerce system.
 * Handles authentication, roles, permissions, and tenant relationships.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'tenant_id',
        'phone',
        'address',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'tenant_id', // Hide tenant_id for security
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set tenant_id when creating users in tenant context
        static::creating(function ($user) {
            if (app()->bound('currentTenant') && !$user->tenant_id) {
                $user->tenant_id = app('currentTenant')->id;
            }
        });
    }

    /**
     * Get the tenant that owns the user.
     *
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the orders for the user.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the payments for the user.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if user is a customer.
     *
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }

    /**
     * Check if user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    // public function hasRole(string $role): bool
    // {
    //     return $this->hasRole($role);
    // }

    /**
     * Check if user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Scope to get only admin users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    /**
     * Scope to get only customer users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustomers($query)
    {
        return $query->where('user_type', 'customer');
    }

    /**
     * Scope to get users by tenant.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $tenantId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Get user's full name or fallback to email.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->email;
    }

    /**
     * Get user's initials for avatars.
     *
     * @return string
     */
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name ?: $this->email);
        $initials = '';

        foreach ($parts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return substr($initials, 0, 2);
    }
}
