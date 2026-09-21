<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\NoticeAndAnnouncement;
use Auth;
use App\Services\FcmNotificationService;

class NoticeAndAnnouncementController extends Controller
{
    protected $fcmNotificationService;

    public function __construct(FcmNotificationService $fcmNotificationService)
    {
        $this->fcmNotificationService = $fcmNotificationService;
    }
    public function list(){
        $notice_and_announcements = NoticeAndAnnouncement::orderBy('id', 'desc')->get();
        return view('master.notice-and-announcement.list', compact('notice_and_announcements'));
    }
    public function add(){
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return view('master.notice-and-announcement.create', compact('batches'));
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'title'=> 'required',
            'image' => 'required|mimes:png,jpg,jpeg|image',
            'visibility' => 'required',
            'type'=>'required',
            'batch_id' => 'required_if:visibility,Batch',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $notice_and_announcement = new NoticeAndAnnouncement();
        $notice_and_announcement->title = $request->title;
        $notice_and_announcement->description = $request->description;
        $notice_and_announcement->visibility = $request->visibility;
        $notice_and_announcement->type = $request->type;
        if($request->visibility == 'Batch'){
            $notice_and_announcement->batch_id = $request->batch_id;
        }
        $notice_and_announcement->added_by = Auth::user()->id;
        $notice_and_announcement->is_active = 1;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '-' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move(public_path('uploads/notice-and-announcement'), $filename);

            // Get the image dimensions
            // $image = Image::make(public_path('uploads/notice_and_announcement/'.$filename));
            // $width = $image->width();
            // $height = $image->height();

            // // Desired dimensions
            // $desiredWidth = 473;
            // $desiredHeight = 451;

            // // Check if the image dimensions match the desired size
            // if ($width != $desiredWidth || $height != $desiredHeight) {
            //     // Resize the image to the desired dimensions
            //     $image->resize($desiredWidth, $desiredHeight);
            // }

            // // Save the resized image

            // $image->save(public_path('uploads/notice_and_announcement/'.$filename));

            // Assuming $notice_and_announcement is an instance of your model
            $notice_and_announcement->image = $filename;
        }
        $notice_and_announcement->save();
        if($request->visibility == 'Batch'){
            $batch = Batch::find($request->batch_id);
            $device_tokens = $batch?->batchParticipants()->whereNotNull('device_token')->pluck('device_token')->toArray();
            $this->fcmNotificationService->sendNotification($device_tokens, $notice_and_announcement->type, 'New '.ucfirst($request->type).' Added', $notice_and_announcement->type, $notice_and_announcement);
        }
        return redirect()->route('master.notice-and-announcement.list')->with('success', ucfirst($request->type).' Added Successfully');
    }

    public function edit($id){
        $notice_and_announcement = NoticeAndAnnouncement::findOrFail($id);
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return view('master.notice-and-announcement.edit', compact('notice_and_announcement', 'batches'));
    }
    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'title'=> 'required',
            'image' => 'mimes:png,jpg,jpeg|image',
            'visibility' => 'required',
            'type'=>'required',
            'batch_id' => 'required_if:visibility,Batch',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $notice_and_announcement = NoticeAndAnnouncement::findOrFail($id);
        $notice_and_announcement->title = $request->title;
        $notice_and_announcement->description = $request->description;
        $notice_and_announcement->visibility = $request->visibility;
        $notice_and_announcement->type = $request->type;
        if($request->visibility == 'Batch'){
            $notice_and_announcement->batch_id = $request->batch_id;
        }
        if($request->hasFile('image')){
            if($notice_and_announcement->image && file_exists(public_path('uploads/notice-and-announcements/'.$notice_and_announcement->image))){
                unlink(public_path('uploads/notice-and-announcements/'.$notice_and_announcement->image));
            }
            $file = $request->file('image');
            $filename = time() . '-' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move(public_path('uploads/notice-and-announcement'), $filename);

            // Get the image dimensions
            // $image = Image::make(public_path('uploads/notice_and_announcement/'.$filename));
            // $width = $image->width();
            // $height = $image->height();

            // // Desired dimensions
            // $desiredWidth = 473;
            // $desiredHeight = 451;

            // // Check if the image dimensions match the desired size
            // if ($width != $desiredWidth || $height != $desiredHeight) {
            //     // Resize the image to the desired dimensions
            //     $image->resize($desiredWidth, $desiredHeight);
            // }

            // // Save the resized image

            // $image->save(public_path('uploads/notice_and_announcement/'.$filename));

            // Assuming $notice_and_announcement is an instance of your model
            $notice_and_announcement->image = $filename;
        }
        $notice_and_announcement->save();
        return redirect()->route('master.notice-and-announcement.list')->with('success', ucfirst($request->type).' Updated Successfully');
    }

    public function delete($id){
        $notice_and_announcement = NoticeAndAnnouncement::findOrFail($id);
        if($notice_and_announcement->image && file_exists(public_path('uploads/notice-and-announcement/'.$notice_and_announcement->image))){
            unlink(public_path('uploads/notice-and-announcement/'.$notice_and_announcement->image));
        }
        $notice_and_announcement->delete();
        return redirect()->back()->with('success', 'Deleted Successfully');
    }
    public function update_active_status($id){
        $notice_and_announcement = NoticeAndAnnouncement::findOrFail($id);
        $notice_and_announcement->is_active = !$notice_and_announcement->is_active;
        $notice_and_announcement->save();
        return redirect()->back()->with('success', 'Status Updated Successfully');
    }
}
