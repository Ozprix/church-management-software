<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\Family;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MembersImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    private $families = [];
    private $importResults = [
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
        'errors' => []
    ];

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // Load all families to avoid multiple queries
        $this->loadFamilies();
        
        $this->importResults['total'] = count($rows);
        
        foreach ($rows as $index => $row) {
            try {
                // Check if member exists (by email)
                $member = null;
                if (!empty($row['email'])) {
                    $member = Member::where('email', $row['email'])->first();
                }
                
                // Handle family association
                $familyId = null;
                if (!empty($row['family_name'])) {
                    $familyId = $this->findOrCreateFamily($row['family_name']);
                }
                
                // Prepare member data
                $memberData = [
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'gender' => strtolower($row['gender'] ?? ''),
                    'date_of_birth' => !empty($row['date_of_birth']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_of_birth']) : null,
                    'phone' => $row['phone'] ?? null,
                    'email' => $row['email'] ?? null,
                    'address' => $row['address'] ?? null,
                    'city' => $row['city'] ?? null,
                    'state' => $row['state'] ?? null,
                    'zip' => $row['zip'] ?? null,
                    'country' => $row['country'] ?? null,
                    'membership_status' => strtolower($row['membership_status'] ?? 'pending'),
                    'membership_date' => !empty($row['membership_date']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['membership_date']) : null,
                    'journey_stage' => strtolower($row['journey_stage'] ?? 'visitor'),
                    'family_id' => $familyId,
                ];
                
                // Handle custom fields
                $customFields = [];
                foreach ($row as $key => $value) {
                    if (strpos($key, 'custom_') === 0 && !empty($value)) {
                        $customFields[str_replace('custom_', '', $key)] = $value;
                    }
                }
                
                if (!empty($customFields)) {
                    $memberData['custom_fields'] = json_encode($customFields);
                }
                
                if ($member) {
                    // Update existing member
                    $member->update($memberData);
                    $this->importResults['updated']++;
                } else {
                    // Create new member
                    Member::create($memberData);
                    $this->importResults['created']++;
                }
            } catch (\Exception $e) {
                $this->importResults['failed']++;
                $this->importResults['errors'][] = [
                    'row' => $index + 2, // +2 because of 0-indexing and header row
                    'error' => $e->getMessage()
                ];
            }
        }
    }
    
    /**
     * Load all families into memory
     */
    private function loadFamilies()
    {
        $families = Family::all();
        foreach ($families as $family) {
            $this->families[strtolower($family->name)] = $family->id;
        }
    }
    
    /**
     * Find or create a family by name
     * 
     * @param string $familyName
     * @return int Family ID
     */
    private function findOrCreateFamily($familyName)
    {
        $familyNameLower = strtolower($familyName);
        
        // Check if we already have this family in our cache
        if (isset($this->families[$familyNameLower])) {
            return $this->families[$familyNameLower];
        }
        
        // Create new family
        $family = Family::create([
            'name' => $familyName,
            'status' => 'active'
        ]);
        
        // Add to cache
        $this->families[$familyNameLower] = $family->id;
        
        return $family->id;
    }
    
    /**
     * Get import results
     * 
     * @return array
     */
    public function getImportResults()
    {
        return $this->importResults;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female,other,Male,Female,Other',
            'membership_status' => 'nullable|in:active,inactive,pending,transferred,Active,Inactive,Pending,Transferred',
            'journey_stage' => 'nullable|in:visitor,regular,committed,leader,Visitor,Regular,Committed,Leader',
        ];
    }
    
    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.email' => 'Email must be a valid email address',
            'gender.in' => 'Gender must be one of: male, female, other',
            'membership_status.in' => 'Membership status must be one of: active, inactive, pending, transferred',
            'journey_stage.in' => 'Journey stage must be one of: visitor, regular, committed, leader',
        ];
    }
    
    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }
    
    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
