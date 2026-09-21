<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Batch;
use App\Models\Coach;
use Auth;

class CoachController extends Controller
{
    public function list(){
        $participants = Participant::orderBy('id', 'desc')->where('is_coach',1)->get();
        $batches = Batch::active()->where('start_coach_registration',1)->where('status', '!=','completed')->orderBy('id', 'desc')->get();
        return view('master.coach.list', compact('participants','batches'));
    }
    public function assign_batch(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
            'participant_id' => 'required',
        ], [
            'participant_id.required' => 'Participant is required',
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $check = Coach::where('batch_id', $request->batch_id)->where('participant_id', $request->participant_id)->first();
        if($check){
            return redirect()->back()->with('error', 'Already Assigned')->withInput();
        }
        $participant = Participant::findOrFail($request->participant_id);

        $coach = new Coach();
        $coach->batch_id = $request->batch_id;
        $coach->participant_id = $request->participant_id;
        $coach->added_by = Auth::user()->id;
        $coach->status = 'approved';
        $coach->save();

        $participant->is_coach = 1;
        $participant->save();
        return redirect()->back()->with('success', 'Batch Assigned Successfully');
    }
    public function add(){
        $participants = Participant::whereHas('batch',function($query){
            $query->where('status','completed');
        })->where('is_coach','0')->orderBy('first_name', 'ASC')->get();
        return view('master.coach.create', compact('participants'));
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [

            'participant_id' => 'required',
        ], [
            'participant_id.required' => 'Participant is required',

        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $participant = Participant::findOrFail($request->participant_id);

        $participant->is_coach = 1;
        $participant->save();
        return redirect()->route('master.coaches.list')->with('success', 'Coach Added Successfully');
    }
    public function edit($id){
        $coach = Coach::findOrFail($id);
        $batches = Batch::active()->where('start_coach_registration',1)->where('status', '!=','completed')->orderBy('id', 'desc')->get();
        $participants = Participant::whereHas('batch',function($query){
            $query->where('status','completed');
        })->orderBy('id', 'desc')->get();
        return view('master.coach.edit', compact('coach','batches','participants'));
    }
    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
            'participant_id' => 'required',
        ], [
            'participant_id.required' => 'Participant is required',
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $check = Coach::where('batch_id', $request->batch_id)->where('participant_id', $request->participant_id)->first();
        if($check){
            return redirect()->back()->with('error', 'Coach Already Exist');
        }
        $coach = Coach::findOrFail($id);
        $coach->batch_id = $request->batch_id;
        $coach->participant_id = $request->participant_id;
        $coach->save();
        return redirect()->route('master.coaches.list')->with('success', 'Coach Updated Successfully');
    }
    public function delete($id){
        $coach = Coach::findOrFail($id);
        $coach->delete();
        return redirect()->route('master.coaches.list')->with('success', 'Coach Deleted Successfully');
    }

    public function coach_registration_requests(){
        $coaches = Coach::orderBy('id', 'desc')->get();
        return view('master.coach.coach-registration-requests', compact('coaches'));
    }
    public function update_registration_request_status(Request $request){
        $coach = Coach::findOrFail($request->id);
        $coach->status = $request->status;
        $coach->save();
        if($request->status == 'approved'){
            $participant = Participant::findOrFail($coach->participant_id);
            $participant->is_coach = 1;
            $participant->save();
        }
        return redirect()->back()->with('success', 'Status Updated Successfully');
    }
}
