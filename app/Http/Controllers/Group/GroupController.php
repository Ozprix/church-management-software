<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\GroupServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    /**
     * @var GroupServiceInterface
     */
    protected $groupService;

    /**
     * GroupController constructor.
     *
     * @param GroupServiceInterface $groupService
     */
    public function __construct(GroupServiceInterface $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * Display a listing of the groups.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->search,
            'type' => $request->type,
            'is_active' => $request->is_active,
            'sort_by' => $request->sort_by ?? 'name',
            'sort_dir' => $request->sort_dir ?? 'asc'
        ];
        
        $groups = $this->groupService->getPaginatedGroups($request->per_page ?? 15, $filters);
        
        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }

    /**
     * Store a newly created group in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:ministry,committee,small_group,other',
            'leader_id' => 'nullable|exists:members,id',
            'meeting_day' => 'nullable|string|max:255',
            'meeting_time' => 'nullable|date_format:H:i',
            'meeting_location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = $this->groupService->createGroup($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Group created successfully',
            'data' => $group
        ], 201);
    }

    /**
     * Display the specified group.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $group = $this->groupService->getGroupById($id);

        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $group
        ]);
    }

    /**
     * Update the specified group in storage.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:ministry,committee,small_group,other',
            'leader_id' => 'nullable|exists:members,id',
            'meeting_day' => 'nullable|string|max:255',
            'meeting_time' => 'nullable|date_format:H:i',
            'meeting_location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = $this->groupService->updateGroup($id, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Group updated successfully',
            'data' => $group
        ]);
    }

    /**
     * Remove the specified group from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $this->groupService->deleteGroup($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Group deleted successfully'
        ]);
    }

    /**
     * Get group statistics.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->groupService->getGroupsStatistics();

        return response()->json([
            'status' => 'success',
            'data' => $statistics
        ]);
    }
}
