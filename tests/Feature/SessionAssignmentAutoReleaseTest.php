<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Batch;
use App\Models\BatchSchedule;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Services\AssignmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SessionAssignmentAutoReleaseTest extends TestCase
{
    protected $batch;
    protected $course;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $this->admin = User::first();
        if (!$this->admin) {
            $this->admin = new User();
            $this->admin->name = 'Admin Tester';
            $this->admin->email = 'admin_auto_' . time() . '@example.com';
            $this->admin->password = bcrypt('password123');
            $this->admin->user_type = 'master';
            $this->admin->save();
        }

        $this->course = new Course();
        $this->course->name = 'Test Program Auto Release ' . time();
        $this->course->duration = '2 Months';
        $this->course->added_by = $this->admin->id;
        $this->course->save();

        $this->batch = new Batch();
        $this->batch->course_id = $this->course->id;
        $this->batch->name = 'Auto Release Test Batch ' . time();
        $this->batch->number_of_sessions = 5;
        $this->batch->registration_fee = '1000';
        $this->batch->fee_per_session = '500';
        $this->batch->start_date = '2026-10-01';
        $this->batch->end_date = '2026-12-01';
        $this->batch->is_active = 1;
        $this->batch->status = 'upcoming';
        $this->batch->added_by = $this->admin->id;
        $this->batch->save();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_future_session_assignments_are_not_auto_released(): void
    {
        $futureDate = Carbon::tomorrow()->format('Y-m-d');
        $schedule = new BatchSchedule();
        $schedule->batch_id = $this->batch->id;
        $schedule->name = 'Future Session 1';
        $schedule->date = $futureDate;
        $schedule->amount = '500';
        $schedule->session_number = 1;
        $schedule->is_session_completed = 0;
        $schedule->save();

        $assignment = new Assignment();
        $assignment->batch_id = $this->batch->id;
        $assignment->batch_schedule_id = $schedule->id;
        $assignment->question = 'Future Assignment Question';
        $assignment->option_type = 'manual';
        $assignment->is_started = 0;
        $assignment->added_by = 0;
        $assignment->save();

        $this->assertFalse(AssignmentService::shouldAutoRelease($schedule));

        $released = AssignmentService::autoReleaseForSchedule($schedule);
        $this->assertEquals(0, $released);

        $assignment->refresh();
        $this->assertEquals(0, $assignment->is_started);
    }

    public function test_past_session_assignments_are_auto_released(): void
    {
        $pastDate = Carbon::yesterday()->format('Y-m-d');
        $schedule = new BatchSchedule();
        $schedule->batch_id = $this->batch->id;
        $schedule->name = 'Past Session 1';
        $schedule->date = $pastDate;
        $schedule->amount = '500';
        $schedule->session_number = 1;
        $schedule->is_session_completed = 0;
        $schedule->save();

        $assignment = new Assignment();
        $assignment->batch_id = $this->batch->id;
        $assignment->batch_schedule_id = $schedule->id;
        $assignment->question = 'Past Assignment Question';
        $assignment->option_type = 'manual';
        $assignment->is_started = 0;
        $assignment->added_by = 0;
        $assignment->save();

        $this->assertTrue(AssignmentService::shouldAutoRelease($schedule));

        $released = AssignmentService::autoReleaseForSchedule($schedule);
        $this->assertEquals(1, $released);

        $assignment->refresh();
        $this->assertEquals(1, $assignment->is_started);
    }

    public function test_today_session_auto_release_timing_at_6_pm(): void
    {
        $todayDate = Carbon::today()->format('Y-m-d');
        $schedule = new BatchSchedule();
        $schedule->batch_id = $this->batch->id;
        $schedule->name = 'Today Session 1';
        $schedule->date = $todayDate;
        $schedule->amount = '500';
        $schedule->session_number = 1;
        $schedule->is_session_completed = 0;
        $schedule->save();

        // Case 1: Today at 2:00 PM (14:00) -> should NOT release
        Carbon::setTestNow(Carbon::parse($todayDate . ' 14:00:00'));
        $this->assertFalse(AssignmentService::shouldAutoRelease($schedule));
        $statusBefore6PM = AssignmentService::getAutoReleaseStatus($schedule);
        $this->assertEquals('scheduled_today', $statusBefore6PM['status']);

        // Case 2: Today at 6:00 PM (18:00) -> SHOULD release
        Carbon::setTestNow(Carbon::parse($todayDate . ' 18:00:00'));
        $this->assertTrue(AssignmentService::shouldAutoRelease($schedule));
        $statusAt6PM = AssignmentService::getAutoReleaseStatus($schedule);
        $this->assertEquals('released_today', $statusAt6PM['status']);

        // Case 3: Today at 8:30 PM (20:30) -> SHOULD release
        Carbon::setTestNow(Carbon::parse($todayDate . ' 20:30:00'));
        $this->assertTrue(AssignmentService::shouldAutoRelease($schedule));

        // Reset test time
        Carbon::setTestNow();
    }

    public function test_artisan_auto_release_command_executes_successfully(): void
    {
        $pastDate = Carbon::yesterday()->format('Y-m-d');
        $schedule = new BatchSchedule();
        $schedule->batch_id = $this->batch->id;
        $schedule->name = 'Command Test Session';
        $schedule->date = $pastDate;
        $schedule->amount = '500';
        $schedule->session_number = 1;
        $schedule->is_session_completed = 0;
        $schedule->save();

        $assignment = new Assignment();
        $assignment->batch_id = $this->batch->id;
        $assignment->batch_schedule_id = $schedule->id;
        $assignment->question = 'Command Test Assignment Question';
        $assignment->option_type = 'manual';
        $assignment->is_started = 0;
        $assignment->added_by = 0;
        $assignment->save();

        $this->artisan('assignments:auto-release')
            ->expectsOutputToContain('Auto-release completed')
            ->assertExitCode(0);

        $assignment->refresh();
        $this->assertEquals(1, $assignment->is_started);
    }

    public function test_copy_all_session_assignments_successfully(): void
    {
        // Source Schedule with 2 assignments (1 manual, 1 with options)
        $sourceSchedule = new BatchSchedule();
        $sourceSchedule->batch_id = $this->batch->id;
        $sourceSchedule->name = 'Source Session';
        $sourceSchedule->date = '2026-10-10';
        $sourceSchedule->amount = '500';
        $sourceSchedule->session_number = 1;
        $sourceSchedule->is_session_completed = 0;
        $sourceSchedule->save();

        $asgn1 = new Assignment();
        $asgn1->batch_id = $this->batch->id;
        $asgn1->batch_schedule_id = $sourceSchedule->id;
        $asgn1->question = 'Question 1: Reflection';
        $asgn1->option_type = 'manual';
        $asgn1->is_file_upload = 1;
        $asgn1->is_started = 0;
        $asgn1->added_by = $this->admin->id;
        $asgn1->save();

        $asgn2 = new Assignment();
        $asgn2->batch_id = $this->batch->id;
        $asgn2->batch_schedule_id = $sourceSchedule->id;
        $asgn2->question = 'Question 2: Rating';
        $asgn2->option_type = 'options';
        $asgn2->is_file_upload = 0;
        $asgn2->is_started = 0;
        $asgn2->added_by = $this->admin->id;
        $asgn2->save();

        $opt1 = new \App\Models\AssignmentOption();
        $opt1->batch_id = $this->batch->id;
        $opt1->batch_schedule_id = $sourceSchedule->id;
        $opt1->assignment_id = $asgn2->id;
        $opt1->option = 'Excellent';
        $opt1->mark = '10';
        $opt1->save();

        $opt2 = new \App\Models\AssignmentOption();
        $opt2->batch_id = $this->batch->id;
        $opt2->batch_schedule_id = $sourceSchedule->id;
        $opt2->assignment_id = $asgn2->id;
        $opt2->option = 'Good';
        $opt2->mark = '8';
        $opt2->save();

        // Target Schedule (currently empty)
        $targetSchedule = new BatchSchedule();
        $targetSchedule->batch_id = $this->batch->id;
        $targetSchedule->name = 'Target Session';
        $targetSchedule->date = '2026-10-20';
        $targetSchedule->amount = '500';
        $targetSchedule->session_number = 2;
        $targetSchedule->is_session_completed = 0;
        $targetSchedule->save();

        $response = $this->actingAs($this->admin)->post(route('master.batches.copy-session-assignments', $targetSchedule->id), [
            'source_schedule_id' => $sourceSchedule->id,
            'target_schedule_id' => $targetSchedule->id,
            'action_type' => 'append',
        ]);

        $response->assertRedirect(route('master.batches.view-session-assignment', $targetSchedule->id));
        $response->assertSessionHas('success');

        $targetAssignments = Assignment::where('batch_schedule_id', $targetSchedule->id)->get();
        $this->assertCount(2, $targetAssignments);

        $copiedAsgn2 = Assignment::where('batch_schedule_id', $targetSchedule->id)->where('question', 'Question 2: Rating')->first();
        $this->assertNotNull($copiedAsgn2);
        $this->assertCount(2, $copiedAsgn2->options);
    }

    public function test_duplicate_single_session_assignment_successfully(): void
    {
        $schedule = new BatchSchedule();
        $schedule->batch_id = $this->batch->id;
        $schedule->name = 'Duplicate Test Session';
        $schedule->date = '2026-10-15';
        $schedule->amount = '500';
        $schedule->session_number = 1;
        $schedule->is_session_completed = 0;
        $schedule->save();

        $asgn = new Assignment();
        $asgn->batch_id = $this->batch->id;
        $asgn->batch_schedule_id = $schedule->id;
        $asgn->question = 'Original Question';
        $asgn->option_type = 'options';
        $asgn->is_file_upload = 0;
        $asgn->is_started = 0;
        $asgn->added_by = $this->admin->id;
        $asgn->save();

        $opt = new \App\Models\AssignmentOption();
        $opt->batch_id = $this->batch->id;
        $opt->batch_schedule_id = $schedule->id;
        $opt->assignment_id = $asgn->id;
        $opt->option = 'Option A';
        $opt->mark = '5';
        $opt->save();

        $response = $this->actingAs($this->admin)->get(route('master.batches.duplicate-session-assignment', $asgn->id));
        $response->assertRedirect(route('master.batches.view-session-assignment', $schedule->id));

        $this->assertEquals(2, Assignment::where('batch_schedule_id', $schedule->id)->count());
        $duplicate = Assignment::where('batch_schedule_id', $schedule->id)->where('question', 'Original Question (Copy)')->first();
        $this->assertNotNull($duplicate);
        $this->assertCount(1, $duplicate->options);
    }
}

