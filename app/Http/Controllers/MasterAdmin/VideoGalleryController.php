<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VideoGallery;
use App\Models\Batch;
use Auth;

class VideoGalleryController extends Controller
{
    public function list(){
        $video_galleries = VideoGallery::where('type','video')->orderBy('id', 'desc')->get();
        return view('master.video-gallery.list', compact('video_galleries'));
    }
    public function add(){
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return view('master.video-gallery.create', compact('batches'));
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'title'=> 'required',
            'video_url' => 'required|url',
            'visibility' => 'required',
            'batch_id' => 'required_if:visibility,Batch',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $video_gallery = new VideoGallery();
        $video_gallery->title = $request->title;
        $video_gallery->description = $request->description;
        $video_gallery->video_url = $request->video_url;
        $video_gallery->visibility = $request->visibility;
        if($request->visibility == 'Batch'){
            $video_gallery->batch_id = $request->batch_id;
        }
        $video_gallery->added_by = Auth::user()->id;
        $video_gallery->is_active = 1;
        $video_gallery->save();
        return redirect()->route('master.video-gallery.list')->with('success', 'Video Gallery Added Successfully');
    }
    public function edit($id){
        $video_gallery = VideoGallery::where('type','video')->findOrFail($id);
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return view('master.video-gallery.edit', compact('video_gallery', 'batches'));
    }

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'title'=> 'required',
            'video_url' => 'required|url',
            'visibility' => 'required',
            'batch_id' => 'required_if:visibility,Batch',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $video_gallery = VideoGallery::where('type','video')->findOrFail($id);
        $video_gallery->title = $request->title;
        $video_gallery->description = $request->description;
        $video_gallery->video_url = $request->video_url;
        $video_gallery->visibility = $request->visibility;
        if($request->visibility == 'Batch'){
            $video_gallery->batch_id = $request->batch_id;
        }
        $video_gallery->save();
        return redirect()->route('master.video-gallery.list')->with('success', 'Video Gallery Updated Successfully');
    }

    public function delete($id){
        $video_gallery = VideoGallery::where('type','video')->findOrFail($id);
        $video_gallery->delete();
        return redirect()->back()->with('success', 'Video Gallery Deleted Successfully');
    }
    public function update_active_status($id){
        $video_gallery = VideoGallery::where('type','video')->findOrFail($id);
        $video_gallery->is_active = !$video_gallery->is_active;
        $video_gallery->save();
        return redirect()->back()->with('success', 'Video Gallery Status Updated Successfully');
    }


    public function list_e3_talk(){
        $video_galleries = VideoGallery::where('type','e3-talk')->orderBy('id', 'desc')->get();
        return view('master.e3-talk.list', compact('video_galleries'));
    }
    public function add_e3_talk(){
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return view('master.e3-talk.create', compact('batches'));
    }
    public function store_e3_talk(Request $request){
        $validator = \Validator::make($request->all(), [
            'title'=> 'required',
            'video_url' => 'required|url',
            'visibility' => 'required',
            'batch_id' => 'required_if:visibility,Batch',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $video_gallery = new VideoGallery();
        $video_gallery->title = $request->title;
        $video_gallery->description = $request->description;
        $video_gallery->video_url = $request->video_url;
        $video_gallery->visibility = $request->visibility;
        $video_gallery->type = 'e3-talk';
        if($request->visibility == 'Batch'){
            $video_gallery->batch_id = $request->batch_id;
        }
        $video_gallery->added_by = Auth::user()->id;
        $video_gallery->is_active = 1;
        $video_gallery->save();
        return redirect()->route('master.e3-talk.list')->with('success', 'Data Added Successfully');
    }
    public function edit_e3_talk($id){
        $video_gallery = VideoGallery::where('type','e3-talk')->findOrFail($id);
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return view('master.e3-talk.edit', compact('video_gallery', 'batches'));
    }

    public function update_e3_talk(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'title'=> 'required',
            'video_url' => 'required|url',
            'visibility' => 'required',
            'batch_id' => 'required_if:visibility,Batch',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $video_gallery = VideoGallery::where('type','e3-talk')->findOrFail($id);
        $video_gallery->title = $request->title;
        $video_gallery->description = $request->description;
        $video_gallery->video_url = $request->video_url;
        $video_gallery->visibility = $request->visibility;
        if($request->visibility == 'Batch'){
            $video_gallery->batch_id = $request->batch_id;
        }
        $video_gallery->save();
        return redirect()->route('master.e3-talk.list')->with('success', 'Data Updated Successfully');
    }

    public function delete_e3_talk($id){
        $video_gallery = VideoGallery::where('type','e3-talk')->findOrFail($id);
        $video_gallery->delete();
        return redirect()->back()->with('success', 'Data Deleted Successfully');
    }
    public function update_e3_talk_active_status($id){
        $video_gallery = VideoGallery::where('type','e3-talk')->findOrFail($id);
        $video_gallery->is_active = !$video_gallery->is_active;
        $video_gallery->save();
        return redirect()->back()->with('success', 'Status Updated Successfully');
    }
}
