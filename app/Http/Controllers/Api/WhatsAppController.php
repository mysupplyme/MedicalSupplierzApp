<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI;

class WhatsAppController extends Controller
{
    private $accessToken;
    private $phoneNumberId;
    private $baseUrl = 'https://graph.facebook.com/v18.0';

    public function __construct()
    {
        $this->accessToken = env('WHATSAPP_ACCESS_TOKEN');
        $this->phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
    }

    public function webhook(Request $request)
    {
        // Log ALL requests
        Log::info('WhatsApp Webhook Request:', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'query' => $request->query->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent()
        ]);
        
        // Handle GET request for webhook verification
        if ($request->isMethod('GET')) {
            if ($request->query('hub_mode') === 'subscribe' && 
                $request->query('hub_verify_token') === env('WHATSAPP_VERIFY_TOKEN')) {
                Log::info('Webhook verification successful');
                return response($request->query('hub_challenge'), 200)
                    ->header('Content-Type', 'text/plain');
            }
            Log::error('Webhook verification failed');
            return response('Forbidden', 403);
        }

        $data = $request->all();
        Log::info('WhatsApp Webhook:', $data);

        if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
            $from = $message['from'];
            
            Log::info('Processing message:', [
                'from' => $from,
                'message_type' => $message['type'] ?? 'unknown',
                'text' => $message['text']['body'] ?? 'no text',
                'has_interactive' => isset($message['interactive'])
            ]);
            
