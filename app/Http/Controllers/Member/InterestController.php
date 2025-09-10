<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\InterestRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class InterestController extends Controller
{
    protected $interestRepository;

    /**
     * Create a new controller instance.
     *
     * @param InterestRepositoryInterface $interestRepository
     * @return void
     */
    public function __construct(InterestRepositoryInterface $interestRepository)
    {
        $this->middleware('auth:api');
        $this->interestRepository = $interestRepository;
    }

    /**
     * Display a listing of the interests.
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
        
        $interests = $this->interestRepository->getPaginatedInterests($perPage, $filters);
        
        return response()->json([
            'status' => 'success',
            'data' => $interests
        ]);
    }

    /**
     * Store a newly created interest in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_interests')) {
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

        $interest = $this->interestRepository->createInterest($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Interest created successfully',
            'data' => $interest
        ], 201);
    }

    /**
     * Display the specified interest.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $interest = $this->interestRepository->getInterestById($id);

        if (!$interest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Interest not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $interest
        ]);
    }

    /**
     * Update the specified interest in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_interests')) {
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

        $interest = $this->interestRepository->getInterestById($id);

        if (!$interest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Interest not found'
            ], 404);
        }

        $updatedInterest = $this->interestRepository->updateInterest($id, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Interest updated successfully',
            'data' => $updatedInterest
        ]);
    }

    /**
     * Remove the specified interest from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_interests')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $interest = $this->interestRepository->getInterestById($id);

        if (!$interest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Interest not found'
            ], 404);
        }

        $this->interestRepository->deleteInterest($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Interest deleted successfully'
        ]);
    }

    /**
     * Get all interest categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        $categories = $this->interestRepository->getAllCategories();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Get interests by category.
     *
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInterestsByCategory($category)
    {
        $interests = $this->interestRepository->getInterestsByCategory($category);

        return response()->json([
            'status' => 'success',
            'data' => $interests
        ]);
    }

    /**
     * Get interests for a specific member.
     *
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInterestsForMember($memberId)
    {
        $interests = $this->interestRepository->getInterestsForMember($memberId);

        return response()->json([
            'status' => 'success',
            'data' => $interests
        ]);
    }

    /**
     * Get members with a specific interest.
     *
     * @param Request $request
     * @param int $interestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembersWithInterest(Request $request, $interestId)
    {
        $filters = [
            'search' => $request->search,
            'interest_level' => $request->interest_level,
        ];
        
        $perPage = $request->input('per_page', 15);
        
        $members = $this->interestRepository->getMembersWithInterest($interestId, $filters, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $members
        ]);
    }

    /**
     * Assign an interest to a member.
     *
     * @param Request $request
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignInterestToMember(Request $request, $memberId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_interests')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'interest_id' => 'required|exists:interests,id',
            'interest_level' => 'nullable|in:low,medium,high',
            'notes' => 'nullable|string',
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
            'interest_level' => $request->input('interest_level', 'medium'),
            'notes' => $request->input('notes'),
        ];

        // Check if the relationship already exists
        if ($member->interests()->where('interest_id', $request->interest_id)->exists()) {
            $member->interests()->updateExistingPivot($request->interest_id, $pivotData);
            $message = 'Interest updated for member successfully';
        } else {
            $member->interests()->attach($request->interest_id, $pivotData);
            $message = 'Interest assigned to member successfully';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $this->interestRepository->getInterestsForMember($memberId)
        ]);
    }

    /**
     * Remove an interest from a member.
     *
     * @param int $memberId
     * @param int $interestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeInterestFromMember($memberId, $interestId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_interests')) {
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
        if (!$member->interests()->where('interest_id', $interestId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Interest not assigned to this member'
            ], 404);
        }

        $member->interests()->detach($interestId);

        return response()->json([
            'status' => 'success',
            'message' => 'Interest removed from member successfully',
            'data' => $this->interestRepository->getInterestsForMember($memberId)
        ]);
    }
}
