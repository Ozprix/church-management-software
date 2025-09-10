<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'donation_id',
        'transaction_id',
        'gateway',
        'amount',
        'currency',
        'status',
        'type',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',
        'metadata' => 'array',
    ];

    /**
     * Get the donation associated with the transaction.
     */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }
    
    /**
     * Scope a query to only include payment transactions.
     */
    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }
    
    /**
     * Scope a query to only include refund transactions.
     */
    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }
    
    /**
     * Scope a query to only include successful transactions.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['succeeded', 'completed', 'success']);
    }
    
    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'failure', 'error']);
    }
    
    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }
    
    /**
     * Check if transaction is successful.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['succeeded', 'completed', 'success']);
    }
    
    /**
     * Check if transaction is a payment.
     */
    public function isPayment(): bool
    {
        return $this->type === 'payment';
    }
    
    /**
     * Check if transaction is a refund.
     */
    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }
}
