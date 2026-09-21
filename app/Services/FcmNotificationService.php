<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FcmNotificationService{
    protected $projectId;
    protected $credentialsPath;

    public function __construct()
    {
        // Load Firebase Project ID
        $this->credentialsPath = storage_path('app/firebase/firebase_credentials.json');
        if (file_exists($this->credentialsPath)) {
            $credentials = json_decode(file_get_contents($this->credentialsPath), true);
            $this->projectId = $credentials['project_id'] ?? null;
        } else {
            $this->projectId = null;
        }
    }

    /**
     * Generate Firebase Access Token manually using cURL
     */
    public function getAccessToken()
    {
        if (!file_exists($this->credentialsPath)) {
            return null;
        }
        $credentials = json_decode(file_get_contents($this->credentialsPath), true);

        $tokenUrl = 'https://oauth2.googleapis.com/token';

        $data = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $this->generateJWT($credentials),
        ];

        $response = Http::asForm()->post($tokenUrl, $data);
        $json = $response->json();

        return $json['access_token'] ?? null;
    }

    /**
     * Generate JWT for Firebase Authentication
     */
    private function generateJWT($credentials)
    {
        $header = base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $now = time();
        $payload = base64_encode(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600, // Token valid for 1 hour
        ]));

        $signatureInput = "$header.$payload";
        openssl_sign($signatureInput, $signature, $credentials['private_key'], 'sha256WithRSAEncryption');

        return "$signatureInput." . base64_encode($signature);
    }

    /**
     * Send Push Notification to Multiple Devices
     */
    public function sendNotification(array $deviceTokens, string $title, string $body,string $type,$details)
    {
        // print_r($deviceTokens);exit;
        if (empty($deviceTokens)) {
            return;
        }

        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];
        $detailsArray = is_array($details) ? $details : $details->toArray();
        $formattedDetails = [];

        foreach ($detailsArray as $key => $value) {
            $formattedDetails[$key] = (string) $value; // Convert all values to strings
        }

        foreach ($deviceTokens as $token) {
            $data = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_merge([
                        'type' => $type,
                    ], $formattedDetails),
                ],
            ];

            try {
                $response = Http::withHeaders($headers)->post($url, $data);
                if ($response->failed()) {
                    \Log::error('Firebase Notification Error:', [
                        'token' => $token,
                        'response' => $response->json(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Firebase Notification Exception:', [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
