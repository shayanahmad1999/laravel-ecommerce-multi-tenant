<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;

/**
 * Tenant Model
 *
 * Represents a tenant in the multi-tenant e-commerce system.
 * Each tenant has its own domain, database, and isolated data.
 */
class Tenant extends SpatieTenant
{
    use UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'domain',
        'database',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure domain uniqueness
        static::creating(function ($tenant) {
            if (static::where('domain', $tenant->domain)->exists()) {
                throw new \Exception("Domain '{$tenant->domain}' is already taken.");
            }
        });

        static::updating(function ($tenant) {
            if ($tenant->isDirty('domain') &&
                static::where('domain', $tenant->domain)->where('id', '!=', $tenant->id)->exists()) {
                throw new \Exception("Domain '{$tenant->domain}' is already taken.");
            }
        });
    }

    /**
     * Get all users belonging to this tenant.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all orders for this tenant.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all products for this tenant.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all categories for this tenant.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get all payments for this tenant.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope to get only active tenants.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive tenants.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to find tenant by domain.
     *
     * @param Builder $query
     * @param string $domain
     * @return Builder
     */
    public function scopeByDomain(Builder $query, string $domain): Builder
    {
        return $query->where('domain', $domain);
    }

    /**
     * Check if tenant is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if tenant is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return !$this->is_active;
    }

    /**
     * Activate the tenant.
     *
     * @return bool
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the tenant.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Get tenant's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->domain;
    }

    /**
     * Get tenant statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'users_count' => $this->users()->count(),
            'orders_count' => $this->orders()->count(),
            'products_count' => $this->products()->count(),
            'categories_count' => $this->categories()->count(),
            'payments_count' => $this->payments()->count(),
        ];
    }

    /**
     * Get the full URL for the tenant's domain.
     *
     * @return string
     */
    public function getFullUrlAttribute(): string
    {
        $protocol = request()->secure() ? 'https://' : 'http://';
        return $protocol . $this->domain;
    }
}
