<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\GroupDocumentRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\GroupMemberRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GroupDocumentController extends Controller
{
    /**
     * @var GroupDocumentRepositoryInterface
     */
    protected $groupDocumentRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupMemberRepositoryInterface
     */
    protected $groupMemberRepository;

    /**
     * GroupDocumentController constructor.
     *
     * @param GroupDocumentRepositoryInterface $groupDocumentRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupMemberRepositoryInterface $groupMemberRepository
     */
    public function __construct(
        GroupDocumentRepositoryInterface $groupDocumentRepository,
        GroupRepositoryInterface $groupRepository,
        GroupMemberRepositoryInterface $groupMemberRepository
    ) {
        $this->groupDocumentRepository = $groupDocumentRepository;
        $this->groupRepository = $groupRepository;
        $this->groupMemberRepository = $groupMemberRepository;
    }

    /**
     * Get all documents for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get filters from request
        $filters = [
            'category' => $request->input('category'),
            'file_type' => $request->input('file_type'),
            'uploaded_by' => $request->input('uploaded_by'),
            'is_public' => $request->input('is_public'),
            'search' => $request->input('search'),
            'order_by' => $request->input('order_by'),
            'order_direction' => $request->input('order_direction'),
        ];

        $perPage = $request->input('per_page', 15);
        $documents = $this->groupDocumentRepository->getGroupDocuments($groupId, $filters, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $documents
        ]);
    }

    /**
     * Get a specific document.
     *
     * @param int $groupId
     * @param int $documentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($groupId, $documentId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the document
        $document = $this->groupDocumentRepository->getDocumentById($documentId);
        if (!$document || $document->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document not found'
            ], 404);
        }

        // Record document access
        $this->groupDocumentRepository->recordDocumentAccess($documentId, $member->id);

        return response()->json([
            'status' => 'success',
            'data' => $document
        ]);
    }

    /**
     * Upload a new document.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Check if user has permission to upload documents
        if (!$groupMember->hasPermission('upload_documents')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to upload documents'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_public' => 'nullable|boolean',
            'file' => 'required|file|max:20480', // 20MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle file upload
        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $fileType = $file->getMimeType();
            
            // Generate a unique filename
            $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $fileExtension;
            
            // Store the file
            $filePath = $file->storeAs('group_documents/' . $groupId, $fileName, 'public');
            
            // Prepare data for document creation
            $documentData = [
                'group_id' => $groupId,
                'uploaded_by' => $member->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'category' => $request->input('category'),
                'is_public' => $request->input('is_public', false),
            ];
            
            $document = $this->groupDocumentRepository->createDocument($documentData);

            return response()->json([
                'status' => 'success',
                'message' => 'Document uploaded successfully',
                'data' => $document
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a document.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $documentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $groupId, $documentId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the document
        $document = $this->groupDocumentRepository->getDocumentById($documentId);
        if (!$document || $document->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document not found'
            ], 404);
        }

        // Check if user is the uploader or has manage_documents permission
        if ($document->uploaded_by != $member->id && !$groupMember->hasPermission('manage_documents')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this document'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_public' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare data for document update
        $documentData = [];
        
        if ($request->has('title')) {
            $documentData['title'] = $request->input('title');
        }
        
        if ($request->has('description')) {
            $documentData['description'] = $request->input('description');
        }
        
        if ($request->has('category')) {
            $documentData['category'] = $request->input('category');
        }
        
        if ($request->has('is_public')) {
            $documentData['is_public'] = $request->input('is_public');
        }

        try {
            $result = $this->groupDocumentRepository->updateDocument($documentId, $documentData);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Document updated successfully',
                    'data' => $this->groupDocumentRepository->getDocumentById($documentId)
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update document'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a document.
     *
     * @param int $groupId
     * @param int $documentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($groupId, $documentId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the document
        $document = $this->groupDocumentRepository->getDocumentById($documentId);
        if (!$document || $document->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document not found'
            ], 404);
        }

        // Check if user is the uploader or has manage_documents permission
        if ($document->uploaded_by != $member->id && !$groupMember->hasPermission('manage_documents')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete this document'
            ], 403);
        }

        try {
            $result = $this->groupDocumentRepository->deleteDocument($documentId);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Document deleted successfully'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete document'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a document.
     *
     * @param int $groupId
     * @param int $documentId
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function download($groupId, $documentId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the document
        $document = $this->groupDocumentRepository->getDocumentById($documentId);
        if (!$document || $document->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document not found'
            ], 404);
        }

        // Record document access and increment download count
        $this->groupDocumentRepository->recordDocumentAccess($documentId, $member->id);
        $this->groupDocumentRepository->incrementDownloadCount($documentId);

        // Check if file exists
        if (!Storage::exists($document->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found on server'
            ], 404);
        }

        // Get file content
        $fileContent = Storage::get($document->file_path);
        $fileName = basename($document->file_path);

        // Return file for download
        return response($fileContent, 200, [
            'Content-Type' => $document->file_type,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => $document->file_size,
        ]);
    }

    /**
     * Get document categories for a group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories($groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        $categories = $this->groupDocumentRepository->getDocumentCategories($groupId);

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Get recent documents for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentDocuments(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        $limit = $request->input('limit', 5);
        $recentDocuments = $this->groupDocumentRepository->getRecentDocuments($groupId, $limit);

        return response()->json([
            'status' => 'success',
            'data' => $recentDocuments
        ]);
    }

    /**
     * Get most accessed documents for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMostAccessedDocuments(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        $limit = $request->input('limit', 5);
        $mostAccessedDocuments = $this->groupDocumentRepository->getMostAccessedDocuments($groupId, $limit);

        return response()->json([
            'status' => 'success',
            'data' => $mostAccessedDocuments
        ]);
    }
}