            if (isset($message['interactive'])) {
                Log::info('Handling interactive message');
                $this->handleInteractive($from, $message['interactive']);
            } elseif (isset($message['text'])) {
                Log::info('Handling text message: ' . $message['text']['body']);
                $this->handleText($from, $message['text']['body']);
            }
        } else {
            Log::warning('No message found in webhook data');
        }

        return response()->json(['status' => 'ok']);
    }

    public function test(Request $request)
    {
        Log::info('WhatsApp Test Endpoint Hit:', $request->all());
        
        // Test API connection
        $testPayload = [
            'messaging_product' => 'whatsapp',
            'to' => '96594089218', // Your test number
            'type' => 'text',
            'text' => ['body' => 'Test message from API']
        ];
        
        Log::info('Testing WhatsApp API with:', [
            'token' => substr($this->accessToken, 0, 20) . '...',
            'phone_id' => $this->phoneNumberId,
            'url' => "{$this->baseUrl}/{$this->phoneNumberId}/messages"
        ]);
        
        $response = $this->sendMessage($testPayload);
        
        return response()->json([
            'message' => 'Test endpoint working', 
            'timestamp' => now(),
            'api_response' => $response
        ]);
    }

    private function handleInteractive($from, $interactive)
    {
        $buttonId = $interactive['button_reply']['id'] ?? $interactive['list_reply']['id'] ?? '';
        
        switch ($buttonId) {
            case 'SUPPLIER_REG':
                $this->sendSupplierMenu($from);
                break;
            case 'BUYER_REG':
                $this->sendBuyerMenu($from);
                break;
            case 'MED_PERSONNEL':
                $this->sendCMEMenu($from);
                break;
            case 'SUP_SIGNUP':
                $this->sendText($from, "✅ Great. Please complete your profile here: https://supplier.medicalsupplierz.com\nNeed help? Tap \"Talk to Sales\".");
                break;
            case 'BUY_SIGNUP':
                $this->sendText($from, "✅ Create your free buyer account: https://medicalsupplierz.com/b2b-register\nInvite your procurement team inside your dashboard.");
                break;
            case 'CME_SUBSCRIBE':
                $this->sendText($from, "✅ Subscribe here: https://medicalsupplierz.com/doctor-register\nYour credits, centralized. Your career, compounded.");
                break;
        }
    }

    private function handleText($from, $text)
    {
        Log::info('handleText called with:', ['from' => $from, 'text' => $text]);
        
        // Get AI response
        $response = $this->getAIResponse($text);
        $this->sendText($from, $response['message']);
        
        // Send appropriate menu
        switch ($response['action']) {
            case 'supplier':
                $this->sendSupplierMenu($from);
                break;
            case 'buyer':
                $this->sendBuyerMenu($from);
                break;
            case 'cme':
                $this->sendCMEMenu($from);
                break;
            default:
                $this->sendWelcomeMenu($from);
        }
    }
    
    private function getAIResponse($userMessage)
    {
        Log::info('getAIResponse called with:', ['message' => $userMessage]);
        
        try {
            Log::info('Creating OpenAI client...');
            $client = OpenAI::client(env('OPENAI_API_KEY'));
            
            $prompt = "You are MedicalSupplierz.com assistant. Respond in under 160 chars. Based on user message, return JSON with 'message' and 'action' (supplier/buyer/cme/welcome).
            
User: {$userMessage}";
            
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => 80
            ]);
            
            $aiResponse = $response->choices[0]->message->content;
            $decoded = json_decode($aiResponse, true);
            
            if ($decoded && isset($decoded['message']) && isset($decoded['action'])) {
                return $decoded;
            }
            
            throw new \Exception('Invalid AI response format');
            
        } catch (\Exception $e) {
            Log::error('AI Error:', ['error' => $e->getMessage()]);
            
            // Fallback to keyword matching
            $text = strtolower($userMessage);
            if (strpos($text, 'supplier') !== false || strpos($text, 'sell') !== false) {
                return ['message' => '🚀 Great! I can help you with supplier registration.', 'action' => 'supplier'];
            } elseif (strpos($text, 'buyer') !== false || strpos($text, 'hospital') !== false) {
                return ['message' => '🏥 Perfect! Let me show you buyer options.', 'action' => 'buyer'];
            } elseif (strpos($text, 'doctor') !== false || strpos($text, 'cme') !== false) {
                return ['message' => '🩺 Excellent! Here are medical education options.', 'action' => 'cme'];
            } else {
                return ['message' => '👋 Welcome to MedicalSupplierz.com! We connect medical suppliers with buyers globally.', 'action' => 'welcome'];
            }
        }
    }

    private function sendWelcomeMenu($to)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => "Welcome to MedicalSupplierz.com — where global medical trade and CME pathways converge.\nHow can we help you today?"],
                'action' => [
                    'buttons' => [
                        ['type' => 'reply', 'reply' => ['id' => 'SUPPLIER_REG', 'title' => 'Supplier Setup']],
                        ['type' => 'reply', 'reply' => ['id' => 'BUYER_REG', 'title' => 'Buyer Registration']],
                        ['type' => 'reply', 'reply' => ['id' => 'MED_PERSONNEL', 'title' => 'Medical Personnel']]
                    ]
                ]
            ]
        ];
        
        $this->sendMessage($payload);
    }

    private function sendSupplierMenu($to)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => "🚀 Suppliers win here. List unlimited products, access global buyers, capture qualified leads.\nSubscription: $100/mo or $1,000/yr.\nWhat do you want to do next?"],
                'action' => [
                    'buttons' => [
                        ['type' => 'reply', 'reply' => ['id' => 'SUP_SIGNUP', 'title' => 'Start Supplier Signup']],
                        ['type' => 'reply', 'reply' => ['id' => 'SUP_BENEFITS', 'title' => 'See Benefits']],
                        ['type' => 'reply', 'reply' => ['id' => 'SUP_SALES', 'title' => 'Talk to Sales']]
                    ]
                ]
            ]
        ];
        
        $this->sendMessage($payload);
    }

    private function sendBuyerMenu($to)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => "🏥 Buyers register free. Search global suppliers, compare products, post bulk RFQs, negotiate directly.\nWhat's your next step?"],
                'action' => [
                    'buttons' => [
                        ['type' => 'reply', 'reply' => ['id' => 'BUY_SIGNUP', 'title' => 'Create Free Buyer Account']],
                        ['type' => 'reply', 'reply' => ['id' => 'BUY_POST_RFQ', 'title' => 'Post RFQ Now']],
                        ['type' => 'reply', 'reply' => ['id' => 'BUY_CATEGORIES', 'title' => 'Explore Categories']]
                    ]
                ]
            ]
        ];
        
        $this->sendMessage($payload);
    }

    private function sendCMEMenu($to)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => "🩺 Premium Membership: $5/mo or $50/yr\nDiscover CME events, track credits & download certificates.\nChoose an option:"],
                'action' => [
                    'buttons' => [
                        ['type' => 'reply', 'reply' => ['id' => 'CME_SUBSCRIBE', 'title' => 'Subscribe Now']],
                        ['type' => 'reply', 'reply' => ['id' => 'CME_BY_SPEC', 'title' => 'Find CME by Specialty']],
                        ['type' => 'reply', 'reply' => ['id' => 'CME_MONTH', 'title' => 'Upcoming This Month']]
                    ]
                ]
            ]
        ];
        
        $this->sendMessage($payload);
    }

    private function sendText($to, $text)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $text]
        ];
        
        $this->sendMessage($payload);
    }

    private function sendMessage($payload)
    {
        Log::info('Sending WhatsApp message:', [
            'payload' => $payload,
            'url' => "{$this->baseUrl}/{$this->phoneNumberId}/messages",
            'token_preview' => substr($this->accessToken, 0, 20) . '...'
        ]);
        
        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $payload);
                
            Log::info('WhatsApp API Response:', [
                'status' => $response->status(),
                'body' => $response->json(),
                'headers' => $response->headers()
            ]);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp API Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => $e->getMessage()];
        }
    }
}