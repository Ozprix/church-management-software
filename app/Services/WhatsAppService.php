<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $accessToken;
    protected $phoneNumberId;

    /**
     * Create a new WhatsApp service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', 'https://graph.facebook.com/v17.0');
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    /**
     * Send a text message via WhatsApp.
     *
     * @param string $to Recipient's phone number in international format (e.g., +1234567890)
     * @param string $message The message text
     * @return array Response data
     */
    public function sendTextMessage(string $to, string $message): array
    {
        try {
            if (empty($this->accessToken) || empty($this->phoneNumberId)) {
                Log::error('WhatsApp API credentials not configured');
                return [
                    'success' => false,
                    'message' => 'WhatsApp API credentials not configured'
                ];
            }

            // Format the phone number (remove any non-numeric characters except +)
            $to = preg_replace('/[^0-9+]/', '', $to);
            
            // Ensure the phone number starts with a +
            if (substr($to, 0, 1) !== '+') {
                $to = '+' . $to;
            }

            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message
                    ]
                ]);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to' => $to,
                    'message_id' => $responseData['messages'][0]['id'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $responseData
                ];
            } else {
                Log::error('Failed to send WhatsApp message', [
                    'to' => $to,
                    'error' => $responseData['error'] ?? 'Unknown error'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to send message',
                    'error' => $responseData['error'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception when sending WhatsApp message', [
                'to' => $to,
                'exception' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Exception when sending message',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send a template message via WhatsApp.
     *
     * @param string $to Recipient's phone number
     * @param string $templateName The name of the template
     * @param array $components Template components (header, body, buttons)
     * @param string $language Language code (default: en_US)
     * @return array Response data
     */
    public function sendTemplateMessage(string $to, string $templateName, array $components = [], string $language = 'en_US'): array
    {
        try {
            if (empty($this->accessToken) || empty($this->phoneNumberId)) {
                Log::error('WhatsApp API credentials not configured');
                return [
                    'success' => false,
                    'message' => 'WhatsApp API credentials not configured'
                ];
            }

            // Format the phone number
            $to = preg_replace('/[^0-9+]/', '', $to);
            if (substr($to, 0, 1) !== '+') {
                $to = '+' . $to;
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $language
                    ]
                ]
            ];

            // Add components if provided
            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('WhatsApp template message sent successfully', [
                    'to' => $to,
                    'template' => $templateName,
                    'message_id' => $responseData['messages'][0]['id'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Template message sent successfully',
                    'data' => $responseData
                ];
            } else {
                Log::error('Failed to send WhatsApp template message', [
                    'to' => $to,
                    'template' => $templateName,
                    'error' => $responseData['error'] ?? 'Unknown error'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to send template message',
                    'error' => $responseData['error'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception when sending WhatsApp template message', [
                'to' => $to,
                'template' => $templateName,
                'exception' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Exception when sending template message',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send an image message via WhatsApp.
     *
     * @param string $to Recipient's phone number
     * @param string $imageUrl URL of the image to send
     * @param string $caption Optional caption for the image
     * @return array Response data
     */
    public function sendImageMessage(string $to, string $imageUrl, string $caption = ''): array
    {
        try {
            if (empty($this->accessToken) || empty($this->phoneNumberId)) {
                Log::error('WhatsApp API credentials not configured');
                return [
                    'success' => false,
                    'message' => 'WhatsApp API credentials not configured'
                ];
            }

            // Format the phone number
            $to = preg_replace('/[^0-9+]/', '', $to);
            if (substr($to, 0, 1) !== '+') {
                $to = '+' . $to;
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'image',
                'image' => [
                    'link' => $imageUrl
                ]
            ];

            // Add caption if provided
            if (!empty($caption)) {
                $payload['image']['caption'] = $caption;
            }

            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('WhatsApp image message sent successfully', [
                    'to' => $to,
                    'image_url' => $imageUrl,
                    'message_id' => $responseData['messages'][0]['id'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Image message sent successfully',
                    'data' => $responseData
                ];
            } else {
                Log::error('Failed to send WhatsApp image message', [
                    'to' => $to,
                    'image_url' => $imageUrl,
                    'error' => $responseData['error'] ?? 'Unknown error'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to send image message',
                    'error' => $responseData['error'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception when sending WhatsApp image message', [
                'to' => $to,
                'image_url' => $imageUrl,
                'exception' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Exception when sending image message',
                'error' => $e->getMessage()
            ];
        }
    }
}
