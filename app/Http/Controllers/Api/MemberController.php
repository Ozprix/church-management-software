<?php

namespace App\Http\Controllers\Api;

use App\Models\Member;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Resources\MemberResource;
use App\Http\Resources\MemberCollection;
use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    /**
     * @var MemberService
     */
    protected $memberService;

    /**
     * Create a new controller instance.
     *
     * @param MemberService $memberService
     */
    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
        
        // Apply middleware
        $this->middleware('auth:api');
        $this->middleware('permission:view members', ['only' => ['index', 'show']]);
        $this->middleware('permission:create members', ['only' => ['store']]);
        $this->middleware('permission:edit members', ['only' => ['update']]);
        $this->middleware('permission:delete members', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of members.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'status', 'gender', 'sort', 'order', 'per_page'
            ]);
            
            $perPage = $request->input('per_page', 15);
            $members = $this->memberService->getPaginatedMembers($filters, $perPage);
            
            return response()->json([
                'success' => true,
                'data' => new MemberCollection($members),
                'stats' => $this->memberService->getMemberStatistics(),
                'meta' => [
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'per_page' => $members->perPage(),
                    'total' => $members->total(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching members: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch members.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created member in storage.
     *
     * @param StoreMemberRequest $request
     * @return JsonResponse
     */
    public function store(StoreMemberRequest $request): JsonResponse
    {
        try {
            $member = $this->memberService->createMember($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Member created successfully.',
                'data' => new MemberResource($member->load(['user', 'family', 'groups']))
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            Log::error('Error creating member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create member.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified member.
     *
     * @param Member $member
     * @return JsonResponse
     */
    public function show(Member $member): JsonResponse
    {
        try {
            $member->load(['user', 'family', 'groups', 'attendance']);
            
            return response()->json([
                'success' => true,
                'data' => new MemberResource($member)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch member details.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified member in storage.
     *
     * @param StoreMemberRequest $request
     * @param Member $member
     * @return JsonResponse
     */
    public function update(StoreMemberRequest $request, Member $member): JsonResponse
    {
        try {
            $updatedMember = $this->memberService->updateMember($member, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully.',
                'data' => new MemberResource($updatedMember->load(['user', 'family', 'groups']))
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update member.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified member from storage.
     *
     * @param Member $member
     * @return JsonResponse
     */
    public function destroy(Member $member): JsonResponse
    {
        try {
            $this->memberService->deleteMember($member);
            
            return response()->json([
                'success' => true,
                'message' => 'Member deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete member.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Search members based on various criteria.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->input('q');
            $limit = $request->input('limit', 10);
            
            $members = Member::query()
                ->where('first_name', 'like', "%{$searchTerm}%")
                ->orWhere('last_name', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%")
                ->orWhere('phone', 'like', "%{$searchTerm}%")
                ->limit($limit)
                ->get(['id', 'first_name', 'last_name', 'email', 'phone', 'profile_photo']);
                
            return response()->json([
                'success' => true,
                'data' => $members->map(function($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'photo_url' => $member->profile_photo ? asset('storage/' . $member->profile_photo) : null,
                        'type' => 'member'
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error searching members: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search members.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Get member statistics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->memberService->getMemberStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting member stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get member statistics.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Export members to a file.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'csv');
            $filters = $request->only(['search', 'status', 'gender']);
            
            $members = $this->memberService->getMembersForExport($filters);
            $filename = 'members_export_' . now()->format('Y-m-d_His') . '.' . $format;
            
            if ($format === 'csv') {
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];
                
                $callback = function() use ($members) {
                    $file = fopen('php://output', 'w');
                    
                    // Add headers
                    fputcsv($file, [
                        'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Status', 'Membership Date', 'Gender', 'Date of Birth'
                    ]);
                    
                    // Add data
                    foreach ($members as $member) {
                        fputcsv($file, [
                            $member->id,
                            $member->first_name,
                            $member->last_name,
                            $member->email,
                            $member->phone,
                            $member->membership_status,
                            $member->membership_date ? $member->membership_date->format('Y-m-d') : '',
                            $member->gender,
                            $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : ''
                        ]);
                    }
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
                
            } elseif ($format === 'pdf') {
                // For PDF export, you would typically use a package like barryvdh/laravel-dompdf
                // This is a simplified example
                $pdf = \PDF::loadView('exports.members', ['members' => $members]);
                return $pdf->download($filename);
                
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported export format.'
                ], Response::HTTP_BAD_REQUEST);
            }
            
        } catch (\Exception $e) {
            Log::error('Error exporting members: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export members.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Import members from a file.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'update_existing' => 'sometimes|boolean'
        ]);
        
        try {
            $file = $request->file('file');
            $updateExisting = $request->boolean('update_existing', false);
            
            // Process the import
            $imported = $this->memberService->importMembers($file, $updateExisting);
            
            return response()->json([
                'success' => true,
                'message' => 'Members imported successfully.',
                'data' => [
                    'imported' => $imported['imported'],
                    'updated' => $imported['updated'],
                    'failed' => $imported['failed'],
                    'errors' => $imported['errors']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error importing members: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import members.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Perform bulk actions on members.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkActions(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,update_status,send_email,export',
            'ids' => 'required|array',
            'ids.*' => 'exists:members,id',
            'status' => 'required_if:action,update_status|in:active,inactive,pending,transferred',
            'email_subject' => 'required_if:action,send_email|string|max:255',
            'email_body' => 'required_if:action,send_email|string',
        ]);
        
        try {
            $action = $request->input('action');
            $ids = $request->input('ids');
            $results = [];
            
            switch ($action) {
                case 'delete':
                    $results = $this->memberService->bulkDelete($ids);
                    break;
                    
                case 'update_status':
                    $status = $request->input('status');
                    $results = $this->memberService->bulkUpdateStatus($ids, $status);
                    break;
                    
                case 'send_email':
                    $subject = $request->input('email_subject');
                    $body = $request->input('email_body');
                    $results = $this->memberService->bulkSendEmail($ids, $subject, $body);
                    break;
                    
                case 'export':
                    // Generate a token for the export and queue it
                    $exportToken = Str::random(32);
                    // Store the export request in cache for 1 hour
                    Cache::put('export_' . $exportToken, [
                        'type' => 'members',
                        'ids' => $ids,
                        'user_id' => auth()->id()
                    ], now()->addHour());
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Export queued successfully.',
                        'data' => [
                            'export_token' => $exportToken,
                            'download_url' => route('api.exports.download', $exportToken)
                        ]
                    ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk action completed.',
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error performing bulk action on members: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action on members.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
