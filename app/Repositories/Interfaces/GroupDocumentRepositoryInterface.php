<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupDocument;
use Illuminate\Pagination\LengthAwarePaginator;

interface GroupDocumentRepositoryInterface
{
    /**
     * Get all documents for a group.
     *
     * @param int $groupId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGroupDocuments(int $groupId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a document by ID.
     *
     * @param int $documentId
     * @return GroupDocument|null
     */
    public function getDocumentById(int $documentId): ?GroupDocument;

    /**
     * Create a new document.
     *
     * @param array $data
     * @return GroupDocument
     */
    public function createDocument(array $data): GroupDocument;

    /**
     * Update a document.
     *
     * @param int $documentId
     * @param array $data
     * @return bool
     */
    public function updateDocument(int $documentId, array $data): bool;

    /**
     * Delete a document.
     *
     * @param int $documentId
     * @return bool
     */
    public function deleteDocument(int $documentId): bool;

    /**
     * Record document access by a member.
     *
     * @param int $documentId
     * @param int $memberId
     * @return bool
     */
    public function recordDocumentAccess(int $documentId, int $memberId): bool;

    /**
     * Increment document download count.
     *
     * @param int $documentId
     * @return bool
     */
    public function incrementDownloadCount(int $documentId): bool;

    /**
     * Get document categories for a group.
     *
     * @param int $groupId
     * @return array
     */
    public function getDocumentCategories(int $groupId): array;

    /**
     * Get recent documents for a group.
     *
     * @param int $groupId
     * @param int $limit
     * @return array
     */
    public function getRecentDocuments(int $groupId, int $limit = 5): array;

    /**
     * Get most accessed documents for a group.
     *
     * @param int $groupId
     * @param int $limit
     * @return array
     */
    public function getMostAccessedDocuments(int $groupId, int $limit = 5): array;
}
