<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\SpiritualGiftRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SpiritualGiftController extends Controller
{
    protected $spiritualGiftRepository;

    /**
     * Create a new controller instance.
     *
     * @param SpiritualGiftRepositoryInterface $spiritualGiftRepository
     * @return void
     */
    public function __construct(SpiritualGiftRepositoryInterface $spiritualGiftRepository)
    {
        $this->middleware('auth:api');
        $this->spiritualGiftRepository = $spiritualGiftRepository;
    }

    /**
     * Display a listing of the spiritual gifts.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'active' => $request->has('active') ? filter_var($request->active, FILTER_VALIDATE_BOOLEAN) : null,
        ];
        
        $perPage = $request->input('per_page', 15);
        
        $spiritualGifts = $this->spiritualGiftRepository->getPaginatedSpiritualGifts($perPage, $filters);
        
        return response()->json([
            'status' => 'success',
            'data' => $spiritualGifts
        ]);
    }

    /**
     * Store a newly created spiritual gift in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('manage_spiritual_gifts')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scripture_reference' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $spiritualGift = $this->spiritualGiftRepository->createSpiritualGift($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Spiritual gift created successfully',
            'data' => $spiritualGift
        ], 201);
    }

    /**
     * Display the specified spiritual gift.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $spiritualGift = $this->spiritualGiftRepository->getSpiritualGiftById($id);

        if (!$spiritualGift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Spiritual gift not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $spiritualGift
        ]);
    }

    /**
     * Update the specified spiritual gift in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->can('manage_spiritual_gifts')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'scripture_reference' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $spiritualGift = $this->spiritualGiftRepository->getSpiritualGiftById($id);

        if (!$spiritualGift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Spiritual gift not found'
            ], 404);
        }

        $updatedSpiritualGift = $this->spiritualGiftRepository->updateSpiritualGift($id, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Spiritual gift updated successfully',
            'data' => $updatedSpiritualGift
        ]);
    }

    /**
     * Remove the specified spiritual gift from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->can('manage_spiritual_gifts')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $spiritualGift = $this->spiritualGiftRepository->getSpiritualGiftById($id);

        if (!$spiritualGift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Spiritual gift not found'
            ], 404);
        }

        $this->spiritualGiftRepository->deleteSpiritualGift($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Spiritual gift deleted successfully'
        ]);
    }

    /**
     * Get spiritual gifts for a specific member.
     *
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpiritualGiftsForMember($memberId)
    {
        $spiritualGifts = $this->spiritualGiftRepository->getSpiritualGiftsForMember($memberId);

        return response()->json([
            'status' => 'success',
            'data' => $spiritualGifts
        ]);
    }

    /**
     * Get members with a specific spiritual gift.
     *
     * @param Request $request
     * @param int $spiritualGiftId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembersWithSpiritualGift(Request $request, $spiritualGiftId)
    {
        $filters = [
            'search' => $request->search,
            'strength_level' => $request->strength_level,
            'is_assessed' => $request->has('is_assessed') ? filter_var($request->is_assessed, FILTER_VALIDATE_BOOLEAN) : null,
        ];
        
        $perPage = $request->input('per_page', 15);
        
        $members = $this->spiritualGiftRepository->getMembersWithSpiritualGift($spiritualGiftId, $filters, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $members
        ]);
    }

    /**
     * Get spiritual gift distribution statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpiritualGiftDistribution()
    {
        $distribution = $this->spiritualGiftRepository->getSpiritualGiftDistribution();

        return response()->json([
            'status' => 'success',
            'data' => $distribution
        ]);
    }

    /**
     * Assign a spiritual gift to a member.
     *
     * @param Request $request
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignSpiritualGiftToMember(Request $request, $memberId)
    {
        // Check permission
        if (!Auth::user()->can('manage_spiritual_gifts')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'spiritual_gift_id' => 'required|exists:spiritual_gifts,id',
            'strength_level' => 'nullable|in:low,medium,high',
            'notes' => 'nullable|string',
            'is_assessed' => 'boolean',
            'assessment_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->spiritualGiftRepository->assignSpiritualGiftToMember(
            $memberId,
            $request->spiritual_gift_id,
            $request->only(['strength_level', 'notes', 'is_assessed', 'assessment_date'])
        );

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to assign spiritual gift to member'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Spiritual gift assigned to member successfully',
            'data' => $this->spiritualGiftRepository->getSpiritualGiftsForMember($memberId)
        ]);
    }

    /**
     * Remove a spiritual gift from a member.
     *
     * @param int $memberId
     * @param int $spiritualGiftId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeSpiritualGiftFromMember($memberId, $spiritualGiftId)
    {
        // Check permission
        if (!Auth::user()->can('manage_spiritual_gifts')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $result = $this->spiritualGiftRepository->removeSpiritualGiftFromMember($memberId, $spiritualGiftId);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove spiritual gift from member'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Spiritual gift removed from member successfully',
            'data' => $this->spiritualGiftRepository->getSpiritualGiftsForMember($memberId)
        ]);
    }
}
