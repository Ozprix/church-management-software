<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\EventResourceRepositoryInterface;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventResourceController extends Controller
{
    /**
     * @var EventResourceRepositoryInterface
     */
    protected $eventResourceRepository;

    /**
     * @var GroupEventRepositoryInterface
     */
    protected $groupEventRepository;

    /**
     * EventResourceController constructor.
     *
     * @param EventResourceRepositoryInterface $eventResourceRepository
     * @param GroupEventRepositoryInterface $groupEventRepository
     */
    public function __construct(
        EventResourceRepositoryInterface $eventResourceRepository,
        GroupEventRepositoryInterface $groupEventRepository
    ) {
        $this->eventResourceRepository = $eventResourceRepository;
        $this->groupEventRepository = $groupEventRepository;
    }

    /**
     * Display a listing of the resources for an event.
     *
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($groupId, $eventId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $resources = $this->eventResourceRepository->getEventResources($eventId);

        return response()->json([
            'status' => 'success',
            'data' => $resources
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $groupId, $eventId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_file' => 'nullable|file|max:10240', // 10MB max
            'external_url' => 'nullable|url|max:255',
            'resource_type' => 'required|string|in:document,video,audio,link,other',
            'is_public' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'group_event_id' => $eventId,
                'title' => $request->title,
                'description' => $request->description,
                'resource_type' => $request->resource_type,
                'is_public' => $request->has('is_public') ? $request->is_public : false,
                'uploaded_by' => Auth::id()
            ];

            // Handle file upload
            if ($request->hasFile('resource_file')) {
                $file = $request->file('resource_file');
                $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('event_resources', $fileName, 'public');
                
                $data['file_path'] = $filePath;
                $data['file_type'] = $file->getClientMimeType();
                $data['file_size'] = $file->getSize();
            } elseif ($request->has('external_url')) {
                $data['external_url'] = $request->external_url;
            }

            $resource = $this->eventResourceRepository->createResource($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Resource created successfully',
                'data' => $resource
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create resource',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $groupId
     * @param int $eventId
     * @param int $resourceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($groupId, $eventId, $resourceId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $resource = $this->eventResourceRepository->getResourceById($resourceId);

        if (!$resource || $resource->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $resource
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $groupId
     * @param int $eventId
     * @param int $resourceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $groupId, $eventId, $resourceId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Check if resource exists and belongs to the event
        $resource = $this->eventResourceRepository->getResourceById($resourceId);
        if (!$resource || $resource->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'resource_file' => 'nullable|file|max:10240', // 10MB max
            'external_url' => 'nullable|url|max:255',
            'resource_type' => 'sometimes|required|string|in:document,video,audio,link,other',
            'is_public' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only(['title', 'description', 'resource_type', 'is_public']);

            // Handle file upload
            if ($request->hasFile('resource_file')) {
                // Delete old file if exists
                if ($resource->file_path && Storage::exists('public/' . $resource->file_path)) {
                    Storage::delete('public/' . $resource->file_path);
                }

                $file = $request->file('resource_file');
                $fileName = time() . '_' . Str::slug($request->title ?? $resource->title) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('event_resources', $fileName, 'public');
                
                $data['file_path'] = $filePath;
                $data['file_type'] = $file->getClientMimeType();
                $data['file_size'] = $file->getSize();
                $data['external_url'] = null; // Clear external URL if uploading a file
            } elseif ($request->has('external_url')) {
                $data['external_url'] = $request->external_url;
                
                // If switching from file to URL, delete the old file
                if ($resource->file_path && Storage::exists('public/' . $resource->file_path)) {
                    Storage::delete('public/' . $resource->file_path);
                    $data['file_path'] = null;
                    $data['file_type'] = null;
                    $data['file_size'] = null;
                }
            }

            $updatedResource = $this->eventResourceRepository->updateResource($resourceId, $data);

            return response()->json([
                'status' => 'success',
                'message' => 'Resource updated successfully',
                'data' => $updatedResource
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update resource',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $groupId
     * @param int $eventId
     * @param int $resourceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($groupId, $eventId, $resourceId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Check if resource exists and belongs to the event
        $resource = $this->eventResourceRepository->getResourceById($resourceId);
        if (!$resource || $resource->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found'
            ], 404);
        }

        try {
            $result = $this->eventResourceRepository->deleteResource($resourceId);

            return response()->json([
                'status' => 'success',
                'message' => 'Resource deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete resource',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public resources for an event.
     *
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicResources($eventId)
    {
        $resources = $this->eventResourceRepository->getPublicResources($eventId);

        return response()->json([
            'status' => 'success',
            'data' => $resources
        ]);
    }

    /**
     * Download a resource file.
     *
     * @param int $resourceId
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function download($resourceId)
    {
        $resource = $this->eventResourceRepository->getResourceById($resourceId);

        if (!$resource) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found'
            ], 404);
        }

        if (!$resource->file_path) {
            return response()->json([
                'status' => 'error',
                'message' => 'No file available for download'
            ], 400);
        }

        if (!Storage::exists('public/' . $resource->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found'
            ], 404);
        }

        return Storage::download('public/' . $resource->file_path, $resource->title . '.' . pathinfo($resource->file_path, PATHINFO_EXTENSION));
    }
}
