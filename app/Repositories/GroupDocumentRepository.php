<?php

namespace App\Repositories;

use App\Models\GroupDocument;
use App\Models\GroupDocumentAccess;
use App\Repositories\Interfaces\GroupDocumentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GroupDocumentRepository implements GroupDocumentRepositoryInterface
{
    /**
     * Get all documents for a group.
     *
     * @param int $groupId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGroupDocuments(int $groupId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = GroupDocument::where('group_id', $groupId)
            ->with(['uploader']);
        
        // Apply filters
        if (isset($filters['category']) && $filters['category']) {
            $query->where('category', $filters['category']);
        }
        
        if (isset($filters['file_type']) && $filters['file_type']) {
            $query->where('file_type', $filters['file_type']);
        }
        
        if (isset($filters['uploaded_by']) && $filters['uploaded_by']) {
            $query->where('uploaded_by', $filters['uploaded_by']);
        }
        
        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        // Order by
        if (isset($filters['order_by'])) {
            $direction = $filters['order_direction'] ?? 'desc';
            $query->orderBy($filters['order_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Get a document by ID.
     *
     * @param int $documentId
     * @return GroupDocument|null
     */
    public function getDocumentById(int $documentId): ?GroupDocument
    {
        return GroupDocument::with(['uploader', 'accessRecords'])->find($documentId);
    }

    /**
     * Create a new document.
     *
     * @param array $data
     * @return GroupDocument
     */
    public function createDocument(array $data): GroupDocument
    {
        return GroupDocument::create([
            'group_id' => $data['group_id'],
            'uploaded_by' => $data['uploaded_by'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size'],
            'category' => $data['category'] ?? null,
            'is_public' => $data['is_public'] ?? false,
            'download_count' => 0,
        ]);
    }

    /**
     * Update a document.
     *
     * @param int $documentId
     * @param array $data
     * @return bool
     */
    public function updateDocument(int $documentId, array $data): bool
    {
        $document = $this->getDocumentById($documentId);
        
        if (!$document) {
            return false;
        }
        
        return $document->update($data);
    }

    /**
     * Delete a document.
     *
     * @param int $documentId
     * @return bool
     */
    public function deleteDocument(int $documentId): bool
    {
        $document = $this->getDocumentById($documentId);
        
        if (!$document) {
            return false;
        }
        
        // Delete the file from storage
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }
        
        return $document->delete();
    }

    /**
     * Record document access by a member.
     *
     * @param int $documentId
     * @param int $memberId
     * @return bool
     */
    public function recordDocumentAccess(int $documentId, int $memberId): bool
    {
        $document = $this->getDocumentById($documentId);
        
        if (!$document) {
            return false;
        }
        
        return $document->recordAccess($memberId);
    }

    /**
     * Increment document download count.
     *
     * @param int $documentId
     * @return bool
     */
    public function incrementDownloadCount(int $documentId): bool
    {
        $document = $this->getDocumentById($documentId);
        
        if (!$document) {
            return false;
        }
        
        return $document->incrementDownloadCount();
    }

    /**
     * Get document categories for a group.
     *
     * @param int $groupId
     * @return array
     */
    public function getDocumentCategories(int $groupId): array
    {
        $cacheKey = "group_{$groupId}_document_categories";
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($groupId) {
            return DB::table('group_documents')
                ->where('group_id', $groupId)
                ->whereNotNull('category')
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->toArray();
        });
    }

    /**
     * Get recent documents for a group.
     *
     * @param int $groupId
     * @param int $limit
     * @return array
     */
    public function getRecentDocuments(int $groupId, int $limit = 5): array
    {
        $cacheKey = "group_{$groupId}_recent_documents_{$limit}";
        
        return Cache::remember($cacheKey, 60 * 5, function() use ($groupId, $limit) {
            return GroupDocument::where('group_id', $groupId)
                ->with('uploader')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get most accessed documents for a group.
     *
     * @param int $groupId
     * @param int $limit
     * @return array
     */
    public function getMostAccessedDocuments(int $groupId, int $limit = 5): array
    {
        $cacheKey = "group_{$groupId}_most_accessed_documents_{$limit}";
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($groupId, $limit) {
            return GroupDocument::where('group_id', $groupId)
                ->with('uploader')
                ->orderBy('download_count', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }
}
