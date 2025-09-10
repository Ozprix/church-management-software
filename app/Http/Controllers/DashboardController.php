<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Member;
use App\Models\Project;
use App\Models\DonationCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get member count
            $memberCount = Member::count();
            
            // Get donations for the current month
            $currentMonth = now()->format('Y-m');
            $donationsThisMonth = Donation::whereRaw("DATE_FORMAT(donation_date, '%Y-%m') = ?", [$currentMonth])
                ->sum('amount');
                
            // Get upcoming events count
            $upcomingEvents = 3; // Placeholder - replace with actual query when Events model is available
            
            // Get recent activity
            $recentActivity = $this->getRecentActivity();
            
            // Get financial stats
            $financialStats = $this->getFinancialStats();
            
            return response()->json([
                'stats' => [
                    'members' => $memberCount,
                    'donations' => $donationsThisMonth,
                    'events' => $upcomingEvents
                ],
                'recentActivity' => $recentActivity,
                'financialStats' => $financialStats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get recent activity for the dashboard
     * 
     * @return array
     */
    private function getRecentActivity(): array
    {
        // Get recent donations
        $recentDonations = Donation::with(['member', 'category', 'project'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($donation) {
                $description = 'New donation of $' . number_format($donation->amount, 2);
                
                if ($donation->member) {
                    $description .= ' received from ' . ($donation->is_anonymous ? 'Anonymous' : $donation->member->name);
                }
                
                if ($donation->category) {
                    $description .= ' for ' . $donation->category->name;
                }
                
                if ($donation->project) {
                    $description .= ' towards project "' . $donation->project->name . '"';
                }
                
                return [
                    'description' => $description,
                    'time' => $donation->created_at->diffForHumans()
                ];
            });
            
        // Get recent projects
        $recentProjects = Project::orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($project) {
                return [
                    'description' => 'New project "' . $project->name . '" created with goal of $' . number_format($project->goal_amount, 2),
                    'time' => $project->created_at->diffForHumans()
                ];
            });
            
        // Get recent categories
        $recentCategories = DonationCategory::orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($category) {
                return [
                    'description' => 'New donation category "' . $category->name . '" added',
                    'time' => $category->created_at->diffForHumans()
                ];
            });
            
        // Combine and sort by time
        $allActivity = $recentDonations->concat($recentProjects)->concat($recentCategories);
        
        // If no real data, return placeholder data
        if ($allActivity->isEmpty()) {
            return [
                ['description' => 'New donation of $250 received from John Doe', 'time' => '2 hours ago'],
                ['description' => 'New project "Church Renovation" created', 'time' => '1 day ago'],
                ['description' => 'New donation category "Missions" added', 'time' => '2 days ago']
            ];
        }
        
        return $allActivity->sortByDesc('time')->values()->all();
    }
    
    /**
     * Get financial statistics for the dashboard
     * 
     * @return array
     */
    private function getFinancialStats(): array
    {
        // Get total donations by category
        $donationsByCategory = DonationCategory::select('donation_categories.name', DB::raw('SUM(donations.amount) as total'))
            ->leftJoin('donations', 'donation_categories.id', '=', 'donations.category_id')
            ->groupBy('donation_categories.id', 'donation_categories.name')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'total' => (float) $item->total
                ];
            });
            
        // Get project progress
        $projectProgress = Project::select('name', 'goal_amount', 'current_amount')
            ->where('status', 'active')
            ->orderBy('end_date')
            ->take(5)
            ->get()
            ->map(function ($project) {
                $percentComplete = $project->goal_amount > 0 
                    ? round(($project->current_amount / $project->goal_amount) * 100) 
                    : 0;
                    
                return [
                    'name' => $project->name,
                    'goal' => (float) $project->goal_amount,
                    'current' => (float) $project->current_amount,
                    'percent' => $percentComplete
                ];
            });
            
        return [
            'donationsByCategory' => $donationsByCategory,
            'projectProgress' => $projectProgress
        ];
    }
}
