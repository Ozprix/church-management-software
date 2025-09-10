<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Facades\Excel;

class MemberImportService
{
    /**
     * Import members from a file.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  bool  $updateExisting
     * @param  array  $mapping
     * @return array
     */
    public function import($file, $updateExisting = false, $mapping = [])
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        try {
            switch ($extension) {
                case 'csv':
                case 'xls':
                case 'xlsx':
                    return $this->importFromExcel($file, $updateExisting, $mapping);
                case 'json':
                    return $this->importFromJson($file, $updateExisting, $mapping);
                default:
                    return [
                        'success' => false,
                        'message' => 'Unsupported file format. Please upload a CSV, Excel, or JSON file.',
                        'imported' => 0,
                        'updated' => 0,
                        'failed' => 0,
                        'errors' => [],
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Member import failed: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred during import: ' . $e->getMessage(),
                'imported' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ];
        }
    }

    /**
     * Import members from an Excel or CSV file.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  bool  $updateExisting
     * @param  array  $mapping
     * @return array
     */
    protected function importFromExcel($file, $updateExisting, $mapping)
    {
        $import = new class($updateExisting, $mapping) implements ToModel, WithHeadingRow, WithValidation {
            protected $updateExisting;
            protected $mapping;
            protected $statuses;
            protected $imported = 0;
            protected $updated = 0;
            protected $failed = 0;
            protected $errors = [];

            public function __construct($updateExisting, $mapping)
            {
                $this->updateExisting = $updateExisting;
                $this->mapping = $mapping;
                $this->statuses = MemberStatus::pluck('id', 'name')->toArray();
            }

            public function model(array $row)
            {
                // Apply field mapping if provided
                $mappedRow = [];
                foreach ($this->mapping as $field => $header) {
                    if (isset($row[$header])) {
                        $mappedRow[$field] = $row[$header];
                    }
                }

                // If no mapping was applied, use the row as is
                if (empty($mappedRow)) {
                    $mappedRow = $row;
                }

                // Clean the row data
                $data = $this->cleanRowData($mappedRow);

                // Validate the row data
                $validator = Validator::make($data, $this->rules());

                if ($validator->fails()) {
                    $this->failed++;
                    $this->errors[] = [
                        'row' => $this->imported + $this->updated + $this->failed,
                        'data' => $data,
                        'errors' => $validator->errors()->toArray(),
                    ];
                    return null;
                }

                // Find existing member by email if update is enabled
                $member = null;
                if ($this->updateExisting && !empty($data['email'])) {
                    $member = Member::where('email', $data['email'])->first();
                }

                // Prepare member data
                $memberData = [
                    'first_name' => $data['first_name'] ?? null,
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                    'maiden_name' => $data['maiden_name'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                    'occupation' => $data['occupation'] ?? null,
                    'employer' => $data['employer'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'work_phone' => $data['work_phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'zip' => $data['zip'] ?? null,
                    'country' => $data['country'] ?? 'USA',
                    'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                    'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
                    'membership_date' => $data['membership_date'] ?? now(),
                    'membership_end_date' => $data['membership_end_date'] ?? null,
                    'membership_type' => $data['membership_type'] ?? null,
                    'baptism_status' => $data['baptism_status'] ?? null,
                    'baptism_date' => $data['baptism_date'] ?? null,
                    'baptism_location' => $data['baptism_location'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ];

                // Set membership status ID if provided
                if (!empty($data['membership_status'])) {
                    $statusName = $data['membership_status'];
                    if (isset($this->statuses[$statusName])) {
                        $memberData['membership_status_id'] = $this->statuses[$statusName];
                    } else {
                        // Default to first active status if not found
                        $defaultStatus = MemberStatus::active()->first();
                        if ($defaultStatus) {
                            $memberData['membership_status_id'] = $defaultStatus->id;
                        }
                    }
                }


                if ($member) {
                    // Update existing member
                    $member->update($memberData);
                    $this->updated++;
                } else {
                    // Create new member
                    $member = Member::create($memberData);
                    $this->imported++;
                }

                return $member;
            }

            public function rules(): array
            {
                return [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'phone' => 'nullable|string|max:20',
                    'date_of_birth' => 'nullable|date',
                    'membership_date' => 'nullable|date',
                    'membership_end_date' => 'nullable|date|after_or_equal:membership_date',
                    'baptism_date' => 'nullable|date',
                    'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                    'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed', 'separated'])],
                ];
            }

            protected function cleanRowData($row)
            {
                $cleaned = [];
                
                foreach ($row as $key => $value) {
                    if (is_string($value)) {
                        $value = trim($value);
                        
                        // Convert empty strings to null for database fields
                        if ($value === '') {
                            $value = null;
                        }
                        
                        // Convert string 'null' to actual null
                        if (strtolower($value) === 'null') {
                            $value = null;
                        }
                        
                        // Convert string 'true'/'false' to boolean
                        if (strtolower($value) === 'true') {
                            $value = true;
                        } elseif (strtolower($value) === 'false') {
                            $value = false;
                        }
                    }
                    
                    $cleaned[$key] = $value;
                }
                
                return $cleaned;
            }

            public function getImportResults()
            {
                return [
                    'imported' => $this->imported,
                    'updated' => $this->updated,
                    'failed' => $this->failed,
                    'errors' => $this->errors,
                ];
            }
        };

        // Import the file
        Excel::import($import, $file);

        // Get the import results
        $results = $import->getImportResults();

        return [
            'success' => true,
            'message' => sprintf(
                'Import completed. %d imported, %d updated, %d failed.',
                $results['imported'],
                $results['updated'],
                $results['failed']
            ),
            'imported' => $results['imported'],
            'updated' => $results['updated'],
            'failed' => $results['failed'],
            'errors' => $results['errors'],
        ];
    }

    /**
     * Import members from a JSON file.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  bool  $updateExisting
     * @param  array  $mapping
     * @return array
     */
    protected function importFromJson($file, $updateExisting, $mapping)
    {
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Invalid JSON file: ' . json_last_error_msg(),
                'imported' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => [],
            ];
        }

        return $this->processJsonData($data, $updateExisting, $mapping);
    }

    /**
     * Process JSON data and import members.
     *
     * @param  array  $data
     * @param  bool  $updateExisting
     * @param  array  $mapping
     * @return array
     */
    protected function processJsonData($data, $updateExisting, $mapping)
    {
        $imported = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];
        $statuses = MemberStatus::pluck('id', 'name')->toArray();

        foreach ($data as $index => $row) {
            try {
                // Apply field mapping if provided
                $mappedRow = [];
                foreach ($mapping as $field => $header) {
                    if (isset($row[$header])) {
                        $mappedRow[$field] = $row[$header];
                    }
                }

                // If no mapping was applied, use the row as is
                if (empty($mappedRow)) {
                    $mappedRow = $row;
                }

                // Clean the row data
                $mappedRow = $this->cleanRowData($mappedRow);

                // Validate the row data
                $validator = Validator::make($mappedRow, [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'phone' => 'nullable|string|max:20',
                    'date_of_birth' => 'nullable|date',
                    'membership_date' => 'nullable|date',
                    'membership_end_date' => 'nullable|date|after_or_equal:membership_date',
                    'baptism_date' => 'nullable|date',
                    'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                    'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed', 'separated'])],
                ]);

                if ($validator->fails()) {
                    $failed++;
                    $errors[] = [
                        'row' => $index + 1,
                        'data' => $mappedRow,
                        'errors' => $validator->errors()->toArray(),
                    ];
                    continue;
                }

                // Find existing member by email if update is enabled
                $member = null;
                if ($updateExisting && !empty($mappedRow['email'])) {
                    $member = Member::where('email', $mappedRow['email'])->first();
                }

                // Prepare member data
                $memberData = [
                    'first_name' => $mappedRow['first_name'] ?? null,
                    'middle_name' => $mappedRow['middle_name'] ?? null,
                    'last_name' => $mappedRow['last_name'] ?? null,
                    'maiden_name' => $mappedRow['maiden_name'] ?? null,
                    'gender' => $mappedRow['gender'] ?? null,
                    'date_of_birth' => $mappedRow['date_of_birth'] ?? null,
                    'marital_status' => $mappedRow['marital_status'] ?? null,
                    'occupation' => $mappedRow['occupation'] ?? null,
                    'employer' => $mappedRow['employer'] ?? null,
                    'phone' => $mappedRow['phone'] ?? null,
                    'work_phone' => $mappedRow['work_phone'] ?? null,
                    'email' => $mappedRow['email'] ?? null,
                    'address' => $mappedRow['address'] ?? null,
                    'city' => $mappedRow['city'] ?? null,
                    'state' => $mappedRow['state'] ?? null,
                    'zip' => $mappedRow['zip'] ?? null,
                    'country' => $mappedRow['country'] ?? 'USA',
                    'emergency_contact_name' => $mappedRow['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $mappedRow['emergency_contact_phone'] ?? null,
                    'emergency_contact_relation' => $mappedRow['emergency_contact_relation'] ?? null,
                    'membership_date' => $mappedRow['membership_date'] ?? now(),
                    'membership_end_date' => $mappedRow['membership_end_date'] ?? null,
                    'membership_type' => $mappedRow['membership_type'] ?? null,
                    'baptism_status' => $mappedRow['baptism_status'] ?? null,
                    'baptism_date' => $mappedRow['baptism_date'] ?? null,
                    'baptism_location' => $mappedRow['baptism_location'] ?? null,
                    'notes' => $mappedRow['notes'] ?? null,
                ];

                // Set membership status ID if provided
                if (!empty($mappedRow['membership_status'])) {
                    $statusName = $mappedRow['membership_status'];
                    if (isset($statuses[$statusName])) {
                        $memberData['membership_status_id'] = $statuses[$statusName];
                    } else {
                        // Default to first active status if not found
                        $defaultStatus = MemberStatus::active()->first();
                        if ($defaultStatus) {
                            $memberData['membership_status_id'] = $defaultStatus->id;
                        }
                    }
                }

                if ($member) {
                    // Update existing member
                    $member->update($memberData);
                    $updated++;
                } else {
                    // Create new member
                    Member::create($memberData);
                    $imported++;
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $index + 1,
                    'data' => $mappedRow ?? [],
                    'errors' => ['import_error' => $e->getMessage()],
                ];
                continue;
            }
        }

        return [
            'success' => true,
            'message' => sprintf(
                'Import completed. %d imported, %d updated, %d failed.',
                $imported,
                $updated,
                $failed
            ),
            'imported' => $imported,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Clean row data by trimming strings and converting empty strings to null.
     *
     * @param  array  $row
     * @return array
     */
    protected function cleanRowData($row)
    {
        $cleaned = [];
        
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                
                // Convert empty strings to null for database fields
                if ($value === '') {
                    $value = null;
                }
                
                // Convert string 'null' to actual null
                if (strtolower($value) === 'null') {
                    $value = null;
                }
                
                // Convert string 'true'/'false' to boolean
                if (strtolower($value) === 'true') {
                    $value = true;
                } elseif (strtolower($value) === 'false') {
                    $value = false;
                }
            }
            
            $cleaned[$key] = $value;
        }
        
        return $cleaned;
    }
}
