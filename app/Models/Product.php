<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * Product Model
 *
 * Represents a product in the e-commerce system.
 * Handles inventory, pricing, and product information.
 */
class Product extends Model
{
    use UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'cost_price',
        'stock_quantity',
        'min_stock_level',
        'images',
        'is_active',
        'allow_installments',
        'max_installments',
        'installment_interest_rate',
        'category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'installment_interest_rate' => 'decimal:2',
        'images' => 'array',
        'is_active' => 'boolean',
        'allow_installments' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure SKU uniqueness within tenant
        static::creating(function ($product) {
            if (!$product->sku) {
                $product->sku = static::generateSKU();
            }
        });
    }

    /**
     * Generate a unique SKU.
     *
     * @return string
     */
    protected static function generateSKU(): string
    {
        do {
            $sku = 'PRD-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the order items for the product.
     *
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to get active products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get inactive products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to get products in stock.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope to get products with low stock.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
    }

    /**
     * Scope to get out of stock products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope to get products by category.
     *
     * @param Builder $query
     * @param int $categoryId
     * @return Builder
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to search products by name or SKU.
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Check if product is in stock for given quantity.
     *
     * @param int $quantity
     * @return bool
     */
    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Check if product has low stock.
     *
     * @return bool
     */
    public function hasLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    /**
     * Check if product is out of stock.
     *
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Decrease stock quantity.
     *
     * @param int $quantity
     * @return bool
     * @throws \Exception
     */
    public function decreaseStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        if (!$this->isInStock($quantity)) {
            throw new \Exception('Insufficient stock for product: ' . $this->name);
        }

        return $this->decrement('stock_quantity', $quantity);
    }

    /**
     * Increase stock quantity.
     *
     * @param int $quantity
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function increaseStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        return $this->increment('stock_quantity', $quantity);
    }

    /**
     * Set stock quantity.
     *
     * @param int $quantity
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function setStock(int $quantity): bool
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Stock quantity cannot be negative');
        }

        return $this->update(['stock_quantity' => $quantity]);
    }

    /**
     * Get the main product image.
     *
     * @return string|null
     */
    public function getMainImageAttribute(): ?string
    {
        return $this->images && count($this->images) > 0 ? $this->images[0] : null;
    }

    /**
     * Get all product images as array.
     *
     * @return array
     */
    public function getImageUrlsAttribute(): array
    {
        if (!$this->images) {
            return [];
        }

        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $this->images);
    }

    /**
     * Calculate installment amount.
     *
     * @param int $installments
     * @return float
     * @throws \InvalidArgumentException
     */
    public function calculateInstallmentAmount(int $installments): float
    {
        if (!$this->allow_installments) {
            throw new \InvalidArgumentException('Installments not allowed for this product');
        }

        if ($installments < 1 || $installments > $this->max_installments) {
            throw new \InvalidArgumentException('Invalid number of installments');
        }

        $principal = $this->price;
        $interestRate = $this->installment_interest_rate / 100;
        $totalAmount = $principal + ($principal * $interestRate);

        return round($totalAmount / $installments, 2);
    }

    /**
     * Get profit margin.
     *
     * @return float
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) {
            return 0;
        }

        return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    /**
     * Get formatted price.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get stock status text.
     *
     * @return string
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }

        if ($this->hasLowStock()) {
            return 'Low Stock';
        }

        return 'In Stock';
    }
}
