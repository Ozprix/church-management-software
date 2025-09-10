<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RecurringDonation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'category_id',
        'project_id',
        'campaign_id',
        'amount',
        'payment_method',
        'payment_gateway',
        'frequency',
        'start_date',
        'end_date',
        'next_donation_date',
        'is_active',
        'gateway_subscription_id',
        'gateway_customer_id',
        'gateway_data',
        'last_donation_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_donation_date' => 'date',
        'is_active' => 'boolean',
        'gateway_data' => 'array',
    ];

    /**
     * Get the member associated with the recurring donation.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the category of the recurring donation.
     */
    public function category()
    {
        return $this->belongsTo(DonationCategory::class);
    }

    /**
     * Get the project associated with the recurring donation.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the campaign associated with the recurring donation.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the donations associated with this recurring donation.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class, 'recurring_donation_id');
    }

    /**
     * Get the last donation made for this recurring donation.
     */
    public function lastDonation()
    {
        return $this->belongsTo(Donation::class, 'last_donation_id');
    }

    /**
     * Scope a query to only include active recurring donations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive recurring donations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to only include recurring donations that are due for processing.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueForProcessing($query)
    {
        return $query->where('is_active', true)
            ->where('next_donation_date', '<=', Carbon::today())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            });
    }

    /**
     * Scope a query to only include recurring donations for a specific member.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $memberId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope a query to only include recurring donations for a specific category.
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
     * Scope a query to only include recurring donations for a specific project.
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
     * Scope a query to only include recurring donations for a specific campaign.
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
     * Scope a query to only include recurring donations with a specific frequency.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $frequency
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Calculate the next donation date based on the frequency.
     *
     * @param  \Carbon\Carbon|null  $fromDate
     * @return \Carbon\Carbon
     */
    public function calculateNextDonationDate(Carbon $fromDate = null): Carbon
    {
        $fromDate = $fromDate ?? ($this->next_donation_date ?? $this->start_date);
        
        switch ($this->frequency) {
            case 'weekly':
                return $fromDate->copy()->addWeek();
            case 'biweekly':
                return $fromDate->copy()->addWeeks(2);
            case 'monthly':
                return $fromDate->copy()->addMonth();
            case 'quarterly':
                return $fromDate->copy()->addMonths(3);
            case 'biannually':
                return $fromDate->copy()->addMonths(6);
            case 'annually':
                return $fromDate->copy()->addYear();
            default:
                return $fromDate->copy()->addMonth(); // Default to monthly
        }
    }

    /**
     * Update the next donation date based on the frequency.
     *
     * @return void
     */
    public function updateNextDonationDate(): void
    {
        $this->next_donation_date = $this->calculateNextDonationDate();
        $this->save();
    }

    /**
     * Check if the recurring donation has expired.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        if (!$this->end_date) {
            return false;
        }
        
        return $this->end_date->isPast();
    }

    /**
     * Get the human-readable frequency.
     *
     * @return string
     */
    public function getFrequencyTextAttribute(): string
    {
        switch ($this->frequency) {
            case 'weekly':
                return 'Weekly';
            case 'biweekly':
                return 'Every 2 Weeks';
            case 'monthly':
                return 'Monthly';
            case 'quarterly':
                return 'Every 3 Months';
            case 'biannually':
                return 'Every 6 Months';
            case 'annually':
                return 'Yearly';
            default:
                return ucfirst($this->frequency);
        }
    }

    /**
     * Get the status text.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        
        if ($this->hasExpired()) {
            return 'Expired';
        }
        
        return 'Active';
    }
}
