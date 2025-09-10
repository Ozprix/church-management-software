<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'recipient_id',
        'category_id',
        'project_id',
        'campaign_id',
        'amount',
        'payment_method',
        'transaction_id',
        'receipt_number',
        'donation_date',
        'is_anonymous',
        'is_recurring',
        'recurring_frequency',
        'recurring_start_date',
        'recurring_end_date',
        'notes',
        'gift_message',
        'receipt_sent',
        'receipt_sent_at',
        'payment_status',
        'refund_id',
        'refund_amount',
        'gateway_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'donation_date' => 'date',
        'is_anonymous' => 'boolean',
        'is_recurring' => 'boolean',
        'recurring_start_date' => 'date',
        'recurring_end_date' => 'date',
        'receipt_sent' => 'boolean',
        'receipt_sent_at' => 'datetime',
        'refund_amount' => 'decimal:2',
        'gateway_data' => 'array',
    ];

    /**
     * Get the member who made the donation.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    
    /**
     * Get the recipient member (for gifts).
     */
    public function recipient()
    {
        return $this->belongsTo(Member::class, 'recipient_id');
    }
    
    /**
     * Get the category of the donation.
     */
    public function category()
    {
        return $this->belongsTo(DonationCategory::class);
    }
    
    /**
     * Get the project associated with the donation.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the campaign associated with the donation.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    /**
     * Get the payment transactions for the donation.
     */
    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Scope a query to only include donations for a specific campaign.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $campaignId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCampaign($query, $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope a query to only include donations between specific dates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('donation_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include recurring donations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }
    
    /**
     * Scope a query to only include anonymous donations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }
    
    /**
     * Scope a query to only include donations for a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    /**
     * Scope a query to only include donations for a specific project.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $projectId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
    
    /**
     * Scope a query to only include gift donations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGifts($query)
    {
        return $query->whereNotNull('recipient_id');
    }
    
    /**
     * Scope a query to only include donations with a specific payment status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }
    
    /**
     * Scope a query to only include completed payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }
    
    /**
     * Scope a query to only include pending payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }
    
    /**
     * Scope a query to only include failed payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }
    
    /**
     * Scope a query to only include refunded payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }
    
    /**
     * Check if the donation payment is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->payment_status === 'completed';
    }
    
    /**
     * Check if the donation payment is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }
    
    /**
     * Check if the donation payment has failed.
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->payment_status === 'failed';
    }
    
    /**
     * Check if the donation has been refunded.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->payment_status === 'refunded';
    }
    
    /**
     * Get the latest transaction for this donation.
     *
     * @return \App\Models\PaymentTransaction|null
     */
    public function getLatestTransaction()
    {
        return $this->transactions()->latest()->first();
    }
}