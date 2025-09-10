<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\IntegrationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SmsController extends Controller
{
    protected $accountSid;
    protected $authToken;
    protected $from;

    public function __construct()
    {
        // Using Twilio as the SMS provider in this example
        $this->accountSid = config('services.twilio.account_sid');
        $this->authToken = config('services.twilio.auth_token');
        $this->from = config('services.twilio.phone_number');
    }

    /**
     * Show SMS messaging form
     */
    public function index()
    {
        $user = Auth::user();
        $groups = Group::all();
        $integrations = IntegrationSetting::where([
            'user_id' => $user->id,
            'integration_type' => 'sms',
            'is_active' => true
        ])->get();
        
        return view('integration.sms.index', compact('groups', 'integrations'));
    }
    
    /**
     * Connect to SMS service
     */
    public function connect(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'group_id' => 'nullable|exists:groups,id',
        ]);
        
        $user = Auth::user();
        $groupId = $request->input('group_id');
        
        // Create or update integration settings
        $integration = IntegrationSetting::updateOrCreate(
            [
                'user_id' => $user->id,
                'integration_type' => 'sms',
                'group_id' => $groupId,
            ],
            [
                'is_active' => true,
                'settings' => json_encode([
                    'phone_number' => $request->phone_number,
                    'provider' => 'twilio'
                ]),
                'preferences' => json_encode([
                    'receive_group_notifications' => true,
                    'receive_event_reminders' => true,
                    'receive_prayer_requests' => true
                ])
            ]
        );
        
        return redirect()->back()->with('success', 'SMS integration configured successfully.');
    }
    
    /**
     * Disconnect from SMS service
     */
    public function disconnect(Request $request)
    {
        $user = Auth::user();
        $integrationId = $request->input('integration_id');
        $groupId = $request->input('group_id');
        
        // Find the integration to disconnect
        $query = IntegrationSetting::where([
            'user_id' => $user->id,
            'integration_type' => 'sms',
        ]);
        
        if ($integrationId) {
            $query->where('id', $integrationId);
        } elseif ($groupId) {
            $query->where('group_id', $groupId);
        }
        
        $integration = $query->first();
        
        if (!$integration) {
            return redirect()->route('dashboard')->with('error', 'No SMS integration found.');
        }
        
        // Delete the integration
        $integration->delete();
        
        return redirect()->route('dashboard')->with('success', 'Successfully disconnected from SMS service.');
    }

    /**
     * Send SMS to group members
     */
    public function sendToGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'message' => 'required|string|max:160',
            'send_to' => 'required|in:all,leaders,specific',
            'member_ids' => 'required_if:send_to,specific|array',
            'member_ids.*' => 'exists:group_members,id',
            'integration_id' => 'nullable|exists:integration_settings,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $group = Group::findOrFail($request->group_id);
        $message = $request->message;
        $sendTo = $request->send_to;
        $integrationId = $request->integration_id;
        
        // Get the SMS integration to use
        if ($integrationId) {
            $integration = IntegrationSetting::findOrFail($integrationId);
        } else {
            // Try to find a group-specific integration
            $integration = IntegrationSetting::where([
                'user_id' => $user->id,
                'integration_type' => 'sms',
                'group_id' => $group->id,
                'is_active' => true
            ])->first();
            
            // If no group-specific integration exists, use a general one
            if (!$integration) {
                $integration = IntegrationSetting::where([
                    'user_id' => $user->id,
                    'integration_type' => 'sms',
                    'is_active' => true
                ])->whereNull('group_id')->first();
            }
        }
        
        if (!$integration) {
            return redirect()->route('sms.connect', ['group_id' => $group->id])
                ->with('error', 'Please set up SMS integration first.');
        }
        
        // Get the members to send to based on selection
        if ($sendTo === 'all') {
            $members = $group->members;
        } elseif ($sendTo === 'leaders') {
            $members = $group->members()->whereIn('role', ['leader', 'assistant_leader'])->get();
        } else {
            $members = GroupMember::whereIn('id', $request->member_ids)->get();
        }
        
        // Send SMS to each member
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($members as $member) {
            // Skip members without phone numbers
            if (!$member->phone) {
                $failureCount++;
                continue;
            }
            
            $result = $this->sendSms($member->phone, $message, $integration);
            
            if ($result) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }
        
        // Create a log of this SMS campaign
        $this->logSmsCampaign($group->id, $message, $sendTo, $successCount, $failureCount);
        
        // Update last synced timestamp
        $integration->last_synced_at = now();
        $integration->save();
        
        if ($failureCount > 0) {
            $status = "SMS sent to {$successCount} members. Failed to send to {$failureCount} members.";
        } else {
            $status = "SMS successfully sent to all {$successCount} members.";
        }
        
        return redirect()->back()->with('success', $status);
    }

    /**
     * Send SMS to individual member
     */
    public function sendToMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:group_members,id',
            'message' => 'required|string|max:160',
            'integration_id' => 'nullable|exists:integration_settings,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $member = GroupMember::findOrFail($request->member_id);
        $message = $request->message;
        $integrationId = $request->integration_id;
        
        if (!$member->phone) {
            return redirect()->back()->with('error', 'Member does not have a phone number.');
        }
        
        // Get the SMS integration to use
        if ($integrationId) {
            $integration = IntegrationSetting::findOrFail($integrationId);
        } else {
            // Try to find a group-specific integration if member belongs to a group
            $groupMember = GroupMember::where('member_id', $member->id)->first();
            
            if ($groupMember) {
                $integration = IntegrationSetting::where([
                    'user_id' => $user->id,
                    'integration_type' => 'sms',
                    'group_id' => $groupMember->group_id,
                    'is_active' => true
                ])->first();
            }
            
            // If no group-specific integration exists, use a general one
            if (!isset($integration) || !$integration) {
                $integration = IntegrationSetting::where([
                    'user_id' => $user->id,
                    'integration_type' => 'sms',
                    'is_active' => true
                ])->whereNull('group_id')->first();
            }
        }
        
        if (!$integration) {
            return redirect()->route('sms.connect')
                ->with('error', 'Please set up SMS integration first.');
        }
        
        $result = $this->sendSms($member->phone, $message, $integration);
        
        if ($result) {
            // Log the individual message
            $this->logSmsMessage($member->id, $message);
            
            // Update last synced timestamp
            $integration->last_synced_at = now();
            $integration->save();
            
            return redirect()->back()->with('success', 'SMS sent successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to send SMS.');
        }
    }

    /**
     * Send SMS using Twilio API
     */
    protected function sendSms($to, $message, IntegrationSetting $integration)
    {
        try {
            // Format the phone number to E.164 format if not already
            if (substr($to, 0, 1) !== '+') {
                $to = '+' . preg_replace('/[^0-9]/', '', $to);
            }
            
            // Get settings from integration if available, otherwise use config
            $settings = json_decode($integration->settings, true) ?? [];
            $provider = $settings['provider'] ?? 'twilio';
            
            // Currently only supporting Twilio, but could be extended for other providers
            if ($provider === 'twilio') {
                $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                    ->post('https://api.twilio.com/2010-04-01/Accounts/' . $this->accountSid . '/Messages.json', [
                        'From' => $this->from,
                        'To' => $to,
                        'Body' => $message,
                    ]);
                
                $data = $response->json();
                
                if ($response->successful() && isset($data['sid'])) {
                    return true;
                } else {
                    Log::error('Twilio API error: ' . json_encode($data));
                    return false;
                }
            } else {
                Log::error('Unsupported SMS provider: ' . $provider);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS sending error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log SMS campaign to database
     */
    protected function logSmsCampaign($groupId, $message, $sendTo, $successCount, $failureCount)
    {
        // This would be implemented with a proper model in a real application
        Log::info("SMS Campaign: Group ID: {$groupId}, Send To: {$sendTo}, Success: {$successCount}, Failure: {$failureCount}, Message: {$message}");
    }

    /**
     * Log individual SMS message to database
     */
    protected function logSmsMessage($memberId, $message)
    {
        // This would be implemented with a proper model in a real application
        Log::info("Individual SMS: Member ID: {$memberId}, Message: {$message}");
    }
}
