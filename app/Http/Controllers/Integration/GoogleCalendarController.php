<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\IntegrationSetting;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class GoogleCalendarController extends Controller
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.google.client_id');
        $this->clientSecret = config('services.google.client_secret');
        $this->redirectUri = config('services.google.redirect');
    }

    /**
     * Redirect to Google OAuth consent screen
     */
    public function connect(Request $request)
    {
        $state = Str::random(40);
        $groupId = $request->input('group_id');
        
        // Store state and group_id in session
        Session::put('google_state', $state);
        if ($groupId) {
            Session::put('google_group_id', $groupId);
        }

        $queryParams = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar',
            'access_type' => 'offline',
            'state' => $state,
            'prompt' => 'consent'
        ]);

        return redirect('https://accounts.google.com/o/oauth2/auth?' . $queryParams);
    }

    /**
     * Handle the callback from Google OAuth
     */
    public function callback(Request $request)
    {
        $state = $request->state;
        
        if ($state !== Session::get('google_state')) {
            return redirect()->route('dashboard')->with('error', 'Invalid state parameter. The request may have been tampered with.');
        }

        $code = $request->code;
        $groupId = Session::get('google_group_id');
        
        // Exchange code for access token
        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri
            ]);

            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                return redirect()->route('dashboard')->with('error', 'Failed to obtain access token from Google.');
            }

            $user = Auth::user();
            
            // Create or update integration settings
            $integration = IntegrationSetting::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'integration_type' => 'google_calendar',
                    'group_id' => $groupId,
                ],
                [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'token_expires_at' => now()->addSeconds($data['expires_in']),
                    'is_active' => true,
                    'settings' => json_encode([
                        'scopes' => ['https://www.googleapis.com/auth/calendar'],
                        'last_connected' => now()->toDateTimeString()
                    ]),
                ]
            );

            // Clear session data
            Session::forget(['google_state', 'google_group_id']);

            return redirect()->route('dashboard')->with('success', 'Successfully connected to Google Calendar!');
        } catch (\Exception $e) {
            Log::error('Google Calendar integration error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to connect to Google Calendar: ' . $e->getMessage());
        }
    }

    /**
     * Create a new event in Google Calendar
     */
    public function createEvent(Request $request)
    {
        $request->validate([
            'summary' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'location' => 'nullable|string|max:255',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $user = Auth::user();
        $groupId = $request->input('group_id');
        
        // Get the integration settings
        $integration = IntegrationSetting::where([
            'user_id' => $user->id,
            'integration_type' => 'google_calendar',
        ])
        ->when($groupId, function($query) use ($groupId) {
            return $query->where('group_id', $groupId);
        })
        ->where('is_active', true)
        ->first();
        
        if (!$integration) {
            return redirect()->route('google.calendar.connect', ['group_id' => $groupId])
                ->with('error', 'Please connect to Google Calendar first.');
        }
        
        // Check if access token is expired and refresh if needed
        if ($integration->isTokenExpired()) {
            $this->refreshAccessToken($integration);
        }

        try {
            // Add event metadata for group events
            $eventData = [
                'summary' => $request->summary,
                'description' => $request->description,
                'start' => [
                    'dateTime' => date('c', strtotime($request->start_datetime)),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => date('c', strtotime($request->end_datetime)),
                    'timeZone' => config('app.timezone'),
                ],
                'location' => $request->location,
            ];
            
            // Add source metadata for group events
            if ($groupId) {
                $group = Group::find($groupId);
                if ($group) {
                    $eventData['source'] = [
                        'title' => 'Church Management System - ' . $group->name,
                        'url' => route('groups.show', $group->id)
                    ];
                }
            }
            
            $response = Http::withToken($integration->access_token)
                ->post('https://www.googleapis.com/calendar/v3/calendars/primary/events', $eventData);

            $data = $response->json();
            
            if ($response->successful()) {
                // Update last synced timestamp
                $integration->last_synced_at = now();
                $integration->save();
                
                return redirect()->back()->with('success', 'Event created successfully in Google Calendar!');
            } else {
                Log::error('Google Calendar API error: ' . json_encode($data));
                return redirect()->back()->with('error', 'Failed to create event: ' . ($data['error']['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('Google Calendar integration error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create event: ' . $e->getMessage());
        }
    }

    /**
     * Refresh the access token using the refresh token
     */
    protected function refreshAccessToken(IntegrationSetting $integration)
    {
        try {
            if (!$integration->refresh_token) {
                Log::error('Cannot refresh token: No refresh token available');
                return false;
            }
            
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $integration->refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            $data = $response->json();
            
            if (isset($data['access_token'])) {
                $integration->access_token = $data['access_token'];
                $integration->token_expires_at = now()->addSeconds($data['expires_in']);
                $integration->save();
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to refresh Google access token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disconnect from Google Calendar
     */
    public function disconnect(Request $request)
    {
        $user = Auth::user();
        $integrationId = $request->input('integration_id');
        $groupId = $request->input('group_id');
        
        // Find the integration to disconnect
        $query = IntegrationSetting::where([
            'user_id' => $user->id,
            'integration_type' => 'google_calendar',
        ]);
        
        if ($integrationId) {
            $query->where('id', $integrationId);
        } elseif ($groupId) {
            $query->where('group_id', $groupId);
        }
        
        $integration = $query->first();
        
        if (!$integration) {
            return redirect()->route('dashboard')->with('error', 'No Google Calendar integration found.');
        }
        
        // Revoke the token
        if ($integration->access_token) {
            try {
                Http::get('https://accounts.google.com/o/oauth2/revoke', [
                    'token' => $integration->access_token
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to revoke Google token: ' . $e->getMessage());
            }
        }
        
        // Delete the integration
        $integration->delete();
        
        return redirect()->route('dashboard')->with('success', 'Successfully disconnected from Google Calendar.');
    }
}
