<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\ParticipantPayment;
use App\Models\ParticipantBatch;
use App\Models\ParticipantSessionRating;
use App\Models\Batch;
use App\Models\Coach;
use App\Models\BatchSchedule;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use MenaraSolutions\Geographer\Earth;
use Auth;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use App\Imports\ParticipantImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;

class ParticipantController extends Controller
{
    public function list(){
        $participants = Participant::orderBy('id', 'desc')->get();
        return view('master.participants.list', compact('participants'));
    }
    public function add(){
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        $earth=new Earth();
        $countries=array_column($earth->getCountries()->toArray(), 'name');
        $participants = Participant::orderBy('id', 'desc')->get();
        $coaches = Participant::where('is_coach', 1)->orWhereHas('batches')->distinct()->orderBy('first_name', 'ASC')->get();
        $headCoachIds = Coach::where('is_head_coach', 1)->pluck('participant_id')->unique();
        $headCoaches = Participant::whereIn('id', $headCoachIds)->orderBy('first_name', 'ASC')->get();
        return view('master.participants.create', compact('batches','countries','participants','coaches','headCoaches'));
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required|unique:participants,mobile',
            'batch_id' => 'required',
            'reference' => 'required',
            'reference_detail'=> 'required',
            'birth_date' => 'nullable|date',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        // $validator->after(function ($validator) use ($request) {
        //     // Check if a record with the same first_name, last_name, and mobile exists
        //     $exists = Participant::where('first_name', $request->input('first_name')) // Replace with your table name
        //         ->where('last_name', $request->input('last_name'))
        //         ->where('mobile', $request->input('mobile'))
        //         ->exists();

        //     if ($exists) {
        //         $validator->errors()->add('mobile', 'A record with this name and mobile already exists.');
        //     }
        // });
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        try{
            DB::beginTransaction();
            $batch = Batch::findOrFail($request->batch_id);
            $participant = new Participant();
            $participant->first_name = $request->first_name;
            $participant->last_name = $request->last_name;
            $participant->mobile = $request->mobile;
            $participant->batch_id = $request->batch_id;
            $participant->email = $request->email;
            $participant->birth_date = !empty($request->birth_date) ? date('Y-m-d', strtotime($request->birth_date)) : null;
            $participant->address = $request->address;
            $participant->country = $request->country;
            $participant->state = $request->state;
            $participant->city = $request->city;
            $participant->reference = $request->reference;
            $participant->reference_detail = $request->reference_detail;
            $participant->type = 'student';
            $participant->added_by = Auth::user()->id;
            $participant->total_amount = $batch->registration_fee + ($batch->number_of_sessions * $batch->fee_per_session);
            $participant->paid_amount = 0;
            $participant->due_amount = $participant->total_amount;
            $participant->registration_type = 'admin';
            $participant->save();

            $participantBatch = new ParticipantBatch();
            $participantBatch->participant_id = $participant->id;
            $participantBatch->batch_id = $request->batch_id;
            $participantBatch->is_registration_fees_paid = 0;
            $participantBatch->save();
            DB::commit();
            return redirect()->route('master.participants.add-registration-payment',$participantBatch->id)->with('success', 'Participant Added Successfully');
        }
        catch(\Exception $e){
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function view($id){
        $participant = Participant::findOrFail($id);
        $batches = Batch::whereDoesntHave('batchParticipants',function ($query) use ($participant){
            $query->where('participant_id',$participant->id);
        })->active()->where('status','!=','completed')->where('start_date','>=',date('Y-m-d'))->orderBy('id', 'desc')->get();
        return view('master.participants.view', compact('participant','batches'));
    }
    public function edit($id){
        $participant = Participant::findOrFail($id);
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        $earth=new Earth();
        $countries=array_column($earth->getCountries()->toArray(), 'name');
        $states=[];
        $cities=[];
        if ($participant->country) {
            try {
                $country = $earth->getCountries()->findOne(['name' => $participant->country]);

                if ($country) {
                    $states = $country->getStates()->toArray();
                } else {
                    throw new \Exception("Country not found: " . $participant->country);
                }
            } catch (\Exception $e) {
                // dd("Exception in getting states: " . $e->getMessage());
                $states = [];
            }
        }

        if ($participant->state) {
            try {
                if (isset($country)) {
                    $state = $country->getStates()->findOne(['name' => $participant->state]);

                    if ($state) {
                        $cities = $state->getCities()->toArray();
                    } else {
                        throw new \Exception("State not found: " . $participant->state);
                    }
                }
            } catch (\Exception $e) {
                // dd("Exception in getting cities: " . $e->getMessage());
                $cities = [];
            }
        }
        foreach ($cities as &$city) {
            $city['name'] = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $city['name']);
        }
        $participants = Participant::orderBy('id', 'desc')->where('id', '!=', $id)->get();
        $coaches = Participant::where('id', '!=', $id)->where(function($q){
            $q->where('is_coach', 1)->orWhereHas('batches');
        })->orderBy('first_name', 'ASC')->get();
        $headCoachIds = Coach::where('is_head_coach', 1)->pluck('participant_id')->unique();
        $headCoaches = Participant::whereIn('id', $headCoachIds)->where('id', '!=', $id)->orderBy('first_name', 'ASC')->get();
        return view('master.participants.edit', compact('participant','batches','countries','states','cities','participants','coaches','headCoaches'));
    }

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required|unique:participants,mobile,'.$id,
            'batch_id' => 'required',
            'reference' => 'required',
            'reference_detail'=> 'required',
            'birth_date' => 'nullable|date',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        // $validator->after(function ($validator) use ($request, $id) {
        //     // Check if a record with the same first_name, last_name, and mobile exists
        //     $exists = Participant::where('first_name', $request->input('first_name')) // Replace with your table name
        //         ->where('last_name', $request->input('last_name'))
        //         ->where('mobile', $request->input('mobile'))
        //         ->where('id', '!=', $id)
        //         ->exists();
        //     if ($exists) {
        //         $validator->errors()->add('mobile', 'A record with this name and mobile already exists.');
        //     }
        // });
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $participant = Participant::findOrFail($id);
        $previous_batch_id = $participant->batch_id;
        $participant->first_name = $request->first_name;
        $participant->last_name = $request->last_name;
        $participant->mobile = $request->mobile;
        $participant->batch_id = $request->batch_id;
        $participant->email = $request->email;
        $participant->birth_date = !empty($request->birth_date) ? date('Y-m-d', strtotime($request->birth_date)) : null;
        $participant->address = $request->address;
        $participant->country = $request->country;
        $participant->state = $request->state;
        $participant->city = $request->city;
        $participant->reference = $request->reference;
        $participant->reference_detail = $request->reference_detail;
        $participant->type = 'student';
        $participant->added_by = Auth::user()->id;
        $participant->save();

        $participantBatch = ParticipantBatch::where('participant_id', $id)->where('batch_id', $previous_batch_id)->first();
        if($participantBatch){
            $participantBatch->batch_id = $request->batch_id;
            $participantBatch->save();
        }
        return redirect()->route('master.participants.list')->with('success', 'Participant Updated Successfully');
    }

