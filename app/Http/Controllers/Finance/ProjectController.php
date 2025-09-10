<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Project::query();
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }
        
        // Include donation count and donor count if requested
        if ($request->boolean('with_stats', false)) {
            $query->withCount('donations')
                  ->withCount(['donations as donors_count' => function ($query) {
                      $query->select(\DB::raw('count(distinct member_id)'));
                  }]);
        }
        
        $projects = $query->orderBy('created_at', 'desc')->get();
        
        // Add computed attributes
        $projects->each(function ($project) {
            $project->append(['progress_percentage', 'remaining_amount', 'is_fully_funded']);
        });
        
        return response()->json([
            'status' => 'success',
            'data' => $projects
        ]);
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,completed,cancelled',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a code from the name if not provided
        $code = $request->code ?? Str::slug($request->name);
        
        // Check if code already exists
        if (Project::where('code', $code)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'A project with this code already exists',
                'errors' => ['code' => ['The code has already been taken.']]
            ], 422);
        }
        
        // Handle image upload if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('projects', 'public');
        }
        
        $project = Project::create([
            'name' => $request->name,
            'code' => $code,
            'description' => $request->description,
            'goal_amount' => $request->goal_amount,
            'current_amount' => 0, // Start with 0
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'image_path' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        // Add computed attributes
        $project->append(['progress_percentage', 'remaining_amount', 'is_fully_funded']);

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully',
            'data' => $project
        ], 201);
    }

    /**
     * Display the specified project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::with(['creator:id,name'])->findOrFail($id);
        
        // Include donation statistics
        $project->loadCount('donations');
        $project->loadCount(['donations as donors_count' => function ($query) {
            $query->select(\DB::raw('count(distinct member_id)'));
        }]);
        
        // Add computed attributes
        $project->append(['progress_percentage', 'remaining_amount', 'is_fully_funded']);
        
        // Get recent donations
        $recentDonations = $project->donations()
            ->with(['member:id,first_name,last_name,profile_photo'])
            ->where('is_anonymous', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'project' => $project,
                'recent_donations' => $recentDonations
            ]
        ]);
    }

    /**
     * Update the specified project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'goal_amount' => 'nullable|numeric|min:0',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|required|in:active,completed,cancelled',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::findOrFail($id);
        
        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($project->image_path) {
                Storage::disk('public')->delete($project->image_path);
            }
            
            $imagePath = $request->file('image')->store('projects', 'public');
            $request->merge(['image_path' => $imagePath]);
        }
        
        $project->update($request->except(['image']));
        
        // Add computed attributes
        $project->append(['progress_percentage', 'remaining_amount', 'is_fully_funded']);

        return response()->json([
            'status' => 'success',
            'message' => 'Project updated successfully',
            'data' => $project
        ]);
    }

    /**
     * Remove the specified project from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        
        // Check if this project is being used by any donations
        if ($project->donations()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete project because it has donations'
            ], 422);
        }
        
        // Delete project image if exists
        if ($project->image_path) {
            Storage::disk('public')->delete($project->image_path);
        }
        
        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully'
        ]);
    }
    
    /**
     * Get project donations.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function donations($id)
    {
        $project = Project::findOrFail($id);
        
        $donations = $project->donations()
            ->with(['member:id,first_name,last_name,profile_photo'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return response()->json([
            'status' => 'success',
            'data' => $donations
        ]);
    }
    
    /**
     * Update project status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::findOrFail($id);
        $project->status = $request->status;
        $project->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Project status updated successfully',
            'data' => $project
        ]);
    }
}
