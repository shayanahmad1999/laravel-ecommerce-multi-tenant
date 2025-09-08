<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Carbon\Carbon;

/**
 * Installment Model
 *
 * Represents an installment payment for orders in the e-commerce system.
 * Handles installment scheduling, payment tracking, and overdue management.
 */
class Installment extends Model
{
    use UsesTenantConnection;

    /**
     * Installment status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'payment_id',
        'installment_number',
        'amount',
        'interest_amount',
        'total_amount',
        'due_date',
        'status',
        'paid_at',
        'penalty_amount',
        'penalty_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set default status if not provided
        static::creating(function ($installment) {
            if (!$installment->status) {
                $installment->status = self::STATUS_PENDING;
            }
        });
    }

    /**
     * Get the order that owns the installment.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the payment that owns the installment.
     *
     * @return BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get all available installment statuses.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PAID => 'Paid',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Scope to get pending installments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get paid installments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope to get overdue installments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('due_date', '<', Carbon::today());
    }

    /**
     * Scope to get cancelled installments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope to get installments by order.
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
     * Scope to get installments due within days.
     *
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeDueWithin(Builder $query, int $days): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('due_date', '<=', Carbon::today()->addDays($days));
    }

    /**
     * Scope to get installments by date range.
     *
     * @param Builder $query
     * @param string $from
     * @param string $to
     * @return Builder
     */
    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('due_date', [$from, $to]);
    }

    /**
     * Check if installment is overdue.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->due_date->isPast();
    }

    /**
     * Check if installment is due soon (within 7 days).
     *
     * @return bool
     */
    public function isDueSoon(): bool
    {
        return $this->status === self::STATUS_PENDING &&
               $this->due_date->diffInDays(Carbon::today()) <= 7;
    }

    /**
     * Check if installment is paid.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if installment can be paid.
     *
     * @return bool
     */
    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Mark installment as paid.
     *
     * @return bool
     */
    public function markAsPaid(): bool
    {
        if (!$this->canBePaid()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark installment as overdue.
     *
     * @return bool
     */
    public function markAsOverdue(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        return $this->update(['status' => self::STATUS_OVERDUE]);
    }

    /**
     * Cancel the installment.
     *
     * @return bool
     */
    public function cancel(): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Add penalty to the installment.
     *
     * @param float $amount
     * @param string|null $reason
     * @return bool
     */
    public function addPenalty(float $amount, ?string $reason = null): bool
    {
        return $this->update([
            'penalty_amount' => $amount,
            'penalty_reason' => $reason,
            'total_amount' => $this->total_amount + $amount,
        ]);
    }

    /**
     * Get days until due date.
     *
     * @return int
     */
    public function getDaysUntilDue(): int
    {
        return Carbon::today()->diffInDays($this->due_date, false);
    }

    /**
     * Get days overdue.
     *
     * @return int
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return $this->due_date->diffInDays(Carbon::today());
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
     * Get formatted amount.
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get formatted total amount (including penalties).
     *
     * @return string
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get due date status.
     *
     * @return string
     */
    public function getDueDateStatusAttribute(): string
    {
        if ($this->isPaid()) {
            return 'Paid';
        }

        $days = $this->getDaysUntilDue();

        if ($days < 0) {
            return 'Overdue by ' . abs($days) . ' days';
        }

        if ($days === 0) {
            return 'Due today';
        }

        if ($days <= 7) {
            return 'Due in ' . $days . ' days';
        }

        return 'Due on ' . $this->due_date->format('M d, Y');
    }
}
