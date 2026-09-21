<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Recommendation;
use App\Models\FollowUp;

class RecommendationController extends Controller
{
    public function list(){
        $recommendations = Recommendation::with('followUps')->orderBy('id','desc');
        if(request()->status == 'Pending' || request()->status == 'Completed' || request()->status == 'Failed' || request()->status == 'InProgress'){
            $recommendations = $recommendations->where('follow_up',request()->status);
        }
        $recommendations = $recommendations->get();
        return view('master.recommendation.list',compact('recommendations'));
    }

    public function follow_up_list(){
        $follow_ups = FollowUp::orderBy('id','desc');
        if(request()->has('status') && (request()->status == 0 || request()->status == 1)){
            $follow_ups = $follow_ups->where('is_follow_up_completed',request()->status);
        }
        $follow_ups = $follow_ups->get();
        return view('master.recommendation.follow-up-list',compact('follow_ups'));
    }

    public function add(){
        return view('master.recommendation.add');
    }

    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'mobile'=> 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $recommendation = new Recommendation();
        $recommendation->participant_id = 0;
        $recommendation->name = $request->name;
        $recommendation->mobile = $request->mobile;
        $recommendation->save();
        return redirect()->route('master.recommendations.list','status=Pending')->with('success','Lead added successfully');
    }

    public function update_follow_up_status(Request $request,$id){
        $recommendation = Recommendation::find($id);
        $recommendation->follow_up = $request->status;
        $recommendation->save();
        return redirect()->back()->with('success','Follow up status updated successfully');
    }
    public function add_follow_up(Request $request){
        $validator = \Validator::make($request->all(), [
            'recommendation_id' => 'required',
            'date' => 'required',
            'time'=> 'required',
            'log'=> 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $follow_up = new FollowUp();
        $follow_up->recommendation_id = $request->recommendation_id;
        $follow_up->date = $request->date;
        $follow_up->time = $request->time;
        $follow_up->log = $request->log;
        $follow_up->added_by = auth()->user()->id;
        $follow_up->save();
        return redirect()->back()->with('success','Follow up added successfully');
    }

    public function mark_follow_up_complete(Request $request,$id){
        $follow_up = FollowUp::findOrFail($id);
        $follow_up->is_follow_up_completed = 1;
        $follow_up->completed_follow_up_log = $request->completed_follow_up_log;
        $follow_up->save();
        return redirect()->back()->with('success','Follow up Completed successfully');
    }
}
