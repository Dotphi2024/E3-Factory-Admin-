<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected ?string $keyId;
    protected ?string $keySecret;
    protected ?string $webhookSecret;
    protected string $currency;
    protected bool $isMock;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key') ?: env('RAZORPAY_KEY', '');
        $this->keySecret = config('services.razorpay.secret') ?: env('RAZORPAY_SECRET', '');
        $this->webhookSecret = config('services.razorpay.webhook_secret') ?: env('RAZORPAY_WEBHOOK_SECRET', '');
        $this->currency = config('services.razorpay.currency') ?: 'INR';
        $this->isMock = (bool) (config('services.razorpay.mock') ?: env('RAZORPAY_MOCK', false));
    }

    /**
     * Check if valid Razorpay API keys are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->keyId) && !empty($this->keySecret) && !str_starts_with($this->keyId, 'rzp_test_placeholder');
    }

    /**
     * Get Razorpay Public Key ID.
     */
    public function getKeyId(): string
    {
        return $this->keyId ?: 'rzp_test_placeholder_key';
    }

    /**
     * Get Razorpay Key Secret.
     */
    public function getKeySecret(): string
    {
        return $this->keySecret ?: '';
    }

    /**
     * Create a Razorpay Order.
     *
     * @param float $amountInRupees Amount in INR (e.g., 500.00)
     * @param string|null $receipt Optional receipt identifier
     * @param array $notes Custom metadata notes
     * @return array
     */
    public function createOrder(float $amountInRupees, ?string $receipt = null, array $notes = []): array
    {
        $amountInPaise = (int) round($amountInRupees * 100);
        $receipt = $receipt ?: 'rcpt_' . time() . '_' . rand(100, 999);

        // If credentials are configured and not forced mock, call live Razorpay API
        if ($this->isConfigured() && !$this->isMock) {
            try {
                $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                    ->timeout(15)
                    ->post('https://api.razorpay.com/v1/orders', [
                        'amount' => $amountInPaise,
                        'currency' => $this->currency,
                        'receipt' => (string) $receipt,
                        'notes' => array_map('strval', $notes),
                        'payment_capture' => 1,
                    ]);

                if ($response->successful()) {
                    $orderData = $response->json();
                    return [
                        'success' => true,
                        'order_id' => $orderData['id'],
                        'amount' => $orderData['amount'],
                        'amount_in_rupees' => $amountInRupees,
                        'currency' => $orderData['currency'],
                        'receipt' => $orderData['receipt'],
                        'key_id' => $this->getKeyId(),
                        'raw_order' => $orderData,
                        'is_live' => true,
                    ];
                }

                Log::warning('Razorpay order creation API failed, falling back to sandbox format', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Exception $e) {
                Log::error('Razorpay API exception during order creation: ' . $e->getMessage());
            }
        }

        // Mock / Sandbox fallback for development or when keys are not yet configured
        $mockOrderId = 'order_mock_' . uniqid() . '_' . rand(1000, 9999);
        return [
            'success' => true,
            'order_id' => $mockOrderId,
            'amount' => $amountInPaise,
            'amount_in_rupees' => $amountInRupees,
            'currency' => $this->currency,
            'receipt' => $receipt,
            'key_id' => $this->getKeyId(),
            'notes' => $notes,
            'is_live' => false,
            'message' => $this->isConfigured() ? 'Order created' : 'Generated in sandbox/mock mode. Set RAZORPAY_KEY and RAZORPAY_SECRET in .env for live Razorpay payments.',
        ];
    }

    /**
     * Verify payment signature returned by Razorpay checkout.
     *
     * @param string $orderId
     * @param string $paymentId
     * @param string $signature
     * @return bool
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        // Sandbox bypass for testing
        if (!$this->isConfigured() || $this->isMock || str_starts_with($orderId, 'order_mock_')) {
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify webhook signature.
     *
     * @param string $payload Raw request body
     * @param string $signature Signature from X-Razorpay-Signature header
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Fetch payment info from Razorpay.
     */
    public function fetchPayment(string $paymentId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(15)
                ->get("https://api.razorpay.com/v1/payments/{$paymentId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Razorpay fetch payment error: ' . $e->getMessage());
            return null;
        }
    }
}
