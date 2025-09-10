<?php

namespace App\Exports;

use App\Models\Member;
use App\Models\CustomFieldSchema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MembersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;
    protected $customFields;

    /**
     * Create a new export instance.
     *
     * @param array $filters
     * @return void
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->loadCustomFields();
    }

    /**
     * Load custom fields for members
     */
    private function loadCustomFields()
    {
        $this->customFields = CustomFieldSchema::where('entity_type', 'member')
            ->where('active', true)
            ->orderBy('order')
            ->get();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Member::with(['family', 'currentJourney']);
        
        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('membership_status', $this->filters['status']);
        }
        
        if (!empty($this->filters['journey_stage'])) {
            $query->where('journey_stage', $this->filters['journey_stage']);
        }
        
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if (!empty($this->filters['family_id'])) {
            $query->where('family_id', $this->filters['family_id']);
        }
        
        return $query->orderBy('last_name')->orderBy('first_name')->get();
    }

    /**
     * @var Member $member
     */
    public function map($member): array
    {
        $row = [
            $member->id,
            $member->first_name,
            $member->last_name,
            $member->gender,
            $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : '',
            $member->phone,
            $member->email,
            $member->address,
            $member->city,
            $member->state,
            $member->zip,
            $member->country,
            $member->membership_status,
            $member->membership_date ? $member->membership_date->format('Y-m-d') : '',
            $member->journey_stage,
            $member->family ? $member->family->name : '',
        ];
        
        // Add custom fields
        $customFieldValues = $member->custom_fields ? json_decode($member->custom_fields, true) : [];
        
        foreach ($this->customFields as $field) {
            $row[] = $customFieldValues[$field->name] ?? '';
        }
        
        return $row;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = [
            'ID',
            'First Name',
            'Last Name',
            'Gender',
            'Date of Birth',
            'Phone',
            'Email',
            'Address',
            'City',
            'State',
            'ZIP',
            'Country',
            'Membership Status',
            'Membership Date',
            'Journey Stage',
            'Family Name',
        ];
        
        // Add custom field headings
        foreach ($this->customFields as $field) {
            $headings[] = $field->label;
        }
        
        return $headings;
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
