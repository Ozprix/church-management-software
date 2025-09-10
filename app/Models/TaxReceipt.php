<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxReceipt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'donation_id',
        'member_id',
        'receipt_number',
        'amount',
        'donation_date',
        'issue_date',
        'sent_at',
        'tax_year',
        'is_annual',
        'status',
        'file_path',
        'void_reason',
        'voided_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'donation_date' => 'date',
        'issue_date' => 'date',
        'sent_at' => 'datetime',
        'is_annual' => 'boolean',
        'voided_at' => 'datetime',
    ];

    /**
     * Get the donation associated with the tax receipt.
     */
    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    /**
     * Get the member associated with the tax receipt.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the donations associated with an annual tax receipt.
     */
    public function annualDonations()
    {
        return $this->hasMany(Donation::class, 'annual_tax_receipt_id');
    }

    /**
     * Scope a query to only include receipts for a specific tax year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('tax_year', $year);
    }

    /**
     * Scope a query to only include annual receipts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAnnual($query)
    {
        return $query->where('is_annual', true);
    }

    /**
     * Scope a query to only include single donation receipts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSingle($query)
    {
        return $query->where('is_annual', false);
    }

    /**
     * Scope a query to only include receipts with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include issued receipts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope a query to only include sent receipts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include voided receipts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVoided($query)
    {
        return $query->where('status', 'voided');
    }

    /**
     * Scope a query to only include receipts for a specific member.
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
     * Get the download URL for the receipt PDF.
     *
     * @return string|null
     */
    public function getDownloadUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        return url('storage/' . $this->file_path);
    }

    /**
     * Check if the receipt is voided.
     *
     * @return bool
     */
    public function isVoided()
    {
        return $this->status === 'voided';
    }

    /**
     * Check if the receipt has been sent.
     *
     * @return bool
     */
    public function isSent()
    {
        return $this->status === 'sent';
    }
}