    public function delete($id){
        $participant = Participant::findOrFail($id);
        $participant->delete();
        return redirect()->route('master.participants.list')->with('success', 'Participant Deleted Successfully');
    }

    public function update_active_status($id){
        $participant = Participant::findOrFail($id);
        $participant->is_active = !$participant->is_active;
        $participant->save();
        return redirect()->route('master.participants.list')->with('success', 'Participant Status Updated Successfully');
    }
    public function getStates(Request $request){
        $country=$request->country;
        $earth=new Earth();
        $states=$earth->getCountries()->findOne(['name' => $country])->getStates()->toArray();
        return response()->json($states);
    }
    public function getCities(Request $request){
        $earth = new Earth();
        $cities=$earth->getCountries()->findOne(['name' => $request->country])->getStates()->findOne(['name' => $request->state])->getCities()->toArray();
        foreach ($cities as &$city) {
            $city['name'] = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $city['name']);
        }
        return response()->json($cities);
    }
    public function add_registration_payment($id){
        // $participant = Participant::findOrFail($id);
        // if($participant->is_registration_fees_paid){
        //     return back()->with('error','Registration fees is already Paid');
        // }
        $participant_batch = ParticipantBatch::findOrFail($id);
        if($participant_batch->is_registration_fees_paid){
            return back()->with('error','Registration fees is already Paid');
        }
        $participant = Participant::findOrFail($participant_batch->participant_id);
        return view('master.participants.add-registration-payment', compact('participant'));
    }
    public function store_registration_payment(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'payment_mode' => 'required',
            'payment_image' => 'required',
            'transaction_id' => 'required',
            'amount' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        try{


            DB::beginTransaction();
            $filename = '';
            $participant = Participant::findOrFail($id);
            $batch = Batch::findOrFail($participant->batch_id);
            $paid_amount = $request->amount;
            $expected_amount = $batch->registration_fee + $batch->fee_per_session;
            if($paid_amount <= $batch->registration_fee){
                $participant_payment = new ParticipantPayment();
                $participant_payment->participant_id = $participant->id;
                $participant_payment->batch_id = $participant->batch_id;
                $participant_payment->transaction_id = $request->transaction_id;
                $participant_payment->amount = $paid_amount;
                $participant_payment->payment_mode = $request->payment_mode;
                if($request->hasFile('payment_image')){
                    $file = $request->file('payment_image');
                    $filename = time().'-'.str_replace(' ','-',$file->getClientOriginalName());
                    $file->move(public_path('uploads/participant-payment'), $filename);
                    $participant_payment->payment_image = $filename;
                }
                $participant_payment->payment_for = 'registration';
                $participant_payment->save();

                $participant_batch = ParticipantBatch::where('participant_id', $participant->id)->where('batch_id', $participant->batch_id)->first();
                $participant->paid_amount += $paid_amount;
                $participant->due_amount -= $paid_amount;
                $participant->save();
                if($paid_amount == $batch->registration_fee){
                    if($participant_batch){
                        if($participant_batch->batch_id == $participant->batch_id){

                            $participant->is_registration_fees_paid = true;
                            $participant->save();
                        }
                        $participant_batch->is_registration_fees_paid = true;
                        $participant_batch->save();
                    }
                }
            }
            elseif($paid_amount == $expected_amount){
                $participant_payment = new ParticipantPayment();
                    $participant_payment->participant_id = $participant->id;
                    $participant_payment->batch_id = $participant->batch_id;
                    $participant_payment->transaction_id = $request->transaction_id;
                    $participant_payment->amount = $batch->registration_fee;
                    $participant_payment->payment_mode = $request->payment_mode;
                    if($request->hasFile('payment_image')){
                        $file = $request->file('payment_image');
                        $filename = time().'-'.str_replace(' ','-',$file->getClientOriginalName());
                    $file->move(public_path('uploads/participant-payment'), $filename);
                    $participant_payment->payment_image = $filename;
                }
                $participant_payment->payment_for = 'registration';
                $participant_payment->save();

                if($batch->is_one_session_advance_payment){
                    $participant_payment = new ParticipantPayment();
                    $participant_payment->participant_id = $participant->id;
                    $participant_payment->batch_id = $participant->batch_id;
                    $participant_payment->transaction_id = $request->transaction_id;
                    $participant_payment->amount = $batch->fee_per_session;
                    $participant_payment->payment_mode = $request->payment_mode;
                    $participant_payment->payment_for = 'session_fee';
                    $participant_payment->session_number = 1;
                    $participant_payment->payment_image = $filename;
                    $participant_payment->save();
                    $data = [
                        'participant_id' => $participant->id,
                        'payment_id' => $participant_payment->id,
                        // Add any other data you need
                    ];
                    $jsonData = json_encode($data);
                    $qr_code_url = route('entry-user.scan-qr-code',$data);
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
                }
                $participant_batch = ParticipantBatch::where('participant_id', $participant->id)->where('batch_id', $participant->batch_id)->first();
                $participant->paid_amount += $batch->registration_fee;
                $participant->due_amount -= $batch->registration_fee;
                $participant->save();
                if($participant_batch){
                    if($participant_batch->batch_id == $participant->batch_id){

                        $participant->is_registration_fees_paid = true;
                        $participant->save();
                    }
                    $participant_batch->is_registration_fees_paid = true;
                    $participant_batch->save();
                }
                if($batch->is_one_session_advance_payment){
                    $participant->paid_amount += $batch->fee_per_session;
                    $participant->due_amount -= $batch->fee_per_session;
                    $participant->save();
                }
            }
            elseif($paid_amount < $expected_amount && $paid_amount > $batch->registration_fee){
                $registration_fees = $paid_amount - $batch->fee_per_session;
                $participant_payment = new ParticipantPayment();
                $participant_payment->participant_id = $participant->id;
                $participant_payment->batch_id = $participant->batch_id;
                $participant_payment->transaction_id = $request->transaction_id;
                $participant_payment->amount = $registration_fees;
                $participant_payment->payment_mode = $request->payment_mode;
                if($request->hasFile('payment_image')){
                    $file = $request->file('payment_image');
                    $filename = time().'-'.str_replace(' ','-',$file->getClientOriginalName());
                    $file->move(public_path('uploads/participant-payment'), $filename);
                    $participant_payment->payment_image = $filename;
                }
                $participant_payment->payment_for = 'registration';
                $participant_payment->save();

                if($batch->is_one_session_advance_payment){
                    $participant_payment = new ParticipantPayment();
                    $participant_payment->participant_id = $participant->id;
                    $participant_payment->batch_id = $participant->batch_id;
                    $participant_payment->transaction_id = $request->transaction_id;
                    $participant_payment->amount = $batch->fee_per_session;
                    $participant_payment->payment_mode = $request->payment_mode;
                    $participant_payment->payment_for = 'session_fee';
                    $participant_payment->session_number = 1;
                    $participant_payment->payment_image = $filename;
                    $participant_payment->save();
                    $data = [
                        'participant_id' => $participant->id,
                        'payment_id' => $participant_payment->id,
                        // Add any other data you need
                    ];
                    $jsonData = json_encode($data);
                    $qr_code_url = route('entry-user.scan-qr-code',$data);
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
                }
                $participant_batch = ParticipantBatch::where('participant_id', $participant->id)->where('batch_id', $participant->batch_id)->first();
                $participant->paid_amount += $registration_fees;
                $participant->due_amount -= $registration_fees;
                $participant->save();
                if($batch->is_one_session_advance_payment){
                    $participant->paid_amount += $batch->fee_per_session;
                    $participant->due_amount -= $batch->fee_per_session;
                    $participant->save();
                }
            }

            DB::commit();

            try {
                // $msgContent = "Dear " . $participant->first_name . " " . $participant->last_name . ",\n\n" .
                //               "Thank you for registering. We have received your registration payment of Rs. " . $paid_amount . ".\n" .
                //               "Welcome to the E3 Club! " .
                //               "Best regards,\nE3 Admin Team";
                               
              $msgContent = "Dear {$participant->first_name} {$participant->last_name},\n\n" .
              "Thank you for registering with E3!\n\n" .
              "We have successfully received your registration payment of ₹" . number_format($paid_amount) . ".\n\n" .
              "Welcome to E3!\n\n" .
              "Warm regards,\n" .
              "E3 Admin Team";
                               
                $this->trigger_whatsapp_message($participant->mobile, $msgContent);
            } catch (\Exception $ex) {
                \Log::error("Failed to send automatic WhatsApp message: " . $ex->getMessage());
            }

            return redirect()->route('master.participants.list')->with('success', 'Participant Registration Payment Added Successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function view_sessions($participant_id,$batch_id){
        $participant = Participant::findOrFail($participant_id);
        $batch = Batch::findOrFail($batch_id);
        if(!$participant->participantBatches->contains($batch_id)){
            return redirect()->back()->with('error', 'Participant Is Not In This Batch');
        }
        return view('master.participants.view-sessions', compact('participant','batch'));
    }

    public function view_assignments($participant_id,$batch_schedule_id){
        $participant = Participant::findOrFail($participant_id);
        $batch_schedule = BatchSchedule::findOrFail($batch_schedule_id);
        if(!$participant->participantBatches->contains($batch_schedule->batch_id)){
            return redirect()->back()->with('error', 'Participant Is Not In This Batch');
        }
        $assignmentSubmissions = $batch_schedule->assignmentSubmissions->where('participant_id', $participant_id);
        $total_out_of_marks = 300;
        foreach($batch_schedule->assignments as $assignment){
            $total_out_of_marks += $assignment->options->max('mark');
        }
        $total_marks_obtained = $assignmentSubmissions->sum('mark');

        $online_meetings = Meeting::where('batch_schedule_id', $batch_schedule_id)->where('type', 'online')->where('status', 'completed')->get();
        $online_meetings_attended = MeetingAttendance::whereIn('meeting_id', $online_meetings->pluck('id'))
        ->where('participant_id', $participant_id)
        ->where('status', 'present')
        ->count();

        $offline_meetings = Meeting::where('batch_schedule_id', $batch_schedule_id)->where('type', 'offline')->where('status', 'completed')->get();
        $offline_meetings_attended = MeetingAttendance::whereIn('meeting_id', $offline_meetings->pluck('id'))
        ->where('participant_id', $participant_id)
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
        if ($next_session) {
            $total_out_of_marks += 100;
            // Check if the participant has paid for the next session
            $payment = ParticipantPayment::where('participant_id', $participant_id)
                ->where('batch_id', $batch_schedule->batch_id)
                ->where('session_number', $next_session->session_number)
                ->first();

            // If participant is registered for the next session, add 100 marks
            if ($payment) {
                $total_marks_obtained += 100;
                $is_registered_for_next_session = true;
            }
        }
        $rating = ParticipantSessionRating::where('participant_id', $participant_id)->where('batch_id', $batch_schedule->batch_id)->where('batch_schedule_id', $batch_schedule_id)->
        where('rating_type','coach_to_participant')->first();
        if($rating){
            $total_marks_obtained += $rating->rating * 10;
        }
        return view('master.participants.view-assignments', compact('participant','batch_schedule','total_marks_obtained','total_out_of_marks','online_meetings','online_meetings_attended','offline_meetings',
        'offline_meetings_attended','online_meeting_attended_marks','offline_meeting_attended_marks','next_session','is_registered_for_next_session','rating'));
    }

    public function add_to_another_batch(Request $request,$id){
        $participant = Participant::findOrFail($id);
        $batch = Batch::findOrFail($request->batch_id);
        if(!$batch || !$batch->is_active || strtolower($batch->status) == 'completed'){
            return redirect()->back()->with('error', 'Batch Is Inactive Or Completed Or Does Not Exist');
        }
        if($participant->participantBatches->contains($request->batch_id)){
            return redirect()->back()->with('error', 'Participant Is Already In This Batch');
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
            DB::commit();
            return redirect()->route('master.participants.add-registration-payment',$participant_batch->id)->with('success', 'Participant Added To Batch Successfully');
        }
        catch(Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function import(Request $request){
        $validator = \Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,csv',
        ]);
        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return back()->withError($messages->first());
        }
        try{
            Excel::import(new ParticipantImport, request()->file('file'));

            return back()->withSuccess('Participants Imported Successfully');
        }
        catch(Exception $e){
            return back()->withError($e->getMessage());
        }
    }

    public function receive_payment(Request $request){
        $participant = Participant::findOrFail($request->participant_id);
        return view('master.participants.receive-payment', compact('participant'));
    }

    public function receive_registration_due_payment(Request $request){
        $validator = \Validator::make($request->all(), [
            'amount'=>'required|numeric',
            'registration_fees_due'=>'required|numeric',
            'registration_fees_paid'=>'required|numeric',
            'participant_id'=>'required|numeric',
            'batch_id'=>'required|numeric',
            'transaction_id'=>'required',
            'payment_mode'=>'required',
        ]);
        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $participant = Participant::findOrFail($request->participant_id);
        $batch = Batch::findOrFail($request->batch_id);
        try{
            DB::beginTransaction();
            $registration_fees_due = $request->registration_fees_due;
            $registration_fees_paid = $request->registration_fees_paid;
            $paid_amount = $request->amount + $registration_fees_paid;
            if($request->amount > $registration_fees_due){
                return redirect()->back()->with('error', 'Amount Cannot Be Greater Than Registration Fees Due Amount');
            }
            $participant_payment = new ParticipantPayment();
            $participant_payment->participant_id = $participant->id;
            $participant_payment->batch_id = $batch->id;
            $participant_payment->transaction_id = $request->transaction_id;
            $participant_payment->amount = $request->amount;
            $participant_payment->payment_mode = $request->payment_mode;
            if($request->hasFile('payment_image')){
                $file = $request->file('payment_image');
                $filename = time().'-'.str_replace(' ','-',$file->getClientOriginalName());
                $file->move(public_path('uploads/participant-payment'), $filename);
                $participant_payment->payment_image = $filename;
            }
            $participant_payment->payment_for = 'registration';
            $participant_payment->save();

            $participant->paid_amount += $paid_amount;
            $participant->due_amount -= $paid_amount;
            $participant->save();

            $participant_batch = ParticipantBatch::where('participant_id', $participant->id)->where('batch_id', $participant->batch_id)->first();
            if($paid_amount == $batch->registration_fee){
                if($participant_batch){
                    if($participant_batch->batch_id == $participant->batch_id){

                        $participant->is_registration_fees_paid = true;
                        $participant->save();
                    }
                    $participant_batch->is_registration_fees_paid = true;
                    $participant_batch->save();
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Registration Payment Received Successfully');
        }
        catch(Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function add_session_due_payment($participant_id,$batch_id){
        $participant = Participant::findOrFail($participant_id);
        $batch = Batch::findOrFail($batch_id);
        if(!$participant->participantBatches->contains($batch_id)){
            return redirect()->back()->with('error', 'Participant Is Not In This Batch');
        }
        return view('master.participants.add-session-due-payment', compact('participant','batch'));
    }

    public function store_session_due_payment(Request $request){
        $validator = \Validator::make($request->all(), [
           'participant_id'=>'required|numeric',
           'batch_id'=>'required|numeric',
           'amount'=>'required|numeric',
           'transaction_id'=>'required',
           'payment_mode'=>'required',
           'session_number.*'=>'required|numeric',
        ]);
        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $participant = Participant::findOrFail($request->participant_id);
        $batch = Batch::findOrFail($request->batch_id);
        try{
            DB::beginTransaction();
            foreach($request->session_number as $key => $session_number){
                $batch_schedule = BatchSchedule::where('batch_id', $batch->id)->where('session_number', $session_number)->first();
                if($batch_schedule){
                    $check = ParticipantPayment::where('participant_id', $participant->id)->where('batch_id', $batch->id)->where('session_number', $session_number)->first();
                    if(!$check){
                        $participant_payment = new ParticipantPayment();
                        $participant_payment->participant_id = $participant->id;
                        $participant_payment->batch_id = $participant->batch_id;
                        $participant_payment->transaction_id = $request->transaction_id;
                        $participant_payment->amount = $batch_schedule->amount;
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
                        $jsonData = json_encode($data);
                        $qr_code_url = route('entry-user.scan-qr-code',$data);
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

                        $participant->paid_amount += $batch_schedule->amount;
                        $participant->due_amount -= $batch_schedule->amount;
                        $participant->save();
                    }
                }
            }
            DB::commit();
            return redirect()->route('master.participants.receive-payment', ['participant_id'=>$participant->id])->with('success', 'Session Due Payment Received Successfully');
        }
        catch(Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function send_whatsapp_message(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'participant_id' => 'required|numeric',
            'batch_id'       => 'required|numeric',
            'mobile'         => 'required|string',
            'message'        => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $sent = $this->trigger_whatsapp_message($request->mobile, $request->message);
        if ($sent) {
            return redirect()->back()->with('success', 'WhatsApp message sent successfully to ' . $request->mobile);
        }
        return redirect()->back()->with('error', 'WhatsApp send failed. Please check device status.');
    }

    private function trigger_whatsapp_message($mobile, $message)
    {
        try {
            $mobile = preg_replace('/[^0-9]/', '', $mobile);
            // Prepend country code if 10-digit (India +91)
            if (strlen($mobile) === 10) {
                $mobile = '91' . $mobile;
            }

            $apiUrl   = env('WHATSAPP_API_URL', 'http://api.dotphi.com/wapp/v2/api/send');
            $apiToken = env('WHATSAPP_API_TOKEN', '');

            $response = Http::get($apiUrl, [
                'apikey' => $apiToken,
                'mobile' => $mobile,
                'msg'    => $message,
            ]);

            if ($response->successful()) {
                $body = trim($response->body());
                $result = $response->json();

                if (is_array($result)) {
                    $status = $result['status'] ?? $result['code'] ?? null;
                    $msg = $result['message'] ?? $result['msg'] ?? '';
                    
                    if ($status == 200 || 
                        $status === true || 
                        $status === 'success' || 
                        (is_string($status) && stripos($status, 'success') !== false) ||
                        stripos($msg, 'success') !== false ||
                        stripos($msg, 'submitted') !== false ||
                        stripos($msg, 'sent') !== false) {
                        return true;
                    }
                } else {
                    if ($body === "200" || 
                        stripos($body, "success") !== false || 
                        stripos($body, "submitted") !== false ||
                        stripos($body, "sent") !== false) {
                        return true;
                    }
                }
            }
            return false;
        } catch (\Exception $e) {
            \Log::error("Failed to send WhatsApp automated message: " . $e->getMessage());
            return false;
        }
    }

    public function download_invoice($payment_id)
    {
        $payment = ParticipantPayment::with(['participant', 'batch'])->findOrFail($payment_id);
        return view('master.participants.invoice', compact('payment'));
    }
}
