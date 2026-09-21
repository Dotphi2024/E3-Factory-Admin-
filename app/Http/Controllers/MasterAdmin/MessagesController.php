<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GroupMessage;
use App\Models\Batch;
use App\Models\BatchGroup;
use App\Models\BatchGroupParticipant;
use App\Models\Participant;
use App\Services\FcmNotificationService;

class MessagesController extends Controller
{
    protected $fcmNotificationService;
    public function __construct(FcmNotificationService $fcmNotificationService)
    {
        $this->fcmNotificationService = $fcmNotificationService;
    }
    public function batch_messages(){
        $group_messages = GroupMessage::whereNull('batch_group_id')
        ->whereNull('participant_id')
        ->withTrashed()
        ->orderBy('id', 'desc')
        ->get();
        $batches = Batch::orderBy('id', 'desc')->get();
        return view('master.messages.batch-message-list',compact('group_messages','batches'));
    }
    public function store_batch_messages(Request $request){
        $validator = \Validator::make($request->all(), [
            'message' => 'required',
            'description' => 'required',
            'batch_id' => 'required',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $batch = Batch::with('coaches')->findOrFail($request->batch_id);
        if($batch->coaches->count() == 0){
            return redirect()->back()->with('error', 'No head coach found in this batch')->withInput();
        }
        $group_messages = new GroupMessage();
        $group_messages->message = $request->message;
        $group_messages->batch_id = $request->batch_id;
        $group_messages->description = $request->description. '- Message Send by Admin';
        $group_messages->coach_id = $batch->coaches->first()->id;
        $group_messages->save();

        $device_tokens = $batch->batchParticipants()->whereNotNull('device_token')->pluck('device_token')->toArray();
        $this->fcmNotificationService->sendNotification($device_tokens, 'New Message in Batch', 'You have a new Message in batch.','batch_message', $group_messages);
        return redirect()->back()->with('success', 'Message sent to batch successfully');
    }

    public function group_messages(){
        $group_messages = GroupMessage::whereNotNull('batch_group_id')
        ->whereNull('participant_id')
        ->withTrashed()
        ->orderBy('id', 'desc')
        ->get();
        $batch_groups = BatchGroup::orderBy('id', 'desc')->get();
        return view('master.messages.group-message-list',compact('group_messages','batch_groups'));
    }

    public function store_group_messages(Request $request){
        $validator = \Validator::make($request->all(), [
            'message' => 'required',
            'description' => 'required',
            'group_id' => 'required',
        ], [
            'group_id.required' => 'Batch group is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $batch_group = BatchGroup::with('coach')->findOrFail($request->group_id);
        if(!$batch_group->coach){
            return redirect()->back()->with('error', 'No coach found in this group')->withInput();
        }
        $group_messages = new GroupMessage();
        $group_messages->message = $request->message;
        $group_messages->batch_id = $batch_group->batch_id;
        $group_messages->batch_group_id = $request->group_id;
        $group_messages->description = $request->description. ' - Message Send by Admin';
        $group_messages->coach_id = $batch_group->coach_id;
        $group_messages->save();

        $device_tokens = $batch_group->batchGroupParticipants1()->whereNotNull('device_token')->pluck('device_token')->toArray();
        $this->fcmNotificationService->sendNotification($device_tokens, 'New Message in Group', 'You have a new Message in group.','group_message', $group_messages);
        return redirect()->back()->with('success', 'Message sent to group successfully');
    }

    public function participant_messages(){
        $group_messages = GroupMessage::whereNotNull('participant_id')
        ->withTrashed()
        ->orderBy('id', 'desc')
        ->get();
        $participants = Participant::orderBy('id', 'desc')->get();
        return view('master.messages.participant-message-list',compact('group_messages','participants'));
    }

    public function store_participant_messages(Request $request){
        $validator = \Validator::make($request->all(), [
            'message' => 'required',
            'description' => 'required',
            'participant_id' => 'required',
        ], [
            'participant_id.required' => 'Participant is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $participant = Participant::findOrFail($request->participant_id);
        $getGroup = BatchGroupParticipant::where('participant_id', $participant->id)->first();
        if(!$getGroup || !$getGroup->batch_group || !$getGroup->batch_group->coach ){
            return redirect()->back()->with('error', 'No group found for this participant')->withInput();
        }

        $group_messages = new GroupMessage();
        $group_messages->message = $request->message;
        $group_messages->batch_id = $participant->batch_id;
        $group_messages->participant_id = $participant->id;
        $group_messages->coach_id = $getGroup->batch_group->coach_id;
        $group_messages->description = $request->description. ' - Message Send by Admin';
        $group_messages->save();

        $device_tokens = [$participant->device_token];
        $this->fcmNotificationService->sendNotification($device_tokens, 'New Message', 'You have a new Message.','participant_message', $group_messages);
        return redirect()->back()->with('success', 'Message sent to participant successfully');
    }

    public function delete_messages($id){
        $group_messages = GroupMessage::find($id);
        $group_messages->delete();
        return redirect()->back()->with('success', 'Message Deleted Successfully');
    }
}
