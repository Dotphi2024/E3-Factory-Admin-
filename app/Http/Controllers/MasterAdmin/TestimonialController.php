<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Testimonial;
use Auth;

class TestimonialController extends Controller
{
    public function list(){
        $testimonials = Testimonial::orderBy('id', 'desc')->get();
        return view('master.testimonial.list', compact('testimonials'));
    }
    public function add(){
        return view('master.testimonial.create');
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'required_without:video_url',
            'video_url' => 'required_without:image',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $testimonial = new Testimonial();
        $testimonial->title = $request->title;
        $testimonial->description = $request->description;
        $testimonial->video_url = $request->video_url;
        $testimonial->added_by = Auth::user()->id;
        if($request->hasFile('image')){
            $file = $request->file('image');
            $filename = time() . '-' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move(public_path('uploads/testimonials'), $filename);
            $testimonial->image = $filename;
        }
        $testimonial->save();
        return redirect()->route('master.testimonials.list')->with('success', 'Testimonial Added Successfully');
    }
    public function edit($id){
        $testimonial = Testimonial::findOrFail($id);
        return view('master.testimonial.edit', compact('testimonial'));
    }
    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->title = $request->title;
        $testimonial->description = $request->description;
        $testimonial->video_url = $request->video_url;
        if($request->hasFile('image')){
            if($testimonial->image && file_exists(public_path('uploads/testimonials/'.$testimonial->image))){
                unlink(public_path('uploads/testimonials/'.$testimonial->image));
            }
            $file = $request->file('image');
            $filename = time() . '-' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move(public_path('uploads/testimonials'), $filename);
            $testimonial->image = $filename;
        }
        $testimonial->save();
        return redirect()->route('master.testimonials.list')->with('success', 'Testimonial Updated Successfully');
    }

    public function delete($id){
        $testimonial = Testimonial::findOrFail($id);
        if($testimonial->image && file_exists(public_path('uploads/testimonials/'.$testimonial->image))){
            unlink(public_path('uploads/testimonials/'.$testimonial->image));
        }
        $testimonial->delete();
        return redirect()->route('master.testimonials.list')->with('success', 'Testimonial Deleted Successfully');
    }

    public function update_active_status($id){
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->is_active = !$testimonial->is_active;
        $testimonial->save();
        return redirect()->route('master.testimonials.list')->with('success', 'Status Updated Successfully');
    }
}
