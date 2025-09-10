<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['member', 'event']);
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereHas('event', function($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_date, $request->end_date]);
            });
        }
        
        // Filter by event if provided
        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        
        // Filter by member if provided
        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        
        // Sort by check-in time by default
        $sortField = $request->sort_by ?? 'check_in_time';
        $sortDirection = $request->sort_dir ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        $attendances = $query->paginate($request->per_page ?? 15);
        
        return response()->json([
            'status' => 'success',
            'data' => $attendances
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
            'event_id' => 'required|exists:events,id',
            'member_id' => 'required|exists:members,id',
            'check_in_time' => 'required|date',
            'check_out_time' => 'nullable|date|after:check_in_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if attendance record already exists for this member and event
        $existingAttendance = Attendance::where('event_id', $request->event_id)
            ->where('member_id', $request->member_id)
            ->first();
            
        if ($existingAttendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record already exists for this member and event',
                'data' => $existingAttendance
            ], 422);
        }

        $attendance = Attendance::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance recorded successfully',
            'data' => $attendance
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
        $attendance = Attendance::with(['member', 'event'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $attendance
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
        $attendance = Attendance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'event_id' => 'sometimes|required|exists:events,id',
            'member_id' => 'sometimes|required|exists:members,id',
            'check_in_time' => 'sometimes|required|date',
            'check_out_time' => 'nullable|date|after:check_in_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If changing event_id or member_id, check for duplicates
        if (($request->has('event_id') && $request->event_id != $attendance->event_id) || 
            ($request->has('member_id') && $request->member_id != $attendance->member_id)) {
            
            $existingAttendance = Attendance::where('event_id', $request->event_id ?? $attendance->event_id)
                ->where('member_id', $request->member_id ?? $attendance->member_id)
                ->where('id', '!=', $id)
                ->first();
                
            if ($existingAttendance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attendance record already exists for this member and event',
                    'data' => $existingAttendance
                ], 422);
            }
        }

        $attendance->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance updated successfully',
            'data' => $attendance
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
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance record deleted successfully'
        ]);
    }
    
    /**
     * Get attendance records for a specific event.
     *
     * @param  string  $event_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function byEvent(string $event_id, Request $request)
    {
        $event = Event::findOrFail($event_id);
        
        $query = Attendance::with('member')
            ->where('event_id', $event_id);
            
        // Sort by check-in time by default
        $sortField = $request->sort_by ?? 'check_in_time';
        $sortDirection = $request->sort_dir ?? 'asc';
        $query->orderBy($sortField, $sortDirection);
        
        $attendances = $query->paginate($request->per_page ?? 50);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'event' => $event,
                'attendances' => $attendances
            ]
        ]);
    }
    
    /**
     * Get attendance records for a specific member.
     *
     * @param  string  $member_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function byMember(string $member_id, Request $request)
    {
        $member = Member::findOrFail($member_id);
        
        $query = Attendance::with('event')
            ->where('member_id', $member_id);
            
        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereHas('event', function($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_date, $request->end_date]);
            });
        }
        
        // Sort by event date by default
        $query->join('events', 'attendances.event_id', '=', 'events.id')
              ->orderBy('events.start_time', 'desc')
              ->select('attendances.*');
        
        $attendances = $query->paginate($request->per_page ?? 15);
        
        // Calculate attendance statistics
        $totalEvents = Event::count();
        $attendedEvents = $attendances->total();
        $attendanceRate = $totalEvents > 0 ? round(($attendedEvents / $totalEvents) * 100, 1) : 0;
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'member' => $member,
                'statistics' => [
                    'total_events' => $totalEvents,
                    'attended_events' => $attendedEvents,
                    'attendance_rate' => $attendanceRate
                ],
                'attendances' => $attendances
            ]
        ]);
    }
}
