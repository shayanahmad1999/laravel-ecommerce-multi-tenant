<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Illuminate\Support\Str;

/**
 * Payment Model
 *
 * Represents a payment transaction in the e-commerce system.
 * Handles payment processing, status tracking, and gateway integration.
 */
class Payment extends Model
{
    use UsesTenantConnection;

    /**
     * Payment status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Payment method constants
     */
    const METHOD_CASH = 'cash';
    const METHOD_CARD = 'card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_DIGITAL_WALLET = 'digital_wallet';

    /**
     * Payment type constants
     */
    const TYPE_FULL = 'full';
    const TYPE_PARTIAL = 'partial';
    const TYPE_INSTALLMENT = 'installment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_number',
        'order_id',
        'user_id',
        'amount',
        'payment_method',
        'payment_type',
        'status',
        'transaction_id',
        'gateway_response',
        'payment_details',
        'processed_at',
        'refunded_at',
        'refund_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_number) {
                $payment->payment_number = static::generatePaymentNumber();
            }

            // Set default status if not provided
            if (!$payment->status) {
                $payment->status = self::STATUS_PENDING;
            }
        });
    }

    /**
     * Generate a unique payment number.
     *
     * @return string
     */
    protected static function generatePaymentNumber(): string
    {
        do {
            $paymentNumber = 'PAY-' . strtoupper(Str::random(8)) . '-' . time();
        } while (static::where('payment_number', $paymentNumber)->exists());

        return $paymentNumber;
    }

    /**
     * Get the order that owns the payment.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that owns the payment.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the installments for the payment.
     *
     * @return HasMany
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    /**
     * Get all available payment statuses.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }

    /**
     * Get all available payment methods.
     *
     * @return array
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_CARD => 'Card',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_DIGITAL_WALLET => 'Digital Wallet',
        ];
    }

    /**
     * Get all available payment types.
     *
     * @return array
     */
    public static function getPaymentTypes(): array
    {
        return [
            self::TYPE_FULL => 'Full Payment',
            self::TYPE_PARTIAL => 'Partial Payment',
            self::TYPE_INSTALLMENT => 'Installment',
        ];
    }

    /**
     * Scope to get completed payments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get pending payments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get failed payments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to get refunded payments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    /**
     * Scope to get payments by method.
     *
     * @param Builder $query
     * @param string $method
     * @return Builder
     */
    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope to get payments by user.
     *
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get payments by order.
     *
     * @param Builder $query
     * @param int $orderId
     * @return Builder
     */
    public function scopeByOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope to get payments within date range.
     *
     * @param Builder $query
     * @param string $from
     * @param string $to
     * @return Builder
     */
    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Check if payment is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment can be refunded.
     *
     * @return bool
     */
    public function canBeRefunded(): bool
    {
        return $this->isCompleted() && !$this->refunded_at;
    }

    /**
     * Mark payment as completed.
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed.
     *
     * @return bool
     */
    public function markAsFailed(): bool
    {
        return $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Process refund for the payment.
     *
     * @param string|null $reason
     * @return bool
     */
    public function processRefund(?string $reason = null): bool
    {
        if (!$this->canBeRefunded()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
            'refund_reason' => $reason,
        ]);
    }

    /**
     * Get formatted status text.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get formatted payment method text.
     *
     * @return string
     */
    public function getPaymentMethodTextAttribute(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Get formatted payment type text.
     *
     * @return string
     */
    public function getPaymentTypeTextAttribute(): string
    {
        return self::getPaymentTypes()[$this->payment_type] ?? ucfirst($this->payment_type);
    }

    /**
     * Get formatted amount.
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }
}
