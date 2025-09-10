<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberJourney;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MemberJourneyController extends Controller
{
    /**
     * Display a listing of the member's journey history.
     *
     * @param  int  $memberId
     * @return \Illuminate\Http\Response
     */
    public function index($memberId)
    {
        $member = Member::findOrFail($memberId);
        $journeys = $member->journeys()->with('updatedBy')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'member' => $member,
                'journeys' => $journeys,
                'available_stages' => MemberJourney::getStages()
            ]
        ]);
    }

    /**
     * Store a newly created journey stage in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $memberId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $memberId)
    {
        $validator = Validator::make($request->all(), [
            'stage' => 'required|string|in:visitor,regular,committed,leader',
            'stage_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $member = Member::findOrFail($memberId);
        
        // Get the previous stage
        $previousStage = $member->journey_stage;
        
        // Create new journey record
        $journey = new MemberJourney([
            'stage' => $request->stage,
            'stage_date' => $request->stage_date,
            'previous_stage' => $previousStage,
            'notes' => $request->notes,
            'updated_by' => Auth::id(),
        ]);
        
        $member->journeys()->save($journey);
        
        // Update the member's current journey stage
        $member->journey_stage = $request->stage;
        $member->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Member journey stage updated successfully',
            'data' => $journey->load('updatedBy')
        ], 201);
    }

    /**
     * Display the specified journey record.
     *
     * @param  int  $memberId
     * @param  int  $journeyId
     * @return \Illuminate\Http\Response
     */
    public function show($memberId, $journeyId)
    {
        $member = Member::findOrFail($memberId);
        $journey = $member->journeys()->with('updatedBy')->findOrFail($journeyId);
        
        return response()->json([
            'status' => 'success',
            'data' => $journey
        ]);
    }

    /**
     * Update the specified journey record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $memberId
     * @param  int  $journeyId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $memberId, $journeyId)
    {
        $validator = Validator::make($request->all(), [
            'stage' => 'sometimes|required|string|in:visitor,regular,committed,leader',
            'stage_date' => 'sometimes|required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $member = Member::findOrFail($memberId);
        $journey = $member->journeys()->findOrFail($journeyId);
        
        // Update journey record
        $journey->fill($request->only(['stage', 'stage_date', 'notes']));
        $journey->updated_by = Auth::id();
        $journey->save();
        
        // If this is the most recent journey record, update the member's current stage
        $latestJourney = $member->journeys()->orderBy('stage_date', 'desc')->first();
        if ($latestJourney->id === $journey->id) {
            $member->journey_stage = $journey->stage;
            $member->save();
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Journey record updated successfully',
            'data' => $journey->load('updatedBy')
        ]);
    }

    /**
     * Remove the specified journey record from storage.
     *
     * @param  int  $memberId
     * @param  int  $journeyId
     * @return \Illuminate\Http\Response
     */
    public function destroy($memberId, $journeyId)
    {
        $member = Member::findOrFail($memberId);
        $journey = $member->journeys()->findOrFail($journeyId);
        
        // Store the journey ID for reference
        $deletedJourneyId = $journey->id;
        
        // Delete the journey record
        $journey->delete();
        
        // Update the member's current stage based on the most recent remaining journey record
        $latestJourney = $member->journeys()->orderBy('stage_date', 'desc')->first();
        if ($latestJourney) {
            $member->journey_stage = $latestJourney->stage;
        } else {
            $member->journey_stage = 'visitor'; // Default if no journey records remain
        }
        $member->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Journey record deleted successfully',
            'data' => [
                'deleted_journey_id' => $deletedJourneyId,
                'current_stage' => $member->journey_stage
            ]
        ]);
    }
}
