<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FamilyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Family::query();
        
        // Search by name if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Sort by field if provided
        if ($request->has('sort_by')) {
            $sortField = $request->sort_by;
            $sortDirection = $request->has('sort_dir') ? $request->sort_dir : 'asc';
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $families = $query->paginate($request->per_page ?? 15);
        
        return response()->json([
            'status' => 'success',
            'data' => $families
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'head_member_id' => 'nullable|exists:members,id',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'communication_preference' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $family = Family::create($request->all());

        // If head_member_id is provided, create a family member relationship
        if ($request->has('head_member_id') && $request->head_member_id) {
            FamilyMember::create([
                'family_id' => $family->id,
                'member_id' => $request->head_member_id,
                'relationship' => 'head'
            ]);
            
            // Update the member's family_id
            $member = Member::find($request->head_member_id);
            if ($member) {
                $member->update(['family_id' => $family->id]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Family created successfully',
            'data' => $family
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $family = Family::with('members')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $family
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        $family = Family::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'head_member_id' => 'nullable|exists:members,id',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'communication_preference' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $family->update($request->all());

        // If head_member_id is changed, update the family member relationship
        if ($request->has('head_member_id') && $request->head_member_id != $family->head_member_id) {
            // Remove old head if exists
            FamilyMember::where('family_id', $family->id)
                ->where('relationship', 'head')
                ->delete();
            
            // Add new head
            if ($request->head_member_id) {
                FamilyMember::create([
                    'family_id' => $family->id,
                    'member_id' => $request->head_member_id,
                    'relationship' => 'head'
                ]);
                
                // Update the member's family_id
                $member = Member::find($request->head_member_id);
                if ($member) {
                    $member->update(['family_id' => $family->id]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Family updated successfully',
            'data' => $family
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        $family = Family::findOrFail($id);
        
        // Remove family member relationships
        FamilyMember::where('family_id', $family->id)->delete();
        
        // Update members to remove family_id
        Member::where('family_id', $family->id)->update(['family_id' => null]);
        
        $family->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Family deleted successfully'
        ]);
    }
    
    /**
     * Get all members of a family.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function members(string $id)
    {
        $family = Family::findOrFail($id);
        
        $members = Member::where('family_id', $family->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'family' => $family->name,
                'members' => $members
            ]
        ]);
    }
}
