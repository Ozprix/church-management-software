<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MemberService
{
    /**
     * Get paginated list of members with optional filters
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedMembers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Member::query()
            ->with(['user', 'family'])
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $search = '%' . $filters['search'] . '%';
                return $q->where(function($q) use ($search) {
                    $q->where('first_name', 'like', $search)
                      ->orWhere('last_name', 'like', $search)
                      ->orWhere('email', 'like', $search)
                      ->orWhere('phone', 'like', $search);
                });
            })
            ->when(!empty($filters['status']), function ($q) use ($filters) {
                return $q->where('membership_status', $filters['status']);
            })
            ->when(!empty($filters['gender']), function ($q) use ($filters) {
                return $q->where('gender', $filters['gender']);
            });

        // Apply sorting
        $sortField = $filters['sort'] ?? 'last_name';
        $sortDirection = isset($filters['order']) && strtolower($filters['order']) === 'desc' ? 'desc' : 'asc';
        
        return $query->orderBy($sortField, $sortDirection)
                    ->paginate($perPage)
                    ->appends($filters);
    }

    /**
     * Create a new member
     *
     * @param array $data
     * @return Member
     */
    public function createMember(array $data): Member
    {
        try {
            // Handle file upload if present
            if (isset($data['profile_photo'])) {
                $data['profile_photo'] = $this->handleFileUpload($data['profile_photo'], 'profile_photos');
            }

            return Member::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating member: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing member
     *
     * @param Member $member
     * @param array $data
     * @return Member
     */
    public function updateMember(Member $member, array $data): Member
    {
        try {
            // Handle file upload if present
            if (isset($data['profile_photo'])) {
                // Delete old profile photo if exists
                if ($member->profile_photo) {
                    $this->deleteFile($member->profile_photo);
                }
                $data['profile_photo'] = $this->handleFileUpload($data['profile_photo'], 'profile_photos');
            }

            $member->update($data);
            return $member->fresh();
        } catch (\Exception $e) {
            Log::error('Error updating member: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a member
     *
     * @param Member $member
     * @return bool
     */
    public function deleteMember(Member $member): bool
    {
        try {
            // Delete profile photo if exists
            if ($member->profile_photo) {
                $this->deleteFile($member->profile_photo);
            }
            
            return $member->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting member: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle file upload
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return string
     */
    protected function handleFileUpload($file, string $folder): string
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Delete a file
     *
     * @param string $filePath
     * @return bool
     */
    protected function deleteFile(string $filePath): bool
    {
        if (\Storage::disk('public')->exists($filePath)) {
            return \Storage::disk('public')->delete($filePath);
        }
        return false;
    }

    /**
     * Get member statistics
     *
     * @return array
     */
    public function getMemberStatistics(): array
    {
        return [
            'total' => Member::count(),
            'active' => Member::where('membership_status', 'active')->count(),
            'inactive' => Member::where('membership_status', 'inactive')->count(),
            'pending' => Member::where('membership_status', 'pending')->count(),
            'average_age' => $this->calculateAverageAge(),
            'gender_distribution' => $this->getGenderDistribution(),
        ];
    }

    /**
     * Calculate average age of members
     *
     * @return float|null
     */
    protected function calculateAverageAge(): ?float
    {
        $members = Member::whereNotNull('date_of_birth')->get();
        
        if ($members->isEmpty()) {
            return null;
        }

        $totalAge = $members->sum(function($member) {
            return $member->date_of_birth->age;
        });

        return round($totalAge / $members->count(), 1);
    }

    /**
     * Get gender distribution
     *
     * @return array
     */
    protected function getGenderDistribution(): array
    {
        return Member::selectRaw('gender, COUNT(*) as count')
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();
    }
    
    /**
     * Get members for export based on filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMembersForExport(array $filters = [])
    {
        $query = Member::query();
        
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', $search)
                  ->orWhere('last_name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('phone', 'like', $search);
            });
        }
        
        if (!empty($filters['status'])) {
            $query->where('membership_status', $filters['status']);
        }
        
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }
        
        return $query->get();
    }
    
    /**
     * Import members from a file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param bool $updateExisting
     * @return array
     */
    public function importMembers($file, bool $updateExisting = false): array
    {
        $extension = $file->getClientOriginalExtension();
        $imported = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];
        
        if (in_array($extension, ['csv', 'txt'])) {
            // Process CSV file
            $handle = fopen($file->getPathname(), 'r');
            $header = fgetcsv($handle); // Get header row
            
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                
                try {
                    $result = $this->processImportRow($data, $updateExisting);
                    
                    if ($result['created']) $imported++;
                    if ($result['updated']) $updated++;
                    
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row " . ($imported + $updated + $failed) . ": " . $e->getMessage();
                    Log::error('Error importing member: ' . $e->getMessage());
                }
            }
            
            fclose($handle);
            
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            // Process Excel file
            // You would typically use a package like Maatwebsite/Excel for this
            // This is a simplified example
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $header = [];
            $firstRow = true;
            
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                
                if ($firstRow) {
                    $header = $rowData;
                    $firstRow = false;
                    continue;
                }
                
                $data = array_combine($header, $rowData);
                
                try {
                    $result = $this->processImportRow($data, $updateExisting);
                    
                    if ($result['created']) $imported++;
                    if ($result['updated']) $updated++;
                    
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row " . ($imported + $updated + $failed) . ": " . $e->getMessage();
                    Log::error('Error importing member: ' . $e->getMessage());
                }
            }
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
    
    /**
     * Process a single row from the import file
     *
     * @param array $data
     * @param bool $updateExisting
     * @return array
     * @throws \Exception
     */
    protected function processImportRow(array $data, bool $updateExisting): array
    {
        // Normalize data
        $email = strtolower(trim($data['email'] ?? ''));
        
        if (empty($email)) {
            throw new \Exception('Email is required');
        }
        
        // Check if member exists
        $member = Member::where('email', $email)->first();
        
        if ($member) {
            if (!$updateExisting) {
                throw new \Exception("Member with email {$email} already exists");
            }
            
            // Update existing member
            $member->update([
                'first_name' => $data['first_name'] ?? $member->first_name,
                'last_name' => $data['last_name'] ?? $member->last_name,
                'phone' => $data['phone'] ?? $member->phone,
                'gender' => $data['gender'] ?? $member->gender,
                'date_of_birth' => $data['date_of_birth'] ?? $member->date_of_birth,
                'membership_status' => $data['membership_status'] ?? $member->membership_status,
                // Add other fields as needed
            ]);
            
            return ['created' => false, 'updated' => true];
            
        } else {
            // Create new member
            Member::create([
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email' => $email,
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'membership_status' => $data['membership_status'] ?? 'active',
                // Add other fields as needed
            ]);
            
            return ['created' => true, 'updated' => false];
        }
    }
    
    /**
     * Delete multiple members
     *
     * @param array $ids
     * @return array
     */
    public function bulkDelete(array $ids): array
    {
        $deleted = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            try {
                $member = Member::findOrFail($id);
                $this->deleteMember($member);
                $deleted++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Member ID {$id}: " . $e->getMessage();
                Log::error("Error deleting member {$id}: " . $e->getMessage());
            }
        }
        
        return [
            'deleted' => $deleted,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
    
    /**
     * Update status for multiple members
     *
     * @param array $ids
     * @param string $status
     * @return array
     */
    public function bulkUpdateStatus(array $ids, string $status): array
    {
        $updated = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            try {
                $member = Member::findOrFail($id);
                $member->update(['membership_status' => $status]);
                $updated++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Member ID {$id}: " . $e->getMessage();
                Log::error("Error updating status for member {$id}: " . $e->getMessage());
            }
        }
        
        return [
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
    
    /**
     * Send email to multiple members
     *
     * @param array $ids
     * @param string $subject
     * @param string $body
     * @return array
     */
    public function bulkSendEmail(array $ids, string $subject, string $body): array
    {
        $sent = 0;
        $failed = 0;
        $errors = [];
        
        $members = Member::whereIn('id', $ids)->get();
        
        foreach ($members as $member) {
            try {
                // In a real application, you would queue the email
                // Mail::to($member->email)->queue(new MemberEmail($subject, $body, $member));
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Member ID {$member->id}: " . $e->getMessage();
                Log::error("Error sending email to member {$member->id}: " . $e->getMessage());
            }
        }
        
        return [
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
}
