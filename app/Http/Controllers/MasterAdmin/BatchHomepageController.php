<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GuestHomePage;
use App\Models\Batch;
use Auth;

class BatchHomepageController extends Controller
{
    public function list(){
        $guest_homepage_data = GuestHomePage::where('type', 'batch')->orderBy('id', 'desc')->get();
        return view('master.batch-homepage.list', compact('guest_homepage_data'));
    }

    public function add(){
        $batches = Batch::orderBy('id', 'desc')->get();
        return view('master.batch-homepage.create', compact('batches'));
    }

    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id'=>'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        if(!$request->title && !$request->description && !$request->image && !$request->url && !$request->video_url){
            return redirect()->back()->with('error', 'Please enter at least one field');
        }
        $batch = Batch::findOrFail($request->batch_id);
        $guest_homepage = new GuestHomePage();
        $guest_homepage->title = $request->title;
        $guest_homepage->description = $request->description;
        $guest_homepage->url = $request->url;
        $guest_homepage->video_url = $request->video_url;
        $guest_homepage->batch_id = $batch->id;
        $guest_homepage->type = 'batch';
        $guest_homepage->added_by = Auth::user()->id;
        $guest_homepage->sequence = $request->sequence ?? 0;

        if($request->hasFile('image')){
            $file = $request->file('image');
            $filename = time() . '.' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move('uploads/guest-homepage/', $filename);
            $guest_homepage->image = $filename;
        }
        $guest_homepage->save();
        return redirect()->route('master.batch-homepage.list')->with('success', 'Guest Homepage added successfully');
    }

    public function edit($id){
        $guest_home_page = GuestHomePage::where('type', 'batch')->findOrFail($id);
        $batches = Batch::orderBy('id', 'desc')->get();
        return view('master.batch-homepage.edit', compact('guest_home_page','batches'));
    }

    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'batch_id'=>'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        if(!$request->title && !$request->description && !$request->image && !$request->url && !$request->video_url){
            return redirect()->back()->with('error', 'Please enter at least one field');
        }
        $batch = Batch::findOrFail($request->batch_id);
        $guest_homepage = GuestHomePage::where('type', 'batch')->findOrFail($id);
        $guest_homepage->title = $request->title;
        $guest_homepage->description = $request->description;
        $guest_homepage->url = $request->url;
        $guest_homepage->video_url = $request->video_url;
        $guest_homepage->batch_id = $batch->id;
        $guest_homepage->added_by = Auth::user()->id;
        $guest_homepage->sequence = $request->sequence ?? 0;
        if($request->hasFile('image')){
            if($guest_homepage->image && file_exists(public_path('uploads/guest-homepage/'.$guest_homepage->image))){
                unlink(public_path('uploads/guest-homepage/'.$guest_homepage->image));
            }
            $file = $request->file('image');
            $filename = time() . '.' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move('uploads/guest-homepage/', $filename);
            $guest_homepage->image = $filename;
        }
        $guest_homepage->save();
        return redirect()->route('master.batch-homepage.list')->with('success', 'Guest Homepage updated successfully');
    }

    public function delete($id){
        $guest_homepage = GuestHomePage::where('type', 'batch')->findOrFail($id);
        if($guest_homepage->image && file_exists(public_path('uploads/guest-homepage/'.$guest_homepage->image))){
            unlink(public_path('uploads/guest-homepage/'.$guest_homepage->image));
        }
        $guest_homepage->delete();
        return redirect()->back()->with('success', 'Guest Homepage deleted successfully');
    }

    public function update_active_status($id){
        $guest_homepage = GuestHomePage::where('type', 'batch')->findOrFail($id);
        $guest_homepage->is_active = !$guest_homepage->is_active;
        $guest_homepage->save();
        return redirect()->back()->with('success', 'Status Updated Successfully');
    }
}
