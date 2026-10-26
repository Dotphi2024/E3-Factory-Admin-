<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\ParticipantBatch;
use App\Models\ParticipantPayment;
use App\Models\Batch;
use App\Services\RazorpayService;
use App\Http\Resources\ParticipantResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class RazorpayPaymentController extends Controller
{
    protected RazorpayService $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Resolve the target participant from token, query param, or request body.
     */
    protected function resolveParticipant(Request $request): ?Participant
    {
        if (!empty($request->participant)) {
            return $request->participant;
        }

        if ($request->filled('participant_id')) {
            return Participant::with('batch')->find($request->participant_id);
        }

        if ($request->filled('mobile')) {
            return Participant::with('batch')->where('mobile', $request->mobile)->first();
        }

        return null;
    }

    /**
     * Create Razorpay Order for Registration Fee.
     * Accessible with Bearer token OR by passing mobile / participant_id.
     *
     * URL: POST /api/razorpay/create-order
     */
    public function createOrder(Request $request)
    {
        $participant = $this->resolveParticipant($request);

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found. Provide Authorization token, participant_id, or mobile.'
            ], 404);
        }

        $batch = $participant->batch;
        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Associated batch not found for this participant.'
            ], 400);
        }

        // Check if member has already paid
        if ($participant->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration fees are already paid.',
                'data' => [
                    'payment_status' => 'paid',
                    'is_registration_fees_paid' => 1,
                    'logged_in' => true,
                    'redirect_to_payment' => false,
                    'participant' => new ParticipantResource($participant)
                ]
            ], 200);
        }

        // Calculate Registration Amount
        $registrationFee = (float) ($batch->registration_fee ?? 0);
        $advanceSessionFee = $batch->is_one_session_advance_payment ? (float) ($batch->fee_per_session ?? 0) : 0;
        $totalAmount = $registrationFee + $advanceSessionFee;

        if ($totalAmount <= 0) {
            // If fee is 0, auto-mark paid
            $participant->payment_status = 'paid';
            $participant->is_registration_fees_paid = true;
            $participant->save();

            return response()->json([
                'success' => true,
                'message' => 'Registration fee is 0. Account activated directly.',
                'data' => [
                    'payment_status' => 'paid',
                    'is_registration_fees_paid' => 1,
                    'logged_in' => true,
                    'redirect_to_payment' => false,
                    'participant' => new ParticipantResource($participant)
                ]
            ]);
        }

        $receipt = 'rcpt_reg_' . $participant->id . '_' . time();
        $notes = [
            'participant_id' => $participant->id,
            'mobile' => $participant->mobile,
            'batch_id' => $batch->id,
            'purpose' => 'member_registration',
        ];

        $order = $this->razorpayService->createOrder($totalAmount, $receipt, $notes);

        return response()->json([
            'success' => true,
            'message' => 'Razorpay order created successfully. Proceed to payment.',
            'data' => [
                'payment_status' => 'unpaid',
                'is_registration_fees_paid' => 0,
                'logged_in' => false,
                'redirect_to_payment' => true,
                'gateway' => 'razorpay',
                'key_id' => $order['key_id'],
                'order_id' => $order['order_id'],
                'amount' => $order['amount'], // in paise
                'amount_in_rupees' => $order['amount_in_rupees'],
                'currency' => $order['currency'],
                'receipt' => $order['receipt'],
                'name' => config('app.name', 'E3 Factory'),
                'description' => 'Registration Fee - ' . ($batch->name ?? 'Course'),
                'prefill' => [
                    'name' => trim(($participant->first_name ?? '') . ' ' . ($participant->last_name ?? '')),
                    'contact' => (string) $participant->mobile,
                    'email' => (string) ($participant->email ?? ''),
                ],
                'fee_breakdown' => [
                    'registration_fee' => $registrationFee,
                    'advance_session_fee' => $advanceSessionFee,
                    'total_amount' => $totalAmount,
                ],
                'token' => $participant->token,
                'participant' => new ParticipantResource($participant),
            ]
        ]);
    }

    /**
     * Verify Razorpay Payment Signature and Activate Member.
     * Accessible with Bearer token OR by passing participant_id / mobile.
     *
     * URL: POST /api/razorpay/verify-payment
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $participant = $this->resolveParticipant($request);
        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found. Provide Authorization token, participant_id, or mobile.'
            ], 404);
        }

        $batch = $participant->batch;
        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Associated batch not found.'
            ], 400);
        }

        // Verify cryptographic signature
        $isValid = $this->razorpayService->verifyPaymentSignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Payment signature verification failed. Invalid transaction.'
            ], 400);
        }

        // Prevent duplicate processing if payment_id already exists
        $existingPayment = ParticipantPayment::where('transaction_id', $request->razorpay_payment_id)
            ->orWhere('razorpay_payment_id', $request->razorpay_payment_id)
            ->first();

        if ($existingPayment && $participant->isPaid()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment has already been verified and processed.',
                'data' => [
                    'logged_in' => true,
                    'payment_status' => 'paid',
                    'is_registration_fees_paid' => 1,
                    'redirect_to_payment' => false,
                    'token' => $participant->token,
                    'participant' => new ParticipantResource($participant)
                ]
            ]);
        }

        try {
            DB::beginTransaction();

            $regFee = (float) ($batch->registration_fee ?? 0);
            $sessionFee = (float) ($batch->fee_per_session ?? 0);

            // 1. Record Registration Payment
            $regPayment = new ParticipantPayment();
            $regPayment->participant_id = $participant->id;
            $regPayment->batch_id = $participant->batch_id;
            $regPayment->transaction_id = $request->razorpay_payment_id;
            $regPayment->amount = $regFee;
            $regPayment->payment_mode = 'Razorpay';
            $regPayment->payment_for = 'registration';
            $regPayment->razorpay_order_id = $request->razorpay_order_id;
            $regPayment->razorpay_payment_id = $request->razorpay_payment_id;
            $regPayment->razorpay_signature = $request->razorpay_signature;
            $regPayment->save();

            // 2. Advance Session Payment & QR Code if applicable
            if ($batch->is_one_session_advance_payment) {
                $sessionPayment = new ParticipantPayment();
                $sessionPayment->participant_id = $participant->id;
                $sessionPayment->batch_id = $participant->batch_id;
                $sessionPayment->transaction_id = $request->razorpay_payment_id;
                $sessionPayment->amount = $sessionFee;
                $sessionPayment->payment_mode = 'Razorpay';
                $sessionPayment->payment_for = 'session_fee';
                $sessionPayment->session_number = 1;
                $sessionPayment->razorpay_order_id = $request->razorpay_order_id;
                $sessionPayment->razorpay_payment_id = $request->razorpay_payment_id;
                $sessionPayment->razorpay_signature = $request->razorpay_signature;
                $sessionPayment->save();

                $this->generateSessionQrCode($participant, $sessionPayment);
            }

            // 3. Update Participant Financials and Status
            $totalPaidThisTxn = $regFee + ($batch->is_one_session_advance_payment ? $sessionFee : 0);
            $participant->paid_amount = (float) $participant->paid_amount + $totalPaidThisTxn;
            $participant->due_amount = max(0, (float) $participant->due_amount - $totalPaidThisTxn);
            $participant->payment_status = 'paid';
            $participant->is_registration_fees_paid = true;

            // Ensure participant has an auth token for logged-in state
            if (empty($participant->token)) {
                $participant->token = bin2hex(random_bytes(20));
            }
            $participant->save();

            // 4. Update Participant Batch
            $participantBatch = ParticipantBatch::where('participant_id', $participant->id)
                ->where('batch_id', $participant->batch_id)
                ->first();

            if ($participantBatch) {
                $participantBatch->is_registration_fees_paid = true;
                $participantBatch->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully. Registration completed and logged in.',
                'data' => [
                    'logged_in' => true,
                    'payment_status' => 'paid',
                    'is_registration_fees_paid' => 1,
                    'redirect_to_payment' => false,
                    'token' => $participant->token,
                    'participant' => new ParticipantResource($participant)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Razorpay verification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if Participant is Paid or Unpaid.
     * If unpaid, automatically supplies Razorpay order details for seamless payment.
     *
     * URL: GET/POST /api/payment-status
     */
    public function checkPaymentStatus(Request $request)
    {
        $participant = $this->resolveParticipant($request);

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found. Provide Authorization token, participant_id, or mobile.'
            ], 404);
        }

        $batch = $participant->batch;
        $isPaid = $participant->isPaid();

        if ($isPaid) {
            return response()->json([
                'success' => true,
                'message' => 'Participant is paid and authorized.',
                'data' => [
                    'participant_id' => $participant->id,
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'mobile' => $participant->mobile,
                    'batch_id' => $participant->batch_id,
                    'batch_name' => $batch ? $batch->name : null,
                    'payment_status' => 'paid',
                    'is_registration_fees_paid' => 1,
                    'logged_in' => true,
                    'redirect_to_payment' => false,
                    'amount_payable' => 0.00,
                    'token' => $participant->token,
                    'participant' => new ParticipantResource($participant),
                    'payment' => null,
                ]
            ]);
        }

        // Participant is unpaid: generate ready-to-pay Razorpay order
        $registrationFee = (float) ($batch->registration_fee ?? 0);
        $advanceSessionFee = ($batch && $batch->is_one_session_advance_payment) ? (float) ($batch->fee_per_session ?? 0) : 0;
        $totalAmount = $registrationFee + $advanceSessionFee;

        $order = $this->razorpayService->createOrder(
            $totalAmount,
            'rcpt_chk_' . $participant->id . '_' . time(),
            ['participant_id' => $participant->id, 'mobile' => $participant->mobile]
        );

        return response()->json([
            'success' => true,
            'message' => 'Participant is unpaid. Redirect to Razorpay to complete registration payment.',
            'data' => [
                'participant_id' => $participant->id,
                'first_name' => $participant->first_name,
                'last_name' => $participant->last_name,
                'mobile' => $participant->mobile,
                'batch_id' => $participant->batch_id,
                'batch_name' => $batch ? $batch->name : null,
                'payment_status' => 'unpaid',
                'is_registration_fees_paid' => 0,
                'logged_in' => false,
                'redirect_to_payment' => true,
                'amount_payable' => $totalAmount,
                'token' => $participant->token,
                'participant' => new ParticipantResource($participant),
                'payment' => [
                    'gateway' => 'razorpay',
                    'key_id' => $order['key_id'],
                    'order_id' => $order['order_id'],
                    'amount' => $order['amount'],
                    'amount_in_rupees' => $order['amount_in_rupees'],
                    'currency' => $order['currency'],
                    'name' => config('app.name', 'E3 Factory'),
                    'description' => 'Registration Fee - ' . ($batch->name ?? 'Course'),
                    'prefill' => [
                        'name' => trim(($participant->first_name ?? '') . ' ' . ($participant->last_name ?? '')),
                        'contact' => (string) $participant->mobile,
                        'email' => (string) ($participant->email ?? ''),
                    ],
                    'fee_breakdown' => [
                        'registration_fee' => $registrationFee,
                        'advance_session_fee' => $advanceSessionFee,
                        'total_amount' => $totalAmount,
                    ],
                ]
            ]
        ]);
    }

    /**
     * Razorpay Webhook Handler (Server-to-Server asynchronous confirmation).
     *
     * URL: POST /api/razorpay/webhook
     */
    public function webhook(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature');
        $rawPayload = $request->getContent();

        if ($signature && !$this->razorpayService->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('Razorpay webhook signature verification failed');
            return response()->json(['success' => false, 'message' => 'Invalid webhook signature'], 400);
        }

        $event = $request->input('event');
        $payload = $request->input('payload');

        Log::info('Razorpay webhook received: ' . $event, ['event' => $event]);

        if (in_array($event, ['payment.captured', 'order.paid'])) {
            $paymentEntity = $payload['payment']['entity'] ?? null;
            if ($paymentEntity) {
                $notes = $paymentEntity['notes'] ?? [];
                $participantId = $notes['participant_id'] ?? null;
                $paymentId = $paymentEntity['id'] ?? null;
                $orderId = $paymentEntity['order_id'] ?? null;

                if ($participantId && $paymentId) {
                    $participant = Participant::with('batch')->find($participantId);
                    if ($participant && !$participant->isPaid()) {
                        $batch = $participant->batch;
                        $regFee = (float) ($batch->registration_fee ?? 0);
                        $sessionFee = (float) ($batch->fee_per_session ?? 0);

                        $regPayment = new ParticipantPayment();
                        $regPayment->participant_id = $participant->id;
                        $regPayment->batch_id = $participant->batch_id;
                        $regPayment->transaction_id = $paymentId;
                        $regPayment->amount = $regFee;
                        $regPayment->payment_mode = 'Razorpay-Webhook';
                        $regPayment->payment_for = 'registration';
                        $regPayment->razorpay_order_id = $orderId;
                        $regPayment->razorpay_payment_id = $paymentId;
                        $regPayment->save();

                        if ($batch && $batch->is_one_session_advance_payment) {
                            $sessionPayment = new ParticipantPayment();
                            $sessionPayment->participant_id = $participant->id;
                            $sessionPayment->batch_id = $participant->batch_id;
                            $sessionPayment->transaction_id = $paymentId;
                            $sessionPayment->amount = $sessionFee;
                            $sessionPayment->payment_mode = 'Razorpay-Webhook';
                            $sessionPayment->payment_for = 'session_fee';
                            $sessionPayment->session_number = 1;
                            $sessionPayment->razorpay_order_id = $orderId;
                            $sessionPayment->razorpay_payment_id = $paymentId;
                            $sessionPayment->save();

                            $this->generateSessionQrCode($participant, $sessionPayment);
                        }

                        $totalPaid = $regFee + ($batch && $batch->is_one_session_advance_payment ? $sessionFee : 0);
                        $participant->paid_amount = (float) $participant->paid_amount + $totalPaid;
                        $participant->due_amount = max(0, (float) $participant->due_amount - $totalPaid);
                        $participant->payment_status = 'paid';
                        $participant->is_registration_fees_paid = true;
                        $participant->save();

                        ParticipantBatch::where('participant_id', $participant->id)
                            ->where('batch_id', $participant->batch_id)
                            ->update(['is_registration_fees_paid' => true]);
                    }
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Webhook received']);
    }

    /**
     * Helper to generate session QR code.
     */
    protected function generateSessionQrCode(Participant $participant, ParticipantPayment $payment): void
    {
        try {
            $data = [
                'participant_id' => $participant->id,
                'payment_id' => $payment->id,
            ];

            $qr_code_url = route('entry-user.scan-qr-code', $data);
            $fileName = 'qrcode_' . $participant->id . '_' . $payment->id . '.png';
            $directoryPath = public_path('uploads/participant-payment/qrcode/');

            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }

            $qrCodePath = $directoryPath . $fileName;

            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qr_code_url)
                ->size(300)
                ->build();

            $result->saveToFile($qrCodePath);

            $payment->qr_code_file = $fileName;
            $payment->is_qr_used = 0;
            $payment->save();
        } catch (\Exception $e) {
            Log::error('QR code generation failed in Razorpay controller: ' . $e->getMessage());
        }
    }
}
