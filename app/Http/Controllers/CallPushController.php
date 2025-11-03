<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use App\Models\LeadCall;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Google\Client as GoogleClient;
use GuzzleHttp\Client;

class CallPushController extends Controller
{
    /**
     * 🔹 Register or update FCM token (Flutter app)
     */
    public function registerToken(Request $request)
    {
        // dd('');
        $data = $request->validate([
            'token' => 'required|string',
            'platform' => 'required|string|in:android,ios',
            'user_id' => 'nullable|string'
        ]);

        $device = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'platform' => $data['platform'],
                'user_id' => $data['user_id'] ?? null,
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'ok',
            'device' => $device
        ]);
    }

    /**
     * 🔹 Generate a unique call id (for logs or reference)
     */
    public function generateCallId()
    {
        return response()->json(['call_id' => (string) Str::uuid()]);
    }

    /**
     * 🔹 Trigger call push from web side
     */
    public function sendCall(Request $request)
    {
        // dd($request->all());
        $payload = $request->validate([
            'number' => 'required|string',
            'deal_id' => 'required|string',
            'from' => 'nullable|string',
            'user_id' => 'nullable|string',
            'to_token' => 'nullable|string',
            'to_tokens' => 'nullable|array',
            'title' => 'nullable|string',
            'body' => 'nullable|string'
        ]);

        // Collect tokens
        $targets = [];

        if (!empty($payload['to_token']))
            $targets[] = $payload['to_token'];
        if (!empty($payload['to_tokens']))
            $targets = array_merge($targets, $payload['to_tokens']);

        if (!empty($payload['user_id'])) {
            $tokens = DeviceToken::where('user_id', $payload['user_id'])->pluck('token')->toArray();
            $targets = array_merge($targets, $tokens);
        }

        $targets = array_unique($targets);

        if (empty($targets)) {
            return response()->json(['error' => 'No device tokens found'], 404);
        }

        // Build payload
        $callId = (string) Str::uuid();
        $data = [
            'type' => 'incoming_call',
            'deal_id' => $payload['deal_id'] ?? null,
            'call_id' => $callId,
            'number' => $payload['number'],
            'from' => $payload['from'] ?? 'Unknown',
            'timestamp' => now()->toIso8601String(),
        ];

        $notification = [
            'title' => $payload['title'] ?? 'Incoming Call',
            'body' => $payload['body'] ?? 'You have an incoming call'
        ];

        $response = $this->sendFcmData($targets, $data, $notification);

        return response()->json([
            'status' => 'sent',
            'targets' => count($targets),
            'response' => $response
        ]);
    }
    public function callResponse(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'deal_id' => 'required|string',
                'to_number' => 'required|string',
                'subject' => 'nullable|string',
                'call_type' => 'required|string',
                'duration' => 'required|string',
                'start' => 'required|string',
                'end' => 'required|string',
                'recording' => 'nullable',
                'status' => 'required|string',
                'user_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $payload = $validator->validated();

            $call = new LeadCall();
            $call->deal_id = $payload['deal_id'];
            $call->to_number = $payload['to_number'];
            $call->subject = $payload['subject'] ?? null;
            $call->call_type = $payload['call_type'];
            $call->duration = $payload['duration'];
            $call->start = $payload['start'];
            $call->end = $payload['end'];
            $call->status = $payload['status'];
            $call->user_id = $payload['user_id'];

            if ($request->hasFile('recording')) {
                $file = $request->file('recording');
                $filename = uniqid('rec_') . '.' . $file->getClientOriginalExtension();
                $file->storeAs('uploads/recordings', $filename, 'public');
                $call->recording = $filename;
            } elseif (!empty($payload['recording']) && $payload['recording'] !== 'null') {
                $call->recording = $payload['recording'];
            }

            $call->save();

            return response()->json([
                'status' => 'ok',
                'message' => 'Call record saved successfully.',
                'call' => $call
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Internal: Send FCM Message via v1 API
     */
    protected function sendFcmData(array $tokens, array $data, $notification = null)
    {
        $projectId = env('FIREBASE_PROJECT_ID');
        $credentialsPath = public_path('firebase-portal-476611-e608f307a9d1.json');

        if (!file_exists($credentialsPath)) {
            return ['error' => 'Firebase credentials missing'];
        }

        // Generate OAuth2 Access Token
        $google = new GoogleClient();
        $google->setAuthConfig($credentialsPath);
        $google->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $google->refreshTokenWithAssertion();
        $accessToken = $google->getAccessToken()['access_token'] ?? null;

        if (!$accessToken) {
            return ['error' => 'Failed to get access token'];
        }

        $http = new Client();
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $results = [];

        foreach ($tokens as $token) {
            try {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => $notification,
                        'data' => $data,
                    ]
                ];

                $res = $http->post($url, [
                    'headers' => [
                        'Authorization' => "Bearer {$accessToken}",
                        'Content-Type' => 'application/json'
                    ],
                    'json' => $payload
                ]);

                $results[] = json_decode($res->getBody(), true);
            } catch (\Exception $e) {
                $results[] = ['token' => $token, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }


    // protected function sendFcmData(array $tokens, array $data, $notification = null)
    // {
    //     try {
    //         $serverKey = config('services.fcm.server_key') ?: env('FCM_SERVER_KEY');

    //         if (!$serverKey) {
    //             dd('FCM_SERVER_KEY missing in .env');
    //         }

    //         $client = new Client(['timeout' => 5.0]);

    //         $payload = [
    //             'registration_ids' => array_values(array_unique($tokens)),
    //             'priority' => 'high',
    //             'data' => $data,
    //         ];

    //         if ($notification) {
    //             $payload['notification'] = $notification;
    //         }

    //         $response = $client->post('https://fcm.googleapis.com/fcm/send', [
    //             'headers' => [
    //                 'Authorization' => "key={$serverKey}",
    //                 'Content-Type' => 'application/json'
    //             ],
    //             'json' => $payload
    //         ]);

    //         $body = (string) $response->getBody();
    //         $decoded = json_decode($body, true);
    //         return $decoded ?: ['raw' => $body];
    //     } catch (\Exception $e) {
    //         dd('sendFcmData error: ' . $e->getMessage());
    //     }
    // }
}
