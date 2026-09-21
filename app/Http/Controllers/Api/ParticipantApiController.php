<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\ParticipantBatch;
use App\Models\ParticipantSessionRating;
use App\Models\Batch;
use App\Models\BatchGroup;
use App\Models\BatchSchedule;
use App\Models\GroupMessage;
use App\Models\Recommendation;
use App\Models\ParticipantPayment;
use App\Models\Assignment;
use App\Models\AssignmentOption;
use App\Models\AssignmentSubmission;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Http\Resources\ParticipantResource;;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class ParticipantApiController extends Controller
{
    public function getMyGroups(Request $request)
    {
        $participant = $request->participant;
        $groups = $participant->batchGroups;
        return response()->json([
            'success' => true,
            'message' => 'Groups fetched successfully',
            'data'=>[
                'groups' => $groups
            ]
        ]);
    }
    public function getParticipantsOfMyGroup(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_group_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success'=>false,
                'message'=>$messages->first()
            ], 400);
        }
        $participant = $request->participant;
        $group = BatchGroup::with(['batchGroupParticipants1'=>function ($query) use ($participant) {
            $query->where('participant_id','!=', $participant->id);
        }])->find($request->batch_group_id);
        if(!$group){
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Participants fetched successfully',
            'data'=>[
                'group' => $group
            ]
        ]);
    }

    public function getParticipantProfile(Request $request){
        $validator = \Validator::make($request->all(), [
            'participant_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $participant = Participant::find($request->participant_id);
        if(!$participant){
            return response()->json([
                'success' => false,
                'message' => 'Participant not found'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Participant fetched successfully',
            'data'=>[
                'participant' => $participant
            ]
        ]);
    }

    public function getMessagesForParticipant(Request $request){
        $participant = $request->participant;
        $batch_messages = $group_messages = $participant_messages = [];
        $batch_messages = GroupMessage::where('batch_id', $participant->batch->id)
        ->whereNull('batch_group_id')
        ->whereNull('participant_id')
        ->get();

        $group_messages = GroupMessage::where('batch_id', $participant->batch->id)
        ->whereNotNull('batch_group_id')
        ->whereNull('participant_id')
        ->get();

        $participant_messages = GroupMessage::where('batch_id', $participant->batch->id)
        ->where('participant_id', $participant->id)
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Messages fetched successfully',
            'data' => [
                'batch_messages' => $batch_messages,
                'group_messages' => $group_messages,
                'participant_messages' => $participant_messages,
            ]
        ]);
    }

    public function addRecommendation(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $participant = $request->participant;
        $recommendation = new Recommendation();
        $recommendation->name = $request->name;
        $recommendation->mobile = $request->mobile;
        $recommendation->participant_id = $participant->id;
        $recommendation->save();
        return response()->json([
            'success' => true,
            'message' => 'Recommendation added successfully',
        ]);
    }

    public function getSessionPaymentLinks(Request $request){
        $participant = $request->participant;
        $batch_schedules = BatchSchedule::where('batch_id', $participant->batch->id)
        ->where('payment_link_status',1)
        ->where(function ($query) {
            $query->where('is_session_completed',2)->orWhere('is_session_completed',0);
        })
        ->get();
        $participant_payments = $participant->payments()->where('payment_for','session_fee')->where('batch_id', $participant->batch->id)->get();
        $batch_schedules = $batch_schedules->map(function($schedule) use ($participant_payments) {
            // Check if there's a payment for this session number
            $is_already_paid = $participant_payments->contains(function($payment) use ($schedule) {
                return $payment->session_number == $schedule->session_number;
            });

            // Add the is_already_paid field to the schedule
            $schedule->is_already_paid = $is_already_paid ? 1 : 0;

            return $schedule;
        });
        return response()->json([
            'success' => true,
            'message' => 'Links fetched successfully',
            'data'=>[
                'batch_schedule_payments' => $batch_schedules
            ]
        ]);
    }

    public function viewQrCode(Request $request){
        $validator = \Validator::make($request->all(), [
            'session_number' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $participant = $request->participant;
        $participant_payment = $participant->payments()->where('payment_for','session_fee')->where('batch_id', $participant->batch->id)->where('session_number', $request->session_number)->first();
        $batch_schedule = BatchSchedule::where('batch_id', $participant->batch->id)->where('session_number', $request->session_number)->first();
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'Session Schedule not found'
            ]);
        }
        if(!$participant_payment){
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ]);
        }
        if($participant_payment->is_qr_used){
            return response()->json([
                'success' => false,
                'message' => 'QR code already used',
                'data'=>[
                    'qr_code'=>asset('already-scanned-qr.jpg')
                ],
            ]);
        }
        if($batch_schedule->is_session_completed == 1){
            return response()->json([
                'success' => false,
                'message' => asset('session-already-completed.webp')
            ]);
        }
        if($batch_schedule->date > date('Y-m-d')){
            return response()->json([
                'success' => false,
                'message' => asset('session-not-started.webp'),
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'QR code fetched successfully',
            'data'=>[
                'qr_code'=>asset('uploads/participant-payment/qrcode/'.$participant_payment->qr_code_file)
            ],
        ]);
    }

    public function makeSessionPayment(Request $request){
        $validator = \Validator::make($request->all(), [
           'batch_schedule_id' => 'required|required',
           'payment_mode' => 'required',
           'payment_image' => 'required|mimes:png,jpg,jpeg|image',
           'transaction_id' => 'required',
           'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $batch_schedule = BatchSchedule::find($request->batch_schedule_id);
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'Session Schedule not found'
            ]);
        }
        if($batch_schedule->is_session_completed == 1){
            return response()->json([
                'success' => false,
                'message' => 'Session already completed',
            ]);
        }
        if(!$batch_schedule->payment_link_status){
            return response()->json([
                'success' => false,
                'message' => 'Payment link not active',
            ]);
        }

        $participant = $request->participant;
        $batch = $participant->batch;
        $participant_payment = $participant->payments()->where('payment_for','session_fee')->where('batch_id', $batch->id)->where('session_number', $batch_schedule->session_number)->first();
        if($participant_payment){
            return response()->json([
                'success' => false,
                'message' => 'Payment already added'
            ]);
        }
        try{
            DB::beginTransaction();
            $filename = '';
            $participant_payment = new ParticipantPayment();
            $participant_payment->participant_id = $participant->id;
            $participant_payment->batch_id = $participant->batch_id;
            $participant_payment->transaction_id = $request->transaction_id;
            $participant_payment->amount = $batch->fee_per_session;
            $participant_payment->payment_mode = $request->payment_mode;
            $participant_payment->payment_for = 'session_fee';
            $participant_payment->session_number = $batch_schedule->session_number;
            if($request->hasFile('payment_image')){
                $file = $request->file('payment_image');
                $filename = time().'-'.str_replace(' ','-',$file->getClientOriginalName());
                $file->move(public_path('uploads/participant-payment'), $filename);
                $participant_payment->payment_image = $filename;
            }
            $participant_payment->save();
            $data = [
                'participant_id' => $participant->id,
                'payment_id' => $participant_payment->id,
                // Add any other data you need
            ];
            $qr_code_url = route('entry-user.scan-qr-code',$data);
            $jsonData = json_encode($data);

            // Prepare the file name and directory path
            $fileName = 'qrcode_' . $participant->id . '_' . $participant_payment->id . '.png';
            $directoryPath = public_path('uploads/participant-payment/qrcode/');

            // Ensure the directory exists
            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }

            // Full path to save the QR code
            $qrCodePath = $directoryPath . $fileName;

            // Generate the QR code
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qr_code_url)  // Use JSON-encoded data
                ->size(300)
                ->build();

            // Save the QR code to the file path
            $result->saveToFile($qrCodePath);
            $participant_payment->qr_code_file = $fileName;
            $participant_payment->is_qr_used = 0;
            $participant_payment->save();


            // Update payments in participant table
            $participant->paid_amount += $batch->fee_per_session;
            $participant->due_amount -= $batch->fee_per_session;
            $participant->save();

            DB::commit();
            return response()->json([
                'success'=>true,
                'message'=>'Registration fees paid successfully'
            ]);
        }
        catch(Exception $e){
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ],500);
        }
    }
    public function getMyBatches(Request $request){
        $participant = $request->participant;
        if($participant->participantBatches->count() > 1){
            return response()->json([
                'success' => true,
                'message' => 'Batches fetched successfully',
                'data'=>[
                    'my_batches' => $participant->participantBatches
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No batches found',
        ]);
    }
    public function switchBatch(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $participant = $request->participant;
        if($participant->participantBatches->contains($request->batch_id)){
            $participant_batch = ParticipantBatch::where('participant_id', $participant->id)->where('batch_id', $request->batch_id)->first();
            $participant->batch_id = $request->batch_id;
            $participant->is_registration_fees_paid = $participant_batch->is_registration_fees_paid;
            $participant->save();
            $participant->load('batch', 'participantBatches');
            return response()->json([
                'success' => true,
                'message' => 'Batch switched successfully',
                'data'=>[
                    'participant' => new ParticipantResource($participant)
                ]
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'The participant is not enrolled in the selected batch.',
            ]);
        }
    }

    public function enrollBatch(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $participant = $request->participant;
        $batch = Batch::find($request->batch_id);
        if(!$batch || !$batch->is_active || strtolower($batch->status) == 'completed'){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        if($participant->participantBatches->contains($request->batch_id)){
            return response()->json([
                'success' => false,
                'message' => 'The participant is already enrolled in the selected batch.',
            ]);
        }
        try{
            DB::beginTransaction();
            $participant_batch = new ParticipantBatch();
            $participant_batch->participant_id = $participant->id;
            $participant_batch->batch_id = $request->batch_id;
            $participant_batch->is_registration_fees_paid = 0;
            $participant_batch->save();

            $participant->batch_id = $request->batch_id;
            $participant->is_registration_fees_paid = 0;
            $participant->total_amount += $batch->registration_fee + ($batch->number_of_sessions * $batch->fee_per_session);
            $participant->due_amount += $participant->total_amount;
            $participant->save();
            $participant->load('batch', 'participantBatches');
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Batch enrolled successfully',
                'data'=>[
                    'participant' => new ParticipantResource($participant)
                ]
            ]);
        }
        catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ],500);
        }
    }
    public function getSessionByActiveAssignmentForParticipant(Request $request){
        $batch_schedules = BatchSchedule::whereHas('assignments',function ($query) use ($request){
            $query->where('batch_id', $request->participant->batch_id)
            ->where('is_started',1);
        })->get();
        if(!$batch_schedules->count()){
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Active session fetched successfully',
            'data' => [
                'batch_schedules' => $batch_schedules
            ],
        ]);
    }
    public function getAssignmentsForParticipant(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $participant = $request->participant;
        $batch_schedule = BatchSchedule::where('batch_id', $participant->batch_id)->find($request->batch_schedule_id);
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ]);
        }
        $assignments = Assignment::with('options')->where('batch_schedule_id', $request->batch_schedule_id)->where('is_started',1)
        ->whereDoesntHave('submissions',function ($query) use ($participant){
            $query->where('participant_id', $participant->id);
        })->get();
        return response()->json([
            'success' => true,
            'message' => 'Assignments fetched successfully',
            'data' => [
                'assignments' => $assignments
            ],
        ]);
    }
    public function submit_assignment(Request $request){
        $validator = \Validator::make($request->all(), [
            'assignment_id' => 'required',
            'batch_schedule_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $participant = $request->participant;
        $assignment = Assignment::where('batch_schedule_id', $request->batch_schedule_id)->where('batch_id', $participant->batch_id)->where('is_started',1)->find($request->assignment_id);
        if(!$assignment){
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ]);
        }
        if($assignment->option_type == 'manual' && !$request->answer){
            return response()->json([
                'success' => false,
                'message' => 'Answer is required'
            ]);
        }
        if($assignment->option_type == 'options' && !$request->assignment_option_id){
            return response()->json([
                'success' => false,
                'message' => 'Assignment option id is required'
            ]);
        }
        $assignment_option = $assignment->option_type == 'options' ? AssignmentOption::where('assignment_id', $assignment->id)->find($request->assignment_option_id) : null;
        if($assignment->option_type == 'options' && !$assignment_option){
            return response()->json([
                'success' => false,
                'message' => 'Assignment option not found'
            ]);
        }
        $alreadySubmitted  = AssignmentSubmission::where('participant_id', $participant->id)->where('assignment_id', $assignment->id)->first();
        if($alreadySubmitted){
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted this assignment'
            ]);
        }
        $assignment_submission = new AssignmentSubmission();
        $assignment_submission->participant_id = $participant->id;
        $assignment_submission->assignment_id = $assignment->id;
        $assignment_submission->batch_id = $assignment->batch_id;
        $assignment_submission->batch_schedule_id = $assignment->batch_schedule_id;
        $assignment_submission->assignment_option_id = $request->assignment_option_id;
        $assignment_submission->mark = $assignment_option ? $assignment_option->mark : 0;
        $assignment_submission->answer = $request->answer;
        if($request->hasFile('file')){
            $file = $request->file('file');
            $filename = time().'_'.$participant->id.'_'.$assignment->id.'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/participants/assignment'), $filename);
            $assignment_submission->file = $filename;
        }
        $assignment_submission->save();
        return response()->json([
            'success' => true,
            'message' => 'Assignment submitted successfully',
        ]);
    }

    public function getSessionsByAnswerForParticipant(Request $request){
        $participant = $request->participant;
        $batch_schedules = BatchSchedule::whereHas('assignmentSubmissions',function ($query) use ($participant){
            $query->where('participant_id', $participant->id);
        })->get();
        return response()->json([
            'success' => true,
            'message' => 'Schedules fetched successfully',
            'data' => [
                'schedules' => $batch_schedules
            ]
        ]);
    }
    public function getQuestionAndAnswerBySessionForParticipant(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $participant = $request->participant;
        $batch_schedule = BatchSchedule::find($request->batch_schedule_id);
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ]);
        }
        $assignmentSubmissions = $batch_schedule->assignmentSubmissions->where('participant_id', $participant->id);
        $assignmentSubmissions = $assignmentSubmissions->transform(function ($item, $key) {
           if($item->file){
               $item->file = asset('uploads/participants/assignment/'.$item->file);
           }
           return $item;
        });
        $total_out_of_marks = 300;
        foreach($batch_schedule->assignments as $assignment){
            $total_out_of_marks += $assignment->options->max('mark');
        }
        $total_marks_obtained = $assignmentSubmissions->sum('mark');

        $online_meetings = Meeting::where('batch_schedule_id', $request->batch_schedule_id)->where('type', 'online')->where('status', 'completed')->get();
        $online_meetings_attended = MeetingAttendance::whereIn('meeting_id', $online_meetings->pluck('id'))
        ->where('participant_id', $participant->id)
        ->where('status', 'present')
        ->count();

        $offline_meetings = Meeting::where('batch_schedule_id', $request->batch_schedule_id)->where('type', 'offline')->where('status', 'completed')->get();
        $offline_meetings_attended = MeetingAttendance::whereIn('meeting_id', $offline_meetings->pluck('id'))
        ->where('participant_id', $participant->id)
        ->where('status', 'present')
        ->count();

        $online_meeting_attended_marks = $online_meetings->count() > 0 ? ($online_meetings_attended / $online_meetings->count()) * 100 : 0;
        $offline_meeting_attended_marks = $offline_meetings->count() > 0 ? ($offline_meetings_attended / $offline_meetings->count()) * 100 : 0;
        $total_marks_obtained = $total_marks_obtained + $online_meeting_attended_marks + $offline_meeting_attended_marks;

        // Check if the participant is registered for the next session
        $next_session = BatchSchedule::where('batch_id', $batch_schedule->batch_id)
        ->where('session_number', '>', $batch_schedule->session_number)
        ->orderBy('session_number', 'asc')
        ->first();
        $is_registered_for_next_session = false;
        $staticQuestions = [
            [
                'question' => 'How many Offline group meetings you have attended?',
                'formula' => 'Formula: (Total meetings attended)/ (Total meetings conducted) * 100',
                'response'=>'',
                'marks_obtained' => $offline_meetings->count() . ' / ' . $offline_meetings_attended . ' x 100 = ' . $offline_meeting_attended_marks
            ],
            [
                'question' => 'How many Online group meetings you have attended?',
                'formula' => 'Formula: (Total meetings attended)/ (Total meetings conducted) * 100',
                'response'=>'',
                'marks_obtained' => $online_meetings->count() . ' / ' . $online_meetings_attended . ' x 100 = ' . $online_meeting_attended_marks
            ],
        ];
        $rating = ParticipantSessionRating::where('participant_id', $participant->id)->where('batch_schedule_id', $batch_schedule->id)->where('rating_type', 'coach_to_participant')->first();
        if($rating){
            $total_marks_obtained += $rating->rating * 10;
            $staticQuestions[] = [
                'question' => 'Coach Rating',
                'formula' => 'On the scale of 1 to 10',
                'response' => $rating->rating,
                'marks_obtained' => $rating->rating * 10
            ];
        }
        if ($next_session) {
            $staticQuestions[] = [
                'question' => 'Registration For Next Session?',
                'formula' => '',
                'response' => $is_registered_for_next_session ? 'Yes' : 'No',
                'marks_obtained' => $is_registered_for_next_session ? '100 Marks' : '0 Marks'
            ];
            $total_out_of_marks += 100;
            // Check if the participant has paid for the next session
            $payment = ParticipantPayment::where('participant_id', $participant->id)
                ->where('batch_id', $batch_schedule->batch_id)
                ->where('session_number', $next_session->session_number)
                ->first();

            // If participant is registered for the next session, add 100 marks
            if ($payment) {
                $total_marks_obtained += 100;
                $is_registered_for_next_session = true;
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Assignment submissions fetched successfully',
            'data' => [
                'assignmentSubmissions' => $assignmentSubmissions,
                'total_out_of_marks' => $total_out_of_marks,
                'total_marks_obtained' => $total_marks_obtained,
                'staticQuestions' => $staticQuestions
            ]
        ]);
    }
    public function getMeetingsForParticipant(Request $request){
        $participant = $request->participant;
        $batch_group_ids = $participant->batchGroups->pluck('id');
        $meetings = Meeting::whereIn('batch_group_id', $batch_group_ids)->where('batch_id', $participant->batch_id)
        ->where('status', 'active')->get();
        $completed_meetings = Meeting::whereIn('batch_group_id', $batch_group_ids)->where('batch_id', $participant->batch_id)
        ->where('status', 'completed')->get();
        return response()->json([
            'success' => true,
            'message' => 'Meetings fetched successfully',
            'data' => [
                'upcoming_meetings' => $meetings,
                'completed_meetings' => $completed_meetings
            ],
        ]);
    }
}
