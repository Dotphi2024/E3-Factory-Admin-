<?php

namespace App\Services;

use App\Models\CandidateReplacement;
use App\Models\Participant;
use App\Models\ParticipantBatch;
use App\Models\ParticipantPayment;
use App\Models\Batch;
use App\Models\BatchGroupParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class CandidateReplacementService
{
    /**
     * Create a new pending candidate replacement request initiated by the original participant.
     */
    public function createRequest(Participant $originalParticipant, array $data): CandidateReplacement
    {
        // 1. Verify the original participant has an active batch
        $batchId = $data['batch_id'] ?? $originalParticipant->batch_id;
        $batch = Batch::findOrFail($batchId);

        $enrollment = ParticipantBatch::where('participant_id', $originalParticipant->id)
            ->where('batch_id', $batch->id)
            ->where('is_active', 1)
            ->first();

        if (!$enrollment) {
            throw new \Exception('You do not have an active enrollment in this batch.');
        }

        // 2. Check if a pending request already exists for this participant and batch
        $existingPending = CandidateReplacement::where('original_participant_id', $originalParticipant->id)
            ->where('batch_id', $batch->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            throw new \Exception('A replacement request is already pending review for this batch.');
        }

        // 3. Prevent replacing with oneself
        $candidateMobile = trim($data['candidate_mobile']);
        if ($candidateMobile === $originalParticipant->mobile) {
            throw new \Exception('Replacement candidate mobile cannot be the same as your mobile number.');
        }

        // 4. Calculate existing financial entitlement to be transferred
        $payments = ParticipantPayment::where('participant_id', $originalParticipant->id)
            ->where('batch_id', $batch->id)
            ->get();

        $totalTransferred = 0.00;
        $regFeeTransferred = 0.00;
        $sessionFeesTransferred = 0.00;
        $paidSessions = [];
        $paymentIds = [];

        foreach ($payments as $payment) {
            $totalTransferred += (float)$payment->amount;
            $paymentIds[] = $payment->id;
            if ($payment->payment_for === 'registration' || $payment->payment_for === 'registration_fee') {
                $regFeeTransferred += (float)$payment->amount;
            } elseif ($payment->payment_for === 'session_fee' || $payment->payment_for === 'session') {
                $sessionFeesTransferred += (float)$payment->amount;
                if ($payment->session_number) {
                    $paidSessions[] = (int)$payment->session_number;
                }
            }
        }

        // 5. Create the pending candidate replacement request record
        $replacement = new CandidateReplacement();
        $replacement->batch_id = $batch->id;
        $replacement->original_participant_id = $originalParticipant->id;
        $replacement->original_participant_batch_id = $enrollment->id;
        $replacement->candidate_first_name = trim($data['candidate_first_name']);
        $replacement->candidate_last_name = trim($data['candidate_last_name']);
        $replacement->candidate_mobile = $candidateMobile;
        $replacement->candidate_email = !empty($data['candidate_email']) ? trim($data['candidate_email']) : null;
        $replacement->candidate_data = [
            'address' => $data['candidate_address'] ?? null,
            'city' => $data['candidate_city'] ?? null,
            'state' => $data['candidate_state'] ?? null,
            'country' => $data['candidate_country'] ?? null,
            'birth_date' => $data['candidate_birth_date'] ?? null,
            'reference' => 'Candidate Replacement',
            'reference_detail' => 'Replaced ' . $originalParticipant->first_name . ' ' . $originalParticipant->last_name,
        ];
        $replacement->total_amount_transferred = $totalTransferred;
        $replacement->registration_fee_transferred = $regFeeTransferred;
        $replacement->session_fees_transferred = $sessionFeesTransferred;
        $replacement->paid_sessions_transferred = array_values(array_unique($paidSessions));
        $replacement->transferred_payment_ids = $paymentIds;
        $replacement->reason = $data['reason'];
        $replacement->policy_accepted = true;
        $replacement->status = 'pending';
        $replacement->save();

        return $replacement;
    }

    /**
     * Master Admin approves the candidate replacement request atomically.
     */
    public function approveReplacement(CandidateReplacement $replacement, User $adminUser, ?string $notes = null): bool
    {
        if ($replacement->status !== 'pending') {
            throw new \Exception('This request has already been processed (Current status: ' . $replacement->status . ').');
        }

        return DB::transaction(function () use ($replacement, $adminUser, $notes) {
            $originalParticipant = Participant::findOrFail($replacement->original_participant_id);
            $batch = Batch::findOrFail($replacement->batch_id);
            $originalEnrollment = ParticipantBatch::findOrFail($replacement->original_participant_batch_id);

            // 1. Resolve or Create the Replacement Candidate (Permanent Identity Principle)
            $candidateMobile = $replacement->candidate_mobile;
            $replacementParticipant = Participant::where('mobile', $candidateMobile)->first();

            if ($replacementParticipant) {
                // Check if candidate is already actively enrolled in this batch
                $activeInBatch = ParticipantBatch::where('participant_id', $replacementParticipant->id)
                    ->where('batch_id', $batch->id)
                    ->where('is_active', 1)
                    ->exists();

                if ($activeInBatch) {
                    throw new \Exception('Candidate with mobile ' . $candidateMobile . ' is already actively enrolled in this batch.');
                }

                // Update basic active status and current batch
                $replacementParticipant->status = 'active';
                $replacementParticipant->is_active = 1;
                $replacementParticipant->batch_id = $batch->id;
                $replacementParticipant->save();
            } else {
                // Create a permanent record for the replacement candidate
                $candidateData = $replacement->candidate_data ?? [];
                $replacementParticipant = new Participant();
                $replacementParticipant->first_name = $replacement->candidate_first_name;
                $replacementParticipant->last_name = $replacement->candidate_last_name;
                $replacementParticipant->mobile = $candidateMobile;
                $replacementParticipant->email = $replacement->candidate_email;
                $replacementParticipant->address = $candidateData['address'] ?? null;
                $replacementParticipant->city = $candidateData['city'] ?? null;
                $replacementParticipant->state = $candidateData['state'] ?? null;
                $replacementParticipant->country = $candidateData['country'] ?? null;
                $replacementParticipant->birth_date = !empty($candidateData['birth_date']) ? date('Y-m-d', strtotime($candidateData['birth_date'])) : null;
                $replacementParticipant->reference = $candidateData['reference'] ?? 'Candidate Replacement';
                $replacementParticipant->reference_detail = $candidateData['reference_detail'] ?? ('Replaced ' . $originalParticipant->first_name . ' ' . $originalParticipant->last_name);
                $replacementParticipant->type = 'student';
                $replacementParticipant->registration_type = 'replacement';
                $replacementParticipant->added_by = $adminUser->id;
                $replacementParticipant->status = 'active';
                $replacementParticipant->is_active = 1;
                $replacementParticipant->batch_id = $batch->id;
                $replacementParticipant->save();
            }

            // 2. Transfer Enrollment Record (ParticipantBatch)
            // Mark original participant's enrollment as replaced
            $originalEnrollment->enrollment_status = 'replaced';
            $originalEnrollment->is_active = 0;
            $originalEnrollment->replaced_by_participant_id = $replacementParticipant->id;
            $originalEnrollment->replacement_id = $replacement->id;
            $originalEnrollment->save();

            // Create active enrollment for replacement candidate
            $newEnrollment = ParticipantBatch::where('participant_id', $replacementParticipant->id)
                ->where('batch_id', $batch->id)
                ->first();

            if (!$newEnrollment) {
                $newEnrollment = new ParticipantBatch();
                $newEnrollment->participant_id = $replacementParticipant->id;
                $newEnrollment->batch_id = $batch->id;
            }

            $newEnrollment->is_registration_fees_paid = $originalEnrollment->is_registration_fees_paid;
            $newEnrollment->enrollment_status = 'active';
            $newEnrollment->is_active = 1;
            $newEnrollment->replacement_id = $replacement->id;
            $newEnrollment->save();

            // 3. Transfer Payment Records & Regenerate Unused QR Passes
            // Original payer is permanently preserved as original_payer_id
            // Current beneficiary is set to replacementParticipant->id
            $payments = ParticipantPayment::where('participant_id', $originalParticipant->id)
                ->where('batch_id', $batch->id)
                ->get();

            $qrDirectory = public_path('uploads/participant-payment/qrcode/');
            if (!file_exists($qrDirectory)) {
                mkdir($qrDirectory, 0755, true);
            }

            foreach ($payments as $payment) {
                // Ensure original_payer_id is locked to original payer (Rahul)
                if (empty($payment->original_payer_id)) {
                    $payment->original_payer_id = $originalParticipant->id;
                }

                // Assign beneficiary to replacement candidate (Amit)
                $payment->participant_id = $replacementParticipant->id;
                $payment->is_transferred = 1;
                $payment->replacement_id = $replacement->id;

                // If QR code hasn't been used yet, regenerate for the replacement candidate
                if (!$payment->is_qr_used && $payment->payment_for === 'session_fee') {
                    $qrFileName = 'qrcode_' . $replacementParticipant->id . '_' . $payment->id . '.png';
                    $qrPath = $qrDirectory . $qrFileName;

                    $qrData = [
                        'participant_id' => $replacementParticipant->id,
                        'payment_id' => $payment->id,
                    ];
                    $qrUrl = route('entry-user.scan-qr-code', $qrData);

                    $qrResult = Builder::create()
                        ->writer(new PngWriter())
                        ->data($qrUrl)
                        ->size(300)
                        ->build();

                    $qrResult->saveToFile($qrPath);
                    $payment->qr_code_file = $qrFileName;
                }

                $payment->save();
            }

            // 4. Coach Group Transfer (batch_group_participants)
            $groupMemberships = BatchGroupParticipant::where('participant_id', $originalParticipant->id)
                ->where('batch_id', $batch->id)
                ->where('is_active', 1)
                ->get();

            foreach ($groupMemberships as $membership) {
                // Deactivate original participant in coach group
                $membership->is_active = 0;
                $membership->save();

                // Add replacement candidate to the same coach group
                $existingNewMember = BatchGroupParticipant::where('participant_id', $replacementParticipant->id)
                    ->where('batch_group_id', $membership->batch_group_id)
                    ->first();

                if (!$existingNewMember) {
                    $newMember = new BatchGroupParticipant();
                    $newMember->batch_id = $batch->id;
                    $newMember->batch_group_id = $membership->batch_group_id;
                    $newMember->participant_id = $replacementParticipant->id;
                    $newMember->is_active = 1;
                    $newMember->added_by = $adminUser->id;
                    $newMember->save();
                } else {
                    $existingNewMember->is_active = 1;
                    $existingNewMember->save();
                }
            }

            // 5. Update Financial Balances on Participant Models
            $batchTotalFee = (float)$batch->registration_fee + ((int)$batch->number_of_sessions * (float)$batch->fee_per_session);
            $transferredAmount = (float)$replacement->total_amount_transferred;

            // Update Replacement Candidate Balances
            $replacementParticipant->total_amount = $batchTotalFee;
            $replacementParticipant->paid_amount = $transferredAmount;
            $replacementParticipant->due_amount = max(0, $batchTotalFee - $transferredAmount);
            $replacementParticipant->is_registration_fees_paid = $originalEnrollment->is_registration_fees_paid;
            $replacementParticipant->save();

            // Update Original Participant Balances (clear dues for this replaced batch)
            $otherActiveBatches = ParticipantBatch::where('participant_id', $originalParticipant->id)
                ->where('is_active', 1)
                ->count();

            if ($otherActiveBatches === 0) {
                $originalParticipant->is_active = 0;
                $originalParticipant->status = 'replaced';
                $originalParticipant->due_amount = 0;
            }
            $originalParticipant->save();

            // 6. Update Candidate Replacement Record
            $replacement->replacement_participant_id = $replacementParticipant->id;
            $replacement->new_participant_batch_id = $newEnrollment->id;
            $replacement->status = 'approved';
            $replacement->approved_by = $adminUser->id;
            $replacement->approved_at = now();
            $replacement->notes = $notes;
            $replacement->save();

            Log::info("Candidate Replacement Approved: REP-{$replacement->id} (Original: {$originalParticipant->first_name} {$originalParticipant->last_name} -> Replacement: {$replacementParticipant->first_name} {$replacementParticipant->last_name}) in Batch: {$batch->name}");

            return true;
        });
    }

    /**
     * Master Admin rejects the candidate replacement request.
     */
    public function rejectReplacement(CandidateReplacement $replacement, User $adminUser, string $rejectionReason, ?string $notes = null): bool
    {
        if ($replacement->status !== 'pending') {
            throw new \Exception('This request has already been processed (Current status: ' . $replacement->status . ').');
        }

        $replacement->status = 'rejected';
        $replacement->rejection_reason = $rejectionReason;
        $replacement->approved_by = $adminUser->id;
        $replacement->approved_at = now();
        $replacement->notes = $notes;
        $replacement->save();

        Log::info("Candidate Replacement Rejected: REP-{$replacement->id} (Reason: {$rejectionReason})");

        return true;
    }
}
