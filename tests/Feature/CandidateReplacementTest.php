<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Participant;
use App\Models\ParticipantBatch;
use App\Models\ParticipantPayment;
use App\Models\Batch;
use App\Models\Course;
use App\Models\User;
use App\Models\CandidateReplacement;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\BatchSchedule;
use App\Services\CandidateReplacementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CandidateReplacementTest extends TestCase
{
    public function test_complete_candidate_replacement_lifecycle()
    {
        DB::beginTransaction();

        try {
            // 1. Setup Master Admin User
            $admin = User::first();
            if (!$admin) {
                $admin = new User();
                $admin->name = 'Admin Tester';
                $admin->email = 'admin_test_' . time() . '@example.com';
                $admin->password = bcrypt('password123');
                $admin->user_type = 'master';
                $admin->save();
            }

            // 2. Setup Course and Batch
            $course = new Course();
            $course->name = 'E3 Leadership Program';
            $course->duration = '3 Months';
            $course->added_by = $admin->id;
            $course->save();

            $batch = new Batch();
            $batch->name = 'E3 Batch Test ' . time();
            $batch->course_id = $course->id;
            $batch->number_of_sessions = 3;
            $batch->registration_fee = '2000';
            $batch->fee_per_session = '4000';
            $batch->is_one_session_advance_payment = 0;
            $batch->status = '';
            $batch->start_date = '2026-10-01';
            $batch->end_date = '2026-12-01';
            $batch->added_by = $admin->id;
            $batch->save();

            $schedule1 = new BatchSchedule();
            $schedule1->batch_id = $batch->id;
            $schedule1->session_number = 1;
            $schedule1->name = 'Session 1';
            $schedule1->amount = '4000';
            $schedule1->is_session_completed = 1;
            $schedule1->save();

            $schedule2 = new BatchSchedule();
            $schedule2->batch_id = $batch->id;
            $schedule2->session_number = 2;
            $schedule2->name = 'Session 2';
            $schedule2->amount = '4000';
            $schedule2->is_session_completed = 0;
            $schedule2->save();

            // 3. Setup Original Participant: Rahul Patil
            $rahulMobile = '98' . rand(10000000, 99999999);
            $rahul = new Participant();
            $rahul->first_name = 'Rahul';
            $rahul->last_name = 'Patil';
            $rahul->mobile = $rahulMobile;
            $rahul->email = 'rahul_' . time() . '@example.com';
            $rahul->batch_id = $batch->id;
            $rahul->type = 'student';
            $rahul->total_amount = 14000;
            $rahul->paid_amount = 6000;
            $rahul->due_amount = 8000;
            $rahul->is_registration_fees_paid = 1;
            $rahul->is_active = 1;
            $rahul->status = 'active';
            $rahul->token = Str::random(32);
            $rahul->save();

            $rahulEnrollment = new ParticipantBatch();
            $rahulEnrollment->participant_id = $rahul->id;
            $rahulEnrollment->batch_id = $batch->id;
            $rahulEnrollment->is_registration_fees_paid = 1;
            $rahulEnrollment->is_active = 1;
            $rahulEnrollment->enrollment_status = 'active';
            $rahulEnrollment->save();

            // Payments made by Rahul
            $regPayment = new ParticipantPayment();
            $regPayment->participant_id = $rahul->id;
            $regPayment->original_payer_id = $rahul->id;
            $regPayment->batch_id = $batch->id;
            $regPayment->transaction_id = 'TXN_REG_' . time();
            $regPayment->amount = '2000.00';
            $regPayment->payment_mode = 'online';
            $regPayment->payment_for = 'registration_fee';
            $regPayment->save();

            $session1Payment = new ParticipantPayment();
            $session1Payment->participant_id = $rahul->id;
            $session1Payment->original_payer_id = $rahul->id;
            $session1Payment->batch_id = $batch->id;
            $session1Payment->transaction_id = 'TXN_SES1_' . time();
            $session1Payment->amount = '4000.00';
            $session1Payment->payment_mode = 'online';
            $session1Payment->payment_for = 'session_fee';
            $session1Payment->session_number = 1;
            $session1Payment->is_qr_used = 1;
            $session1Payment->save();

            $initialPaymentCount = ParticipantPayment::count();

            // Coach group membership
            $group = new \App\Models\BatchGroup();
            $group->batch_id = $batch->id;
            $group->name = 'Group A';
            $group->coach_id = $admin->id;
            $group->added_by = $admin->id;
            $group->save();

            $membership = new \App\Models\BatchGroupParticipant();
            $membership->batch_id = $batch->id;
            $membership->batch_group_id = $group->id;
            $membership->participant_id = $rahul->id;
            $membership->is_active = 1;
            $membership->added_by = $admin->id;
            $membership->save();

            // Historical activity by Rahul (Attendance in Session 1)
            $meeting = new Meeting();
            $meeting->coach_id = $admin->id;
            $meeting->batch_id = $batch->id;
            $meeting->batch_group_id = $group->id;
            $meeting->batch_schedule_id = $schedule1->id;
            $meeting->title = 'Session 1 Offline Meeting';
            $meeting->date = '2026-10-01';
            $meeting->time = '10:00:00';
            $meeting->status = 'completed';
            $meeting->save();

            $attendance = new MeetingAttendance();
            $attendance->meeting_id = $meeting->id;
            $attendance->participant_id = $rahul->id;
            $attendance->status = 'present';
            $attendance->save();

            // ==========================================
            // STEP 1: Participant Requests Replacement
            // ==========================================
            $service = app(CandidateReplacementService::class);
            $amitMobile = '97' . rand(10000000, 99999999);

            $replacementRequest = $service->createRequest($rahul, [
                'batch_id' => $batch->id,
                'candidate_first_name' => 'Amit',
                'candidate_last_name' => 'Sharma',
                'candidate_mobile' => $amitMobile,
                'candidate_email' => 'amit_' . time() . '@example.com',
                'candidate_city' => 'Pune',
                'reason' => 'Relocating to another city for work',
            ]);

            $this->assertEquals('pending', $replacementRequest->status);
            $this->assertEquals(6000.00, (float)$replacementRequest->total_amount_transferred);
            $this->assertEquals(2000.00, (float)$replacementRequest->registration_fee_transferred);
            $this->assertEquals(4000.00, (float)$replacementRequest->session_fees_transferred);
            $this->assertContains(1, $replacementRequest->paid_sessions_transferred);

            // While pending: Rahul is still active
            $this->assertEquals(1, $rahul->fresh()->is_active);
            $this->assertEquals('active', $rahulEnrollment->fresh()->enrollment_status);

            // ==========================================
            // STEP 2: Master Admin Approves Replacement
            // ==========================================
            $service->approveReplacement($replacementRequest, $admin, 'Approved by Admin Tester');

            $replacementRequest->refresh();
            $this->assertEquals('approved', $replacementRequest->status);
            $this->assertNotNull($replacementRequest->replacement_participant_id);

            // 1. Verify Replacement Candidate: Amit Sharma
            $amit = Participant::findOrFail($replacementRequest->replacement_participant_id);
            $this->assertEquals('Amit', $amit->first_name);
            $this->assertEquals('Sharma', $amit->last_name);
            $this->assertEquals($amitMobile, $amit->mobile);
            $this->assertEquals(1, $amit->is_active);
            $this->assertEquals('active', $amit->status);
            $this->assertEquals(14000.00, (float)$amit->total_amount);
            $this->assertEquals(6000.00, (float)$amit->paid_amount); // Inherited 6000 paid
            $this->assertEquals(8000.00, (float)$amit->due_amount);  // Inherited 8000 due

            // 2. Verify Original Participant: Rahul
            $this->assertEquals('replaced', $rahul->fresh()->status);
            $this->assertEquals('replaced', $rahulEnrollment->fresh()->enrollment_status);
            $this->assertEquals(0, $rahulEnrollment->fresh()->is_active);
            $this->assertEquals($amit->id, $rahulEnrollment->fresh()->replaced_by_participant_id);

            // 3. Verify Amit's Active Enrollment
            $amitEnrollment = ParticipantBatch::where('participant_id', $amit->id)->where('batch_id', $batch->id)->first();
            $this->assertNotNull($amitEnrollment);
            $this->assertEquals('active', $amitEnrollment->enrollment_status);
            $this->assertEquals(1, $amitEnrollment->is_active);
            $this->assertEquals(1, $amitEnrollment->is_registration_fees_paid);

            // 4. Verify Payment Integrity: ZERO duplicate payments, ZERO refunds
            $this->assertEquals($initialPaymentCount, ParticipantPayment::count(), 'Payment count must not change (zero duplicate payments)');

            $updatedRegPayment = ParticipantPayment::findOrFail($regPayment->id);
            $this->assertEquals($amit->id, $updatedRegPayment->participant_id, 'Current beneficiary must be Amit');
            $this->assertEquals($rahul->id, $updatedRegPayment->original_payer_id, 'Original payer must permanently remain Rahul');
            $this->assertEquals(1, $updatedRegPayment->is_transferred);
            $this->assertEquals($replacementRequest->id, $updatedRegPayment->replacement_id);

            $updatedSession1Payment = ParticipantPayment::findOrFail($session1Payment->id);
            $this->assertEquals($amit->id, $updatedSession1Payment->participant_id);
            $this->assertEquals($rahul->id, $updatedSession1Payment->original_payer_id);
            $this->assertEquals(1, $updatedSession1Payment->is_qr_used, 'Historical session 1 used status must remain 1');

            // 5. Verify Historical Records Isolation: Rahul's attendance stays with Rahul
            $this->assertEquals($rahul->id, $attendance->fresh()->participant_id);
            $amitAttendanceCount = MeetingAttendance::where('participant_id', $amit->id)->count();
            $this->assertEquals(0, $amitAttendanceCount, 'Amit must have 0 historical meeting attendances');

            // 6. Verify Coach Group Membership Transfer
            $this->assertEquals(0, $membership->fresh()->is_active, 'Rahul should be inactive in coach group');
            $amitGroupMembership = \App\Models\BatchGroupParticipant::where('participant_id', $amit->id)->where('batch_group_id', $group->id)->first();
            $this->assertNotNull($amitGroupMembership, 'Amit should be added to coach group');
            $this->assertEquals(1, $amitGroupMembership->is_active);

            // ==========================================
            // STEP 3: Rejoin / Reconnect Flow Test
            // ==========================================
            $newBatch = new Batch();
            $newBatch->name = 'E3 Batch Future ' . time();
            $newBatch->course_id = $course->id;
            $newBatch->number_of_sessions = 2;
            $newBatch->registration_fee = '2000';
            $newBatch->fee_per_session = '4000';
            $newBatch->is_one_session_advance_payment = 0;
            $newBatch->status = '';
            $newBatch->start_date = '2027-01-01';
            $newBatch->end_date = '2027-03-01';
            $newBatch->added_by = $admin->id;
            $newBatch->save();

            // Reusing existing Rahul permanent record
            $existingRahul = Participant::where('mobile', $rahulMobile)->first();
            $this->assertNotNull($existingRahul);
            $this->assertEquals($rahul->id, $existingRahul->id);

            $rejoinEnrollment = new ParticipantBatch();
            $rejoinEnrollment->participant_id = $existingRahul->id;
            $rejoinEnrollment->batch_id = $newBatch->id;
            $rejoinEnrollment->is_registration_fees_paid = 0;
            $rejoinEnrollment->is_active = 1;
            $rejoinEnrollment->enrollment_status = 'active';
            $rejoinEnrollment->save();

            $existingRahul->status = 'active';
            $existingRahul->is_active = 1;
            $existingRahul->save();

            $this->assertEquals(2, $existingRahul->participantBatches()->count(), 'Rahul has 2 batch enrollments (1 replaced, 1 active)');
            $this->assertEquals(1, Participant::where('mobile', $rahulMobile)->count(), 'Rahul profile is never duplicated');

            // ==========================================
            // STEP 4: Rejection Flow Test
            // ==========================================
            $johnMobile = '96' . rand(10000000, 99999999);
            $john = new Participant();
            $john->first_name = 'John';
            $john->last_name = 'Doe';
            $john->mobile = $johnMobile;
            $john->batch_id = $batch->id;
            $john->type = 'student';
            $john->is_active = 1;
            $john->status = 'active';
            $john->token = Str::random(32);
            $john->save();

            $johnEnrollment = new ParticipantBatch();
            $johnEnrollment->participant_id = $john->id;
            $johnEnrollment->batch_id = $batch->id;
            $johnEnrollment->is_registration_fees_paid = 0;
            $johnEnrollment->is_active = 1;
            $johnEnrollment->enrollment_status = 'active';
            $johnEnrollment->save();

            $rejectedRequest = $service->createRequest($john, [
                'batch_id' => $batch->id,
                'candidate_first_name' => 'Karan',
                'candidate_last_name' => 'Verma',
                'candidate_mobile' => '95' . rand(10000000, 99999999),
                'reason' => 'Busy schedule',
            ]);

            $service->rejectReplacement($rejectedRequest, $admin, 'Incomplete candidate documentation');

            $this->assertEquals('rejected', $rejectedRequest->fresh()->status);
            $this->assertEquals('Incomplete candidate documentation', $rejectedRequest->fresh()->rejection_reason);
            $this->assertEquals(1, $john->fresh()->is_active, 'John remains active after rejection');
            $this->assertEquals('active', $johnEnrollment->fresh()->enrollment_status);
        } finally {
            DB::rollBack();
        }
    }

    public function test_participant_api_and_business_rules()
    {
        DB::beginTransaction();

        try {
            $admin = User::first() ?? User::factory()->create();

            $course = new Course();
            $course->name = 'API Test Course ' . time();
            $course->duration = '2 Months';
            $course->added_by = $admin->id;
            $course->save();

            $batch = new Batch();
            $batch->name = 'API Batch ' . time();
            $batch->course_id = $course->id;
            $batch->number_of_sessions = 2;
            $batch->registration_fee = '1000';
            $batch->fee_per_session = '2000';
            $batch->status = '';
            $batch->added_by = $admin->id;
            $batch->save();

            $mobile = '91' . rand(10000000, 99999999);
            $token = Str::random(32);
            $participant = new Participant();
            $participant->first_name = 'Vikram';
            $participant->last_name = 'Singh';
            $participant->mobile = $mobile;
            $participant->batch_id = $batch->id;
            $participant->token = $token;
            $participant->type = 'student';
            $participant->is_active = 1;
            $participant->status = 'active';
            $participant->save();

            $enrollment = new ParticipantBatch();
            $enrollment->participant_id = $participant->id;
            $enrollment->batch_id = $batch->id;
            $enrollment->is_registration_fees_paid = 1;
            $enrollment->is_active = 1;
            $enrollment->enrollment_status = 'active';
            $enrollment->save();

            // 1. Test Self Replacement Prevention (Business Rule)
            $responseSelf = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/request-candidate-replacement', [
                'batch_id' => $batch->id,
                'candidate_first_name' => 'Vikram Jr',
                'candidate_last_name' => 'Singh',
                'candidate_mobile' => $mobile, // Same mobile
                'reason' => 'Testing self replacement rejection',
                'policy_accepted' => true,
            ]);

            $responseSelf->assertStatus(400)
                ->assertJson([
                    'success' => false,
                ]);

            // 2. Test Successful Candidate Replacement Request via API
            $candidateMobile = '92' . rand(10000000, 99999999);
            $responseValid = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/request-candidate-replacement', [
                'batch_id' => $batch->id,
                'candidate_first_name' => 'Rohan',
                'candidate_last_name' => 'Deshmukh',
                'candidate_mobile' => $candidateMobile,
                'candidate_email' => 'rohan@example.com',
                'candidate_city' => 'Mumbai',
                'reason' => 'Moving abroad for higher studies',
                'policy_accepted' => true,
            ]);

            $responseValid->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'status' => 'pending',
                        'candidate_mobile' => $candidateMobile,
                    ]
                ]);

            $replacementId = $responseValid->json('data.replacement_id');

            // 3. Test Duplicate Request Prevention (Business Rule)
            $responseDuplicate = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/request-candidate-replacement', [
                'batch_id' => $batch->id,
                'candidate_first_name' => 'Another',
                'candidate_last_name' => 'Candidate',
                'candidate_mobile' => '93' . rand(10000000, 99999999),
                'reason' => 'Attempting duplicate pending request',
                'policy_accepted' => true,
            ]);

            $responseDuplicate->assertStatus(400);

            // 4. Test Get Status API
            $responseStatus = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/get-candidate-replacement-status?batch_id=' . $batch->id);

            $responseStatus->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'has_request' => true,
                    'data' => [
                        'id' => $replacementId,
                        'status' => 'pending',
                        'candidate_mobile' => $candidateMobile,
                    ]
                ]);

            // 5. Test Master Admin Web Routes
            $adminUser = User::where('user_type', 'master')->first() ?? $admin;

            // Admin List View
            $adminListResponse = $this->actingAs($adminUser)
                ->get(route('master.participants.candidate-replacements'));
            $adminListResponse->assertStatus(200);

            // Admin Approve Request
            $adminApproveResponse = $this->actingAs($adminUser)
                ->post(route('master.participants.approve-candidate-replacement', $replacementId), [
                    'notes' => 'Approved via automated test',
                ]);
            $adminApproveResponse->assertRedirect(route('master.participants.candidate-replacements', ['status' => 'approved']));

            // Verify status after admin approval
            $responseStatusAfterApproval = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/get-candidate-replacement-status?batch_id=' . $batch->id);

            $responseStatusAfterApproval->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $replacementId,
                        'status' => 'approved',
                    ]
                ]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_rejection_http_route_and_edge_cases()
    {
        DB::beginTransaction();

        try {
            $admin = User::first() ?? User::factory()->create();

            $course = new Course();
            $course->name = 'Rejection Course ' . time();
            $course->duration = '1 Month';
            $course->added_by = $admin->id;
            $course->save();

            $batch = new Batch();
            $batch->name = 'Rejection Batch ' . time();
            $batch->course_id = $course->id;
            $batch->number_of_sessions = 1;
            $batch->registration_fee = '500';
            $batch->fee_per_session = '1000';
            $batch->status = '';
            $batch->added_by = $admin->id;
            $batch->save();

            $mobile = '94' . rand(10000000, 99999999);
            $token = Str::random(32);
            $participant = new Participant();
            $participant->first_name = 'Anil';
            $participant->last_name = 'Kapoor';
            $participant->mobile = $mobile;
            $participant->batch_id = $batch->id;
            $participant->token = $token;
            $participant->type = 'student';
            $participant->is_active = 1;
            $participant->status = 'active';
            $participant->save();

            $enrollment = new ParticipantBatch();
            $enrollment->participant_id = $participant->id;
            $enrollment->batch_id = $batch->id;
            $enrollment->is_registration_fees_paid = 1;
            $enrollment->is_active = 1;
            $enrollment->enrollment_status = 'active';
            $enrollment->save();

            $candidateMobile = '99' . rand(10000000, 99999999);
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/request-candidate-replacement', [
                'batch_id' => $batch->id,
                'candidate_first_name' => 'Sanjay',
                'candidate_last_name' => 'Dutt',
                'candidate_mobile' => $candidateMobile,
                'reason' => 'Schedule conflict',
                'policy_accepted' => true,
            ]);

            $response->assertStatus(201);
            $repId = $response->json('data.replacement_id');

            // Admin Reject Request via Web Route
            $adminUser = User::where('user_type', 'master')->first() ?? $admin;

            $rejectResponse = $this->actingAs($adminUser)
                ->post(route('master.participants.reject-candidate-replacement', $repId), [
                    'rejection_reason' => 'Eligibility criteria not met for replacement candidate',
                    'notes' => 'Admin rejected due to policy',
                ]);

            $rejectResponse->assertRedirect(route('master.participants.candidate-replacements', ['status' => 'rejected']));

            $rep = CandidateReplacement::findOrFail($repId);
            $this->assertEquals('rejected', $rep->status);
            $this->assertEquals('Eligibility criteria not met for replacement candidate', $rep->rejection_reason);
            $this->assertEquals('Admin rejected due to policy', $rep->notes);
            $this->assertEquals(1, $participant->fresh()->is_active);
            $this->assertEquals('active', $enrollment->fresh()->enrollment_status);
        } finally {
            DB::rollBack();
        }
    }
}
