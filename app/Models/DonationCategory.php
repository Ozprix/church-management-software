<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonationCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'is_tax_deductible',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_tax_deductible' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the donations for this category.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'category_id');
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include tax-deductible categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTaxDeductible($query)
    {
        return $query->where('is_tax_deductible', true);
    }

    /**
     * Get the default category.
     *
     * @return DonationCategory|null
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }
}
