<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Report extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'description',
        'parameters',
        'filters',
        'output_format',
        'created_by',
        'is_favorite',
        'is_scheduled',
        'schedule_frequency',
        'last_generated_at'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_favorite' => 'boolean',
        'is_scheduled' => 'boolean',
        'last_generated_at' => 'datetime'
    ];
    
    /**
     * Get the user who created the report.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the report's formatted last generated date.
     *
     * @return string
     */
    public function getLastGeneratedAttribute()
    {
        if (!$this->last_generated_at) {
            return 'Never';
        }
        
        return $this->last_generated_at->format('M d, Y h:i A');
    }
    
    /**
     * Get the report's type display name.
     *
     * @return string
     */
    public function getTypeDisplayAttribute()
    {
        $types = [
            'financial' => 'Financial',
            'attendance' => 'Attendance',
            'membership' => 'Membership',
            'donation' => 'Donation',
            'expense' => 'Expense',
            'pledge' => 'Pledge',
            'campaign' => 'Campaign',
            'event' => 'Event',
            'volunteer' => 'Volunteer',
            'custom' => 'Custom'
        ];
        
        return $types[$this->type] ?? ucfirst($this->type);
    }
    
    /**
     * Get the report's schedule frequency display name.
     *
     * @return string
     */
    public function getScheduleFrequencyDisplayAttribute()
    {
        if (!$this->is_scheduled) {
            return 'Not scheduled';
        }
        
        $frequencies = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly'
        ];
        
        return $frequencies[$this->schedule_frequency] ?? ucfirst($this->schedule_frequency);
    }
}
