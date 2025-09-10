<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomFieldSchema extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entity_type',
        'name',
        'label',
        'type',
        'options',
        'required',
        'validation',
        'placeholder',
        'description',
        'order',
        'active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'json',
        'required' => 'boolean',
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the user who created this custom field schema.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get available field types.
     *
     * @return array
     */
    public static function getFieldTypes(): array
    {
        return [
            'text' => 'Text',
            'textarea' => 'Text Area',
            'number' => 'Number',
            'date' => 'Date',
            'select' => 'Select Dropdown',
            'multiselect' => 'Multi-Select',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Buttons',
            'email' => 'Email',
            'phone' => 'Phone Number',
            'url' => 'URL',
            'file' => 'File Upload',
        ];
    }

    /**
     * Get validation rules based on field type.
     *
     * @return string
     */
    public function getDefaultValidation(): string
    {
        switch ($this->type) {
            case 'text':
            case 'textarea':
                return 'string|max:255';
            case 'number':
                return 'numeric';
            case 'date':
                return 'date';
            case 'email':
                return 'email';
            case 'phone':
                return 'string|max:20';
            case 'url':
                return 'url';
            case 'file':
                return 'file|max:2048';
            default:
                return '';
        }
    }
}
