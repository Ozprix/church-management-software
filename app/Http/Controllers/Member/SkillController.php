<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\SkillRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    protected $skillRepository;

    /**
     * Create a new controller instance.
     *
     * @param SkillRepositoryInterface $skillRepository
     * @return void
     */
    public function __construct(SkillRepositoryInterface $skillRepository)
    {
        $this->middleware('auth:api');
        $this->skillRepository = $skillRepository;
    }

    /**
     * Display a listing of the skills.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'active' => $request->has('active') ? filter_var($request->active, FILTER_VALIDATE_BOOLEAN) : null,
            'category' => $request->category,
        ];
        
        $perPage = $request->input('per_page', 15);
        
        $skills = $this->skillRepository->getPaginatedSkills($perPage, $filters);
        
        return response()->json([
            'status' => 'success',
            'data' => $skills
        ]);
    }

    /**
     * Store a newly created skill in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_skills')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $skill = $this->skillRepository->createSkill($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Skill created successfully',
            'data' => $skill
        ], 201);
    }

    /**
     * Display the specified skill.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $skill = $this->skillRepository->getSkillById($id);

        if (!$skill) {
            return response()->json([
                'status' => 'error',
                'message' => 'Skill not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $skill
        ]);
    }

    /**
     * Update the specified skill in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_skills')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $skill = $this->skillRepository->getSkillById($id);

        if (!$skill) {
            return response()->json([
                'status' => 'error',
                'message' => 'Skill not found'
            ], 404);
        }

        $updatedSkill = $this->skillRepository->updateSkill($id, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Skill updated successfully',
            'data' => $updatedSkill
        ]);
    }

    /**
     * Remove the specified skill from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_skills')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $skill = $this->skillRepository->getSkillById($id);

        if (!$skill) {
            return response()->json([
                'status' => 'error',
                'message' => 'Skill not found'
            ], 404);
        }

        $this->skillRepository->deleteSkill($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill deleted successfully'
        ]);
    }

    /**
     * Get all skill categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        $categories = $this->skillRepository->getAllCategories();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Get skills by category.
     *
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSkillsByCategory($category)
    {
        $skills = $this->skillRepository->getSkillsByCategory($category);

        return response()->json([
            'status' => 'success',
            'data' => $skills
        ]);
    }

    /**
     * Get skills for a specific member.
     *
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSkillsForMember($memberId)
    {
        $skills = $this->skillRepository->getSkillsForMember($memberId);

        return response()->json([
            'status' => 'success',
            'data' => $skills
        ]);
    }

    /**
     * Get members with a specific skill.
     *
     * @param Request $request
     * @param int $skillId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembersWithSkill(Request $request, $skillId)
    {
        $filters = [
            'search' => $request->search,
            'proficiency_level' => $request->proficiency_level,
            'is_verified' => $request->has('is_verified') ? filter_var($request->is_verified, FILTER_VALIDATE_BOOLEAN) : null,
        ];
        
        $perPage = $request->input('per_page', 15);
        
        $members = $this->skillRepository->getMembersWithSkill($skillId, $filters, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $members
        ]);
    }

    /**
     * Assign a skill to a member.
     *
     * @param Request $request
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignSkillToMember(Request $request, $memberId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_skills')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'skill_id' => 'required|exists:skills,id',
            'proficiency_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'notes' => 'nullable|string',
            'is_verified' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the member model
        $member = \App\Models\Member::find($memberId);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        // Prepare the pivot data
        $pivotData = [
            'proficiency_level' => $request->input('proficiency_level', 'intermediate'),
            'notes' => $request->input('notes'),
            'is_verified' => $request->input('is_verified', false),
        ];

        // Check if the relationship already exists
        if ($member->skills()->where('skill_id', $request->skill_id)->exists()) {
            $member->skills()->updateExistingPivot($request->skill_id, $pivotData);
            $message = 'Skill updated for member successfully';
        } else {
            $member->skills()->attach($request->skill_id, $pivotData);
            $message = 'Skill assigned to member successfully';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $this->skillRepository->getSkillsForMember($memberId)
        ]);
    }

    /**
     * Remove a skill from a member.
     *
     * @param int $memberId
     * @param int $skillId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeSkillFromMember($memberId, $skillId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_skills')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get the member model
        $member = \App\Models\Member::find($memberId);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        // Check if the relationship exists
        if (!$member->skills()->where('skill_id', $skillId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Skill not assigned to this member'
            ], 404);
        }

        $member->skills()->detach($skillId);

        return response()->json([
            'status' => 'success',
            'message' => 'Skill removed from member successfully',
            'data' => $this->skillRepository->getSkillsForMember($memberId)
        ]);
    }
}
