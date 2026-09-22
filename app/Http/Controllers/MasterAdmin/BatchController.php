<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\BatchBanner;
use App\Models\BatchGroup;
use App\Models\BatchSchedule;
use App\Models\Assignment;
use App\Models\AssignmentOption;
use App\Models\BatchGroupParticipant;
use App\Models\Participant;
use App\Models\Course;
use App\Models\Coach;
use Auth;
use Rinvex\Country\CountryLoader;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\FcmNotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BatchController extends Controller
{
    protected $fcmNotificationService;

    public function __construct(FcmNotificationService $fcmNotificationService)
    {
        $this->fcmNotificationService = $fcmNotificationService;
    }
    public function list(){
        $batches = Batch::orderBy('id', 'desc')->get();
        return view('master.batch.list', compact('batches'));
    }
    public function add(){
        $courses = Course::orderBy('id', 'desc')->get();
        return view('master.batch.create', compact('courses'));
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'course_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'number_of_sessions' => 'required|numeric',
            'registration_fee' => 'required|numeric',
            'fee_per_session' => 'required|numeric',
            'is_one_session_advance_payment' => 'required|numeric',
        ], [
            'course_id.required' => 'Course is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        try{
            DB::beginTransaction();
            $batch = new Batch();
            $batch->name = $request->name;
            $batch->course_id = $request->course_id;
            $batch->number_of_sessions = $request->number_of_sessions;
            $batch->registration_fee = $request->registration_fee;
            $batch->fee_per_session = $request->fee_per_session;
            $batch->is_one_session_advance_payment = $request->is_one_session_advance_payment;
            $batch->start_date = $request->start_date;
            $batch->end_date = $request->end_date;
            $batch->added_by = Auth::user()->id;
            $batch->status = '';
            $batch->description = $request->description;
            if($request->hasFile('image')){
                $file = $request->file('image');
                $file_name = time().'_'.str_replace(' ','-',$file->getClientOriginalName());
                $file->move(public_path('uploads/batches'), $file_name);
                $batch->image = $file_name;
            }
            $batch->save();

            for($i = 0; $i < $request->number_of_sessions; $i++){
                $batchSchedule = new BatchSchedule();
                $batchSchedule->batch_id = $batch->id;
                $batchSchedule->name = '';
                // $batchSchedule->date = '';
                $batchSchedule->amount = $batch->fee_per_session;
                if($i == 0){
                    $batchSchedule->payment_link_status = 1;
                }
                $batchSchedule->session_number = $i+1;
                $batchSchedule->save();
            }
            DB::commit();

            return redirect()->route('master.batches.list')->with('success', 'Batch Added Successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
    public function view($id){
        $batch = Batch::findOrFail($id);
        $participantsNotInAnyGroup = Participant::where('batch_id', $batch->id)
        ->whereDoesntHave('batchGroupParticipants')->orderBy('first_name', 'ASC')
        ->get();
        return view('master.batch.view', compact('batch', 'participantsNotInAnyGroup'));
    }
    public function edit($id){
        $batch = Batch::findOrFail($id);
        $courses = Course::orderBy('id', 'desc')->get();
        return view('master.batch.edit', compact('batch', 'courses'));
    }
    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'course_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            // 'number_of_sessions' => 'required|numeric',
            'registration_fee' => 'required|numeric',
            'fee_per_session' => 'required|numeric',
            'is_one_session_advance_payment' => 'required|numeric',
        ], [
            'course_id.required' => 'Course is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $batch = Batch::findOrFail($id);
        $batch->name = $request->name;
        $batch->course_id = $request->course_id;
        // $batch->number_of_sessions = $request->number_of_sessions;
        $batch->registration_fee = $request->registration_fee;
        $batch->fee_per_session = $request->fee_per_session;
        $batch->is_one_session_advance_payment = $request->is_one_session_advance_payment;
        $batch->start_date = date('Y-m-d', strtotime($request->start_date));
        $batch->end_date = date('Y-m-d', strtotime($request->end_date));
        $batch->description = $request->description;
        if($request->hasFile('image')){
            if($batch->image && file_exists(public_path('uploads/batches/'.$batch->image))){
                unlink(public_path('uploads/batches/'.$batch->image));
            }
            $file = $request->file('image');
            $file_name = time().'_'.str_replace(' ','-',$file->getClientOriginalName());
            $file->move(public_path('uploads/batches'), $file_name);
            $batch->image = $file_name;
        }
        $batch->save();
        return redirect()->route('master.batches.list')->with('success', 'Batch Updated Successfully');
    }
    public function delete($id){
        $batch = Batch::findOrFail($id);
        $batch->delete();
        return redirect()->route('master.batches.list')->with('success', 'Batch Deleted Successfully');
    }
    public function update_active_status($id){
        $batch = Batch::findOrFail($id);
        $batch->is_active = !$batch->is_active;
        $batch->save();
        return redirect()->back()->with('success', 'Batch Status Updated Successfully');
    }
    public function start_coach_registration(Request $request, $id){
        $batch = Batch::findOrFail($id);
        $validator = \Validator::make($request->all(), [

            'terms_and_conditions' => 'required',
            'banner'=>'required|mimes:png,jpg,jpeg',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        try{
            DB::beginTransaction();
            $activeBanner = BatchBanner::where('batch_id', $batch->id)->where('is_active', 1)->first();
            if ($activeBanner) {
                $activeBanner->is_active = 0;
                $activeBanner->save();
            }
            $batch_banner = new BatchBanner();
            $batch_banner->batch_id = $batch->id;
            $batch_banner->terms_and_conditions = $request->terms_and_conditions;
            $batch_banner->is_i_agree_mark = $request->is_i_agree_mark ? 1 : 0;
            if($request->hasFile('banner')){
                $file = $request->file('banner');
                $filename = time().'-'.str_replace(' ','-',$file->getClientOriginalName());
                $file->move(public_path('uploads/batch-banners'), $filename);
                $batch_banner->banner = $filename;
            }
            $batch_banner->save();
            $batch->start_coach_registration = 1;
            $batch->save();
            DB::commit();
            return redirect()->back()->with('success', 'Registration Started and Banner Added Successfully');
        }
        catch(Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }

    }
    public function stop_coach_registration($id){
        $batch = Batch::findOrFail($id);
        $batch->start_coach_registration = 0;
        $batch->save();
        BatchBanner::where('batch_id', $batch->id)->update(['is_active' => 0]);
        return redirect()->back()->with('success', 'Registration Stopped Successfully');
    }

    public function mark_batch_completed(Request $request, $id){
        $batch = Batch::findOrFail($id);

        //check if batch status is completed then remove the status and make it blank
        if($batch->status == 'completed'){
            $batch->status = '';
            $batch->save();
            return redirect()->back()->with('error', 'Removed Completed Status');
        }
        else{
            // Check if the batch end date is greater than the current date

        // Convert stored date and current date to Carbon instances
        $batchEndDate = Carbon::createFromFormat('d-m-Y', $batch->end_date);
        $currentDate = Carbon::now(); // Defaults to Y-m-d format

        if($batchEndDate->gt($currentDate)){ // gt = greater than
            return redirect()->back()->with('error', 'You cannot mark batch completed before the end date');
        }

        $batch->status = 'completed';
        $batch->save();
        return redirect()->back()->with('success', 'Batch Completed Successfully');
    }

    }
    public function update_batch_banner(Request $request, $id){
        $validator = \Validator::make($request->all(), [

            'terms_and_conditions' => 'required',
            'banner'=>'nullable|mimes:png,jpg,jpeg',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $batch_banner = BatchBanner::findOrFail($id);
        $batch_banner->terms_and_conditions = $request->terms_and_conditions;
        $batch_banner->is_i_agree_mark = $request->is_i_agree_mark ? 1 : 0;
        if($request->hasFile('banner')){
            if($batch_banner->banner && file_exists(public_path('uploads/batch-banners/'.$batch_banner->banner))){
                unlink(public_path('uploads/batch-banners/'.$batch_banner->banner));
            }
            $file = $request->file('banner');
            $filename = time().'-'.str_replace(' ','-',$file->getClientOriginalName());
            $file->move(public_path('uploads/batch-banners'), $filename);
            $batch_banner->banner = $filename;
        }
        $batch_banner->save();
        return redirect()->back()->with('success', 'Banner Updated Successfully');
    }
    public function update_batch_banner_active_status($id){
        $batch_banner = BatchBanner::findOrFail($id);
        if($batch_banner->batch->start_coach_registration){

            $batch_banner->is_active = !$batch_banner->is_active;
            $batch_banner->save();
            return redirect()->back()->with('success', 'Banner Status Updated Successfully');
        }
        return redirect()->back()->with('error', 'Please Start Coach Registration First');
    }
    public function delete_batch_banner($id){
        $batch_banner = BatchBanner::findOrFail($id);
        if($batch_banner->banner && file_exists(public_path('uploads/batch-banners/'.$batch_banner->banner))){
            unlink(public_path('uploads/batch-banners/'.$batch_banner->banner));
        }
        $batch_banner->delete();
        return redirect()->back()->with('success', 'Banner Deleted Successfully');
    }

    public function add_group(Request $request, $id){

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'coach_id' => 'required',
        ], [
            'coach_id.required' => 'Coach is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        // $check = BatchGroup::where('coach_id', $request->coach_id)->where('batch_id', $id)->first();
        // if($check){
        //     return redirect()->back()->with('error', 'Group Already Added For This Coach');
        // }
        $batch = Batch::findOrFail($id);
        $batch_group = new BatchGroup();
        $batch_group->name = $request->name;
        $batch_group->coach_id = $request->coach_id;
        $batch_group->batch_id = $id;
        $batch_group->added_by = Auth::user()->id;
        $batch_group->save();
        return redirect()->back()->with('success', 'Group Added Successfully');
    }

    public function update_group(Request $request, $id){

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'coach_id' => 'required',
        ], [
            'coach_id.required' => 'Coach is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $batch_group = BatchGroup::findOrFail($id);
        $batch_group->name = $request->name;
        $batch_group->coach_id = $request->coach_id;
        $batch_group->save();
        return redirect()->back()->with('success', 'Group Updated Successfully');
    }
    public function delete_group($id){
        $batch_group = BatchGroup::findOrFail($id);
        $batch_group->delete();
        return redirect()->back()->with('success', 'Group Deleted Successfully');
    }

    public function add_participant_to_group(Request $request){

        $validator = \Validator::make($request->all(), [
            'batch_id'=>'required',
            'batch_group_id'=>'required',
            'participant_id'=>'required',
        ], [
            'participant_id.required' => 'Participant is required',
            'batch_id.required' => 'Batch is required',
            'batch_group_id.required' => 'Batch Group is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $check = BatchGroupParticipant::where('batch_id', $request->batch_id)->where('participant_id', $request->participant_id)->first();
        if($check){
            return redirect()->back()->with('error', 'Participant Already Added To Group');
        }
        $batch = Batch::findOrFail($request->batch_id);
        $batch_group_participant = new BatchGroupParticipant();
        $batch_group_participant->batch_id = $request->batch_id;
        $batch_group_participant->batch_group_id = $request->batch_group_id;
        $batch_group_participant->participant_id = $request->participant_id;
        $batch_group_participant->added_by = Auth::user()->id;
        $batch_group_participant->save();
        return redirect()->back()->with('success', 'Participant Added To Group Successfully');
    }

    public function remove_participant_from_group($id){
        $batch_group_participant = BatchGroupParticipant::findOrFail($id);
        $batch_group_participant->delete();
        return redirect()->back()->with('success', 'Participant Removed From Group Successfully');
    }

    public function update_head_coach_status($id){
        $coach = Coach::findOrFail($id);
        $coach->is_head_coach = !$coach->is_head_coach;
        $coach->save();
        return redirect()->back()->with('success', 'Status Updated Successfully');
    }

    public function add_batch_schedule(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id'=>'required',
            'name.*'=>'required',
            'date.*'=>'required',
            'amount.*'=>'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        foreach ($request->name as $key => $value) {
            $attributes = [
                'batch_id' => $request->batch_id,
                'session_number' => $key + 1, // Unique combination for identification
            ];
            $values = [
                'name' => $request->name[$key],
                'date' => $request->date[$key],
                'amount' => $request->amount[$key],
                'payment_link_status' => $key == 0 ? 1 : 0,
            ];
            BatchSchedule::updateOrCreate($attributes, $values);
        }
        return redirect()->back()->with('success', 'Schedule Updated Successfully');
    }

    public function update_batch_schedule(Request $request,$id){
        $validator = \Validator::make($request->all(), [
            'name'=>'required',
            'date'=>'required',
            'amount'=>'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $batch_schedule = BatchSchedule::findOrFail($id);
        $batch_schedule->name = $request->name;
        $batch_schedule->date = $request->date;
        $batch_schedule->amount = $request->amount;
        $batch_schedule->save();
        return redirect()->back()->with('success', 'Schedule Updated Successfully');
    }

    public function start_receive_payment($id){
        $batch_schedule = BatchSchedule::findOrFail($id);
        if($batch_schedule->is_session_completed){
            return redirect()->back()->with('error', 'Session Already Completed');
        }
        $batch_schedule->payment_link_status = 1;
        $batch_schedule->save();
        $device_tokens = $batch_schedule->batch?->batchParticipants->whereNotNull('device_token')->pluck('device_token')->toArray();
        $this->fcmNotificationService->sendNotification($device_tokens, 'Payment Link', 'Payment Link Started', 'receive_payment_link', $batch_schedule);
        return redirect()->back()->with('success', 'Receive Payment Link Started Successfully');
    }

    public function mark_session_completed($id){
        $batch_schedule = BatchSchedule::findOrFail($id);
        if($batch_schedule->date > date('Y-m-d')){
            return redirect()->back()->with('error', 'Session date is not started yet');
        }
        $batch_schedule->is_session_completed = 1;
        $batch_schedule->payment_link_status = 0;
        $batch_schedule->save();
        return redirect()->back()->with('success', 'Session Completed Successfully');
    }

    public function mark_session_active($id){
        $batch_schedule = BatchSchedule::findOrFail($id);
        $exists = BatchSchedule::where('batch_id',$batch_schedule->batch_id)->where('is_session_completed',2)->exists();
        if($exists){
          return redirect()->back()->with('error', 'Mark Previous Session As Completed First');
        }
        $batch_schedule->is_session_completed = 2;
        $batch_schedule->save();
        return redirect()->back()->with('success', 'Session Active Successfully');
    }

    public function view_session_assignment($id){
        $batch_schedule = BatchSchedule::with('assignments')->findOrFail($id);
        return view('master.batch.view-session-assignment',compact('batch_schedule'));
    }
    public function add_session_assignment($id){
        $batch_schedule = BatchSchedule::findOrFail($id);
        return view('master.batch.add-session-assignment',compact('batch_schedule'));
    }
    public function store_session_assignment(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'question'=>'required',
            'option_type'=>'required',
            'option.*'=>'required_if:option_type,options',
            'mark.*'=>'required_if:option_type,options',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $batch_schedule = BatchSchedule::findOrFail($id);
        try{
            DB::beginTransaction();
            $assignment = new Assignment();
            $assignment->batch_id  = $batch_schedule->batch_id;
            $assignment->batch_schedule_id = $batch_schedule->id;
            $assignment->question = $request->question;
            $assignment->option_type = $request->option_type;
            $assignment->is_file_upload = $request->is_file_upload ?? 0;
            $assignment->added_by = 0;
            $assignment->save();
            if($request->option_type == 'options'){
                foreach ($request->option as $key => $value) {
                    $option = new AssignmentOption();
                    $option->batch_id  = $batch_schedule->batch_id;
                    $option->batch_schedule_id = $batch_schedule->id;
                    $option->assignment_id = $assignment->id;
                    $option->option = $request->option[$key];
                    $option->mark = $request->mark[$key];
                    $option->save();
                }
            }
            DB::commit();
            $device_tokens = $batch_schedule->batch?->batchParticipants()->whereNotNull('device_token')->pluck('device_token')->toArray();
            $this->fcmNotificationService->sendNotification($device_tokens, 'New Assignment', 'A new assignment has been added.','new_assignment',$assignment);
            return redirect()->route('master.batches.view-session-assignment',$id)->with('success', 'Session Assignment Added Successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit_session_assignment($id){
        $assignment = Assignment::findOrFail($id);
        return view('master.batch.edit-session-assignment',compact('assignment'));
    }

    public function update_session_assignment(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'question' => 'required',
            'option_type' => 'required',
            'option.*' => 'required_if:option_type,options',
            'mark.*' => 'required_if:option_type,options',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        try {
            DB::beginTransaction();

            $assignment = Assignment::findOrFail($id);
            $assignment->question = $request->question;
            $assignment->option_type = $request->option_type;
            $assignment->is_file_upload = $request->is_file_upload ?? 0;
            $assignment->save();

            // Handle option updates or deletions
            if ($request->option_type == 'options') {
                // Get existing option IDs
                $existingOptionIds = $assignment->options()->pluck('id')->toArray();

                foreach ($request->option as $key => $value) {
                    if (isset($request->option_ids[$key])) {
                        // Update existing options
                        $option = AssignmentOption::findOrFail($request->option_ids[$key]);
                        $option->option = $value;
                        $option->mark = $request->mark[$key];
                        $option->save();

                        // Remove the option ID from the array to keep track of processed options
                        $existingOptionIds = array_diff($existingOptionIds, [$option->id]);
                    } else {
                        // Add new options
                        $option = new AssignmentOption();
                        $option->batch_id = $assignment->batch_id;
                        $option->batch_schedule_id = $assignment->batch_schedule_id;
                        $option->assignment_id = $assignment->id;
                        $option->option = $value;
                        $option->mark = $request->mark[$key];
                        $option->save();
                    }
                }

                // Remove unprocessed existing options
                AssignmentOption::whereIn('id', $existingOptionIds)->delete();
            } else {
                // If option_type is changed to 'manual', delete all existing options
                $assignment->options()->delete();
            }

            DB::commit();
            return redirect()->route('master.batches.view-session-assignment', $assignment->batch_schedule_id)->with('success', 'Session Assignment Updated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete_session_assignment($id){
        $assignment = Assignment::findOrFail($id);
        $assignment->delete();
        return redirect()->back()->with('success', 'Assignment Deleted Successfully');
    }

    public function start_stop_session_assignment($id){
        $assignment = Assignment::findOrFail($id);
        $assignment->is_started = !$assignment->is_started;
        $assignment->save();
        return redirect()->back()->with('success', 'Assignment Status Updated Successfully');
    }

    public function start_all_session_assignments($id){
        $batch_schedule = BatchSchedule::findOrFail($id);
        $batch_schedule->assignments->each(function($assignment){
            $assignment->is_started = true;
            $assignment->save();
        });
        return redirect()->back()->with('success', 'All Assignments Started Successfully');
    }
    public function stop_all_session_assignments($id){
        $batch_schedule = BatchSchedule::findOrFail($id);
        $batch_schedule->assignments->each(function($assignment){
            $assignment->is_started = false;
            $assignment->save();
        });
        return redirect()->back()->with('success', 'All Assignments Stopped Successfully');
    }
    public function update_rating_start_status($id){
        $batch_schedule = BatchSchedule::findOrFail($id);
        $batch_schedule->is_coach_rating_started = !$batch_schedule->is_coach_rating_started;
        $batch_schedule->save();
        return redirect()->back()->with('success', 'Coach Rating Status Updated Successfully');
    }

    public function send_session_whatsapp_reminder(Request $request, $id)
    {
        $batchSchedule = BatchSchedule::with(['batch.batchParticipants'])->findOrFail($id);
        $participants = $batchSchedule->batch?->batchParticipants()->where('is_active', 1)->get() ?? collect();

        if ($participants->isEmpty()) {
            return redirect()->back()->with('error', 'No active participants enrolled in this batch');
        }

        $sessionName = $batchSchedule->name ? $batchSchedule->name : 'Session #' . $batchSchedule->session_number;
        $batchName = $batchSchedule->batch?->name ?? '';
        $sessionDate = $batchSchedule->date ? date('d-m-Y', strtotime($batchSchedule->date)) : '';

        $customMessageTemplate = $request->input('message');
        $successCount = 0;

        foreach ($participants as $participant) {
            if (empty($participant->mobile)) {
                continue;
            }

            if ($customMessageTemplate) {
                $message = str_replace(
                    ['{name}', '{session_name}', '{batch_name}', '{session_date}'],
                    [$participant->first_name, $sessionName, $batchName, $sessionDate],
                    $customMessageTemplate
                );
            } else {
                $message = "Dear {$participant->first_name}, this is a reminder for your upcoming session: '{$sessionName}' (Batch: {$batchName}) scheduled on {$sessionDate}. Please be on time!";
            }

            if ($this->sendWhatsAppMessage($participant->mobile, $message)) {
                $successCount++;
            }
        }

        return redirect()->back()->with('success', "WhatsApp session reminder sent to {$successCount} active participants!");
    }

    private function sendWhatsAppMessage($mobile, $message)
    {
        try {
            $mobile = preg_replace('/[^0-9]/', '', $mobile);
            if (strlen($mobile) === 10) {
                $mobile = '91' . $mobile;
            }

            $apiUrl   = env('WHATSAPP_API_URL', 'https://api.dotphi.com/wapp/v2/api/send');
            $apiToken = env('WHATSAPP_API_TOKEN', '');

            $response = Http::get($apiUrl, [
                'apikey' => $apiToken,
                'mobile' => $mobile,
                'msg'    => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp session reminder to {$mobile}: " . $e->getMessage());
            return false;
        }
    }
}
