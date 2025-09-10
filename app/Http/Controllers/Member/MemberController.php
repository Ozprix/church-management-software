<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Donation;
use App\Models\TaxReceipt;
use App\Models\Group;
use App\Repositories\MemberRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MemberController extends Controller
{
    /**
     * Member repository instance.
     *
     * @var \App\Repositories\MemberRepository
     */
    protected $memberRepository;

    /**
     * Create a new controller instance.
     *
     * @param \App\Repositories\MemberRepository $memberRepository
     * @return void
     */
    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'status' => $request->status,
            'sort_by' => $request->sort_by,
            'sort_dir' => $request->sort_dir ?? 'asc'
        ];
        
        $members = $this->memberRepository->getPaginatedMembers($request->per_page ?? 15, $filters);
        
        return response()->json([
            'status' => 'success',
            'data' => $members
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
            'membership_status' => 'nullable|in:active,inactive,pending,transferred',
            'membership_date' => 'nullable|date',
            'custom_fields' => 'nullable|json',
            'user_id' => 'nullable|exists:users,id',
            'family_id' => 'nullable|exists:families,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('members/photos', 'public');
            $data['profile_photo'] = $path;
        }

        $member = $this->memberRepository->createMember($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Member created successfully',
            'data' => $member
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
        $member = $this->memberRepository->getMemberById($id);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $member
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
        $member = Member::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
            'membership_status' => 'nullable|in:active,inactive,pending,transferred',
            'membership_date' => 'nullable|date',
            'custom_fields' => 'nullable|json',
            'user_id' => 'nullable|exists:users,id',
            'family_id' => 'nullable|exists:families,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($member->profile_photo) {
                Storage::disk('public')->delete($member->profile_photo);
            }
            
            $path = $request->file('profile_photo')->store('members/photos', 'public');
            $data['profile_photo'] = $path;
        }

        $member = $this->memberRepository->updateMember($id, $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Member updated successfully',
            'data' => $member
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
        $member = Member::findOrFail($id);
        
        // Delete profile photo if exists
        if ($member->profile_photo) {
            Storage::disk('public')->delete($member->profile_photo);
        }
        
        $this->memberRepository->deleteMember($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Member deleted successfully'
        ]);
    }
    
    /**
     * Get donations for a specific member
     *
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function getDonations(string $id)
    {
        $member = Member::findOrFail($id);
        
        $donations = Donation::where('member_id', $id)
            ->with(['category', 'project', 'campaign'])
            ->orderBy('donation_date', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $donations
        ]);
    }
    
    /**
     * Get donation year summary for a specific member
     *
     * @param string $memberId
     * @return \Illuminate\Http\Response
     */
    public function getDonationYearSummary(Request $request, string $memberId)
    {
        $member = Member::findOrFail($memberId);
        $year = $request->input('year', date('Y'));
        
        // Get donation summary for the specified year
        $donations = Donation::where('member_id', $memberId)
            ->whereYear('donation_date', $year)
            ->where('payment_status', 'completed')
            ->get();
            
        // Check if there's an annual tax receipt for this year
        $hasReceipt = TaxReceipt::where('member_id', $memberId)
            ->where('tax_year', $year)
            ->where('is_annual', true)
            ->where('status', '!=', 'voided')
            ->exists();
            
        return response()->json([
            'year' => (int)$year,
            'donation_count' => $donations->count(),
            'total_amount' => $donations->sum('amount'),
            'has_receipt' => $hasReceipt
        ]);
    }
    
    /**
     * Get groups that a member belongs to
     *
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function getGroups(string $id)
    {
        $member = Member::findOrFail($id);
        
        $groups = $member->groups()
            ->with('leader')
            ->get()
            ->map(function ($group) {
                // Format the data as needed
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'type' => $group->type,
                    'meeting_day' => $group->meeting_day,
                    'meeting_time' => $group->meeting_time,
                    'meeting_location' => $group->meeting_location,
                    'is_active' => $group->is_active,
                    'leader' => $group->leader ? [
                        'id' => $group->leader->id,
                        'name' => $group->leader->first_name . ' ' . $group->leader->last_name
                    ] : null,
                    'pivot' => $group->pivot
                ];
            });
            
        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }
}
