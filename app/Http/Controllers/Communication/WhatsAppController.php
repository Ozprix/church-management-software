<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    /**
     * Create a new controller instance.
     *
     * @param WhatsAppService $whatsAppService
     * @return void
     */
    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Send a text message to a single recipient.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required_without:phone|exists:members,id',
            'phone' => 'required_without:recipient_id|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the phone number either directly or from the member record
            $phone = $request->phone;
            
            if (!$phone && $request->recipient_id) {
                $member = Member::findOrFail($request->recipient_id);
                $phone = $member->phone;
                
                if (!$phone) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'The member does not have a phone number'
                    ], 422);
                }
            }

            // Send the message
            $result = $this->whatsAppService->sendTextMessage($phone, $request->message);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'WhatsApp message sent successfully',
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                    'error' => $result['error'] ?? null
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'error' => $e->getMessage(),
                'recipient_id' => $request->recipient_id,
                'phone' => $request->phone
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send WhatsApp message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a bulk message to multiple recipients.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendBulkMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_ids' => 'required_without:group_id|array',
            'recipient_ids.*' => 'exists:members,id',
            'group_id' => 'required_without:recipient_ids|exists:groups,id',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get members either directly or from the group
            $members = collect();
            
            if ($request->has('recipient_ids')) {
                $members = Member::whereIn('id', $request->recipient_ids)
                    ->whereNotNull('phone')
                    ->get();
            } else if ($request->has('group_id')) {
                // Assuming you have a Group model with a members relationship
                $group = \App\Models\Group::with('members')
                    ->findOrFail($request->group_id);
                    
                $members = $group->members()
                    ->whereNotNull('phone')
                    ->get();
            }
            
            if ($members->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No valid recipients found with phone numbers'
                ], 422);
            }

            // Send messages to each recipient
            $results = [
                'total' => $members->count(),
                'sent' => 0,
                'failed' => 0,
                'failures' => []
            ];
            
            foreach ($members as $member) {
                $result = $this->whatsAppService->sendTextMessage($member->phone, $request->message);
                
                if ($result['success']) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                    $results['failures'][] = [
                        'member_id' => $member->id,
                        'name' => $member->full_name,
                        'phone' => $member->phone,
                        'error' => $result['error'] ?? 'Unknown error'
                    ];
                }
                
                // Add a small delay to avoid rate limiting
                usleep(500000); // 500ms
            }

            return response()->json([
                'status' => 'success',
                'message' => "WhatsApp bulk message sent to {$results['sent']} recipients with {$results['failed']} failures",
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp bulk message', [
                'error' => $e->getMessage(),
                'recipient_ids' => $request->recipient_ids,
                'group_id' => $request->group_id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send WhatsApp bulk message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a template message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendTemplateMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required_without:phone|exists:members,id',
            'phone' => 'required_without:recipient_id|string',
            'template_name' => 'required|string',
            'components' => 'nullable|array',
            'language' => 'nullable|string|size:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the phone number either directly or from the member record
            $phone = $request->phone;
            
            if (!$phone && $request->recipient_id) {
                $member = Member::findOrFail($request->recipient_id);
                $phone = $member->phone;
                
                if (!$phone) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'The member does not have a phone number'
                    ], 422);
                }
            }

            // Send the template message
            $result = $this->whatsAppService->sendTemplateMessage(
                $phone, 
                $request->template_name, 
                $request->components ?? [], 
                $request->language ?? 'en_US'
            );

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'WhatsApp template message sent successfully',
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                    'error' => $result['error'] ?? null
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp template message', [
                'error' => $e->getMessage(),
                'recipient_id' => $request->recipient_id,
                'phone' => $request->phone,
                'template_name' => $request->template_name
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send WhatsApp template message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
