<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FinancialMetric extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'metric_name',
        'category',
        'value',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'value' => 'decimal:2',
    ];

    /**
     * Scope a query to only include metrics for a specific date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include metrics with a specific name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $metricName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithName($query, $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /**
     * Scope a query to only include metrics with a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the trend for a specific metric over time.
     *
     * @param  string  $metricName
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  string  $interval
     * @return array
     */
    public static function getTrend($metricName, $startDate, $endDate, $interval = 'month'): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $query = self::withName($metricName)
            ->betweenDates($startDate, $endDate)
            ->orderBy('date');
            
        $metrics = $query->get();
        
        $result = [];
        
        if ($interval === 'day') {
            // Group by day
            $grouped = $metrics->groupBy(function($metric) {
                return $metric->date->format('Y-m-d');
            });
            
            // Generate all days in the range
            $period = Carbon::parse($startDate)->daysUntil($endDate);
            
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');
                $value = isset($grouped[$dateString]) ? $grouped[$dateString]->avg('value') : 0;
                
                $result[] = [
                    'date' => $dateString,
                    'value' => $value,
                ];
            }
        } elseif ($interval === 'week') {
            // Group by week
            $grouped = $metrics->groupBy(function($metric) {
                return $metric->date->startOfWeek()->format('Y-m-d');
            });
            
            // Generate all weeks in the range
            $period = Carbon::parse($startDate)->startOfWeek()->weeksUntil($endDate);
            
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');
                $value = isset($grouped[$dateString]) ? $grouped[$dateString]->avg('value') : 0;
                
                $result[] = [
                    'date' => $dateString,
                    'value' => $value,
                ];
            }
        } elseif ($interval === 'month') {
            // Group by month
            $grouped = $metrics->groupBy(function($metric) {
                return $metric->date->format('Y-m');
            });
            
            // Generate all months in the range
            $period = Carbon::parse($startDate)->startOfMonth()->monthsUntil($endDate);
            
            foreach ($period as $date) {
                $dateString = $date->format('Y-m');
                $value = isset($grouped[$dateString]) ? $grouped[$dateString]->avg('value') : 0;
                
                $result[] = [
                    'date' => $date->format('Y-m-d'),
                    'value' => $value,
                ];
            }
        } elseif ($interval === 'quarter') {
            // Group by quarter
            $grouped = $metrics->groupBy(function($metric) {
                return $metric->date->format('Y') . '-Q' . $metric->date->quarter;
            });
            
            // Generate all quarters in the range
            $startQuarter = Carbon::parse($startDate)->startOfQuarter();
            $endQuarter = Carbon::parse($endDate)->startOfQuarter();
            
            while ($startQuarter->lte($endQuarter)) {
                $quarterString = $startQuarter->format('Y') . '-Q' . $startQuarter->quarter;
                $value = isset($grouped[$quarterString]) ? $grouped[$quarterString]->avg('value') : 0;
                
                $result[] = [
                    'date' => $startQuarter->format('Y-m-d'),
                    'value' => $value,
                ];
                
                $startQuarter->addQuarter();
            }
        } elseif ($interval === 'year') {
            // Group by year
            $grouped = $metrics->groupBy(function($metric) {
                return $metric->date->format('Y');
            });
            
            // Generate all years in the range
            $startYear = Carbon::parse($startDate)->startOfYear();
            $endYear = Carbon::parse($endDate)->startOfYear();
            
            while ($startYear->lte($endYear)) {
                $yearString = $startYear->format('Y');
                $value = isset($grouped[$yearString]) ? $grouped[$yearString]->avg('value') : 0;
                
                $result[] = [
                    'date' => $startYear->format('Y-m-d'),
                    'value' => $value,
                ];
                
                $startYear->addYear();
            }
        }
        
        return $result;
    }

    /**
     * Calculate and store key financial metrics for a specific date.
     *
     * @param  string  $date
     * @return bool
     */
    public static function calculateAndStoreMetrics($date): bool
    {
        $metrics = [];
        $dateObj = Carbon::parse($date);
        $startOfMonth = $dateObj->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $dateObj->copy()->endOfMonth()->format('Y-m-d');
        
        // Calculate total donations for the month
        $totalDonations = Donation::whereBetween('donation_date', [$startOfMonth, $endOfMonth])
            ->where('payment_status', 'completed')
            ->sum('amount');
        
        $metrics[] = [
            'date' => $date,
            'metric_name' => 'monthly_donations',
            'value' => $totalDonations,
            'notes' => 'Total donations for ' . $dateObj->format('F Y'),
        ];
        
        // Calculate total expenses for the month
        $totalExpenses = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        
        $metrics[] = [
            'date' => $date,
            'metric_name' => 'monthly_expenses',
            'value' => $totalExpenses,
            'notes' => 'Total expenses for ' . $dateObj->format('F Y'),
        ];
        
        // Calculate net income for the month
        $netIncome = $totalDonations - $totalExpenses;
        
        $metrics[] = [
            'date' => $date,
            'metric_name' => 'monthly_net_income',
            'value' => $netIncome,
            'notes' => 'Net income for ' . $dateObj->format('F Y'),
        ];
        
        // Calculate average donation amount
        $donationCount = Donation::whereBetween('donation_date', [$startOfMonth, $endOfMonth])
            ->where('payment_status', 'completed')
            ->count();
        
        $avgDonation = $donationCount > 0 ? $totalDonations / $donationCount : 0;
        
        $metrics[] = [
            'date' => $date,
            'metric_name' => 'average_donation',
            'value' => $avgDonation,
            'notes' => 'Average donation amount for ' . $dateObj->format('F Y'),
        ];
        
        // Calculate donor count
        $donorCount = Donation::whereBetween('donation_date', [$startOfMonth, $endOfMonth])
            ->where('payment_status', 'completed')
            ->distinct('member_id')
            ->count('member_id');
        
        $metrics[] = [
            'date' => $date,
            'metric_name' => 'donor_count',
            'value' => $donorCount,
            'notes' => 'Number of unique donors for ' . $dateObj->format('F Y'),
        ];
        
        // Calculate recurring donation percentage
        $recurringDonations = Donation::whereBetween('donation_date', [$startOfMonth, $endOfMonth])
            ->where('payment_status', 'completed')
            ->where('is_recurring', true)
            ->sum('amount');
        
        $recurringPercentage = $totalDonations > 0 ? ($recurringDonations / $totalDonations) * 100 : 0;
        
        $metrics[] = [
            'date' => $date,
            'metric_name' => 'recurring_donation_percentage',
            'value' => $recurringPercentage,
            'notes' => 'Percentage of donations from recurring sources for ' . $dateObj->format('F Y'),
        ];
        
        // Calculate expense by category
        $expenseCategories = ExpenseCategory::all();
        
        foreach ($expenseCategories as $category) {
            $categoryExpenses = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
                ->where('category_id', $category->id)
                ->sum('amount');
            
            $metrics[] = [
                'date' => $date,
                'metric_name' => 'expense_by_category',
                'category' => $category->name,
                'value' => $categoryExpenses,
                'notes' => 'Total expenses for category ' . $category->name . ' in ' . $dateObj->format('F Y'),
            ];
        }
        
        // Calculate donation by category
        $donationCategories = DonationCategory::all();
        
        foreach ($donationCategories as $category) {
            $categoryDonations = Donation::whereBetween('donation_date', [$startOfMonth, $endOfMonth])
                ->where('payment_status', 'completed')
                ->where('category_id', $category->id)
                ->sum('amount');
            
            $metrics[] = [
                'date' => $date,
                'metric_name' => 'donation_by_category',
                'category' => $category->name,
                'value' => $categoryDonations,
                'notes' => 'Total donations for category ' . $category->name . ' in ' . $dateObj->format('F Y'),
            ];
        }
        
        // Store all calculated metrics
        foreach ($metrics as $metric) {
            self::updateOrCreate(
                [
                    'date' => $metric['date'],
                    'metric_name' => $metric['metric_name'],
                    'category' => $metric['category'] ?? null,
                ],
                $metric
            );
        }
        
        return true;
    }
}
