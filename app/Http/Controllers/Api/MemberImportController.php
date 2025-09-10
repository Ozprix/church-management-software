<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberImportRequest;
use App\Services\MemberImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MemberImportController extends Controller
{
    /**
     * @var MemberImportService
     */
    protected $memberImportService;

    /**
     * Create a new controller instance.
     *
     * @param  MemberImportService  $memberImportService
     * @return void
     */
    public function __construct(MemberImportService $memberImportService)
    {
        $this->memberImportService = $memberImportService;
        
        // Apply middleware for permissions
        $this->middleware('permission:members.import');
    }

    /**
     * Import members from a file.
     *
     * @param  MemberImportRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(MemberImportRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $updateExisting = $request->boolean('update_existing', false);
            $mapping = $request->input('mapping', []);

            // Process the import
            $result = $this->memberImportService->import($file, $updateExisting, $mapping);

            // Log the import
            Log::info('Members imported', [
                'imported' => $result['imported'],
                'updated' => $result['updated'],
                'failed' => $result['failed'],
                'file' => $file->getClientOriginalName(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'imported' => $result['imported'],
                    'updated' => $result['updated'],
                    'failed' => $result['failed'],
                    'errors' => $result['errors'] ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Member import failed: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $request->file('file')?->getClientOriginalName(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import: ' . $e->getMessage(),
                'data' => [
                    'imported' => 0,
                    'updated' => 0,
                    'failed' => 0,
                    'errors' => [],
                ],
            ], 500);
        }
    }

    /**
     * Get the template for member import.
     *
     * @return \Illuminate\Http\Response
     */
    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="member_import_template.csv"',
        ];

        $handle = fopen('php://temp', 'w+');
        
        // Add BOM for UTF-8
        fputs($handle, "\xEF\xBB\xBF");
        
        // Add headers
        fputcsv($handle, [
            'first_name',
            'middle_name',
            'last_name',
            'maiden_name',
            'gender',
            'date_of_birth',
            'marital_status',
            'occupation',
            'employer',
            'phone',
            'work_phone',
            'email',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_relation',
            'membership_status',
            'membership_date',
            'membership_end_date',
            'membership_type',
            'baptism_status',
            'baptism_date',
            'baptism_location',
            'notes',
        ]);
        
        // Add example row
        fputcsv($handle, [
            'John',
            'Michael',
            'Doe',
            'Smith',
            'male',
            '1985-05-15',
            'married',
            'Software Developer',
            'Tech Corp',
            '555-123-4567',
            '555-987-6543',
            'john.doe@example.com',
            '123 Main St',
            'Anytown',
            'CA',
            '90210',
            'USA',
            'Jane Doe',
            '555-555-1212',
            'Spouse',
            'Active Member',
            '2023-01-01',
            '',
            'Regular',
            'Baptized',
            '2000-06-15',
            'First Baptist Church',
            'Sample member for import',
        ]);
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, $headers);
    }
}
