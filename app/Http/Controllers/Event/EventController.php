<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Event::query();
        
        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('start_time', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('start_time', '<=', $request->end_date . ' 23:59:59');
        }
        
        // Search by name or description if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        // Sort by start time by default, most recent first
        $sortField = $request->sort_by ?? 'start_time';
        $sortDirection = $request->sort_dir ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        $events = $query->paginate($request->per_page ?? 15);
        
        return response()->json([
            'status' => 'success',
            'data' => $events
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
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['created_by'] = Auth::id();
        
        $event = Event::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Event created successfully',
            'data' => $event
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
        $event = Event::findOrFail($id);
        
        // Get attendance count for this event
        $attendanceCount = Attendance::where('event_id', $id)->count();

        return response()->json([
            'status' => 'success',
            'data' => array_merge($event->toArray(), ['attendance_count' => $attendanceCount])
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
        $event = Event::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $event->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Event updated successfully',
            'data' => $event
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
        $event = Event::findOrFail($id);
        
        // Check if there are attendance records for this event
        $attendanceCount = Attendance::where('event_id', $id)->count();
        
        if ($attendanceCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete event with attendance records. Please delete the attendance records first.'
            ], 422);
        }
        
        $event->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Event deleted successfully'
        ]);
    }
}
