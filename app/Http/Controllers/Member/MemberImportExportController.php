<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Imports\MembersImport;
use App\Exports\MembersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class MemberImportExportController extends Controller
{
    /**
     * Import members from Excel/CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $import = new MembersImport();
            Excel::import($import, $request->file('file'));
            
            $results = $import->getImportResults();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Members imported successfully',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export members to Excel file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['status', 'journey_stage', 'search', 'family_id']);
            
            return Excel::download(
                new MembersExport($filters),
                'members_' . date('Y-m-d_His') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download member import template.
     *
     * @return \Illuminate\Http\Response
     */
    public function template()
    {
        try {
            // Create a template with no data but with headings
            return Excel::download(
                new MembersExport(),
                'members_import_template.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template generation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
