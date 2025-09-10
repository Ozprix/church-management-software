<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pledge extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id',
        'campaign_id',
        'amount',
        'pledge_date',
        'start_date',
        'end_date',
        'frequency',
        'status',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'float',
        'pledge_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the member that made the pledge.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the campaign associated with the pledge.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the donations associated with this pledge.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Calculate the total amount donated against this pledge.
     *
     * @return float
     */
    public function getDonatedAmountAttribute()
    {
        return $this->donations()->sum('amount');
    }

    /**
     * Calculate the remaining amount for this pledge.
     *
     * @return float
     */
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->donated_amount;
    }

    /**
     * Calculate the fulfillment percentage of this pledge.
     *
     * @return float
     */
    public function getFulfillmentPercentageAttribute()
    {
        if ($this->amount <= 0) {
            return 0;
        }
        
        return ($this->donated_amount / $this->amount) * 100;
    }

    /**
     * Check if the pledge is active.
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        $now = now();
        return $now->between($this->start_date, $this->end_date) && $this->status === 'active';
    }

    /**
     * Get the next expected payment date.
     *
     * @return \Carbon\Carbon|null
     */
    public function getNextPaymentDateAttribute()
    {
        if ($this->status !== 'active' || $this->donated_amount >= $this->amount) {
            return null;
        }

        $lastDonation = $this->donations()->orderBy('donation_date', 'desc')->first();
        $startDate = $lastDonation ? $lastDonation->donation_date : $this->start_date;
        
        switch ($this->frequency) {
            case 'weekly':
                return $startDate->addWeek();
            case 'biweekly':
                return $startDate->addWeeks(2);
            case 'monthly':
                return $startDate->addMonth();
            case 'quarterly':
                return $startDate->addMonths(3);
            case 'annually':
                return $startDate->addYear();
            default:
                return null;
        }
    }
}
