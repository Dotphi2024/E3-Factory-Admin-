<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PhotoGallery;
use App\Models\Batch;
use Auth;
use Intervention\Image\Facades\Image;

class PhotoGalleryController extends Controller
{
    public function list(){
        $photo_galleries = PhotoGallery::orderBy('id', 'desc')->get();
        return view('master.photo-gallery.list', compact('photo_galleries'));
    }
    public function add(){
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return view('master.photo-gallery.create', compact('batches'));
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'title'=> 'required',
            'image' => 'required|mimes:png,jpg,jpeg|image',
            'visibility' => 'required',
            'batch_id' => 'required_if:visibility,Batch',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $photo_gallery = new PhotoGallery();
        $photo_gallery->title = $request->title;
        $photo_gallery->description = $request->description;
        $photo_gallery->visibility = $request->visibility;
        if($request->visibility == 'Batch'){
            $photo_gallery->batch_id = $request->batch_id;
        }
        $photo_gallery->added_by = Auth::user()->id;
        $photo_gallery->is_active = 1;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '-' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move(public_path('uploads/photo-galleries'), $filename);

            // Get the image dimensions
            // $image = Image::make(public_path('uploads/photo-galleries/'.$filename));
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

            // $image->save(public_path('uploads/photo-galleries/'.$filename));

            // Assuming $photo_gallery is an instance of your model
            $photo_gallery->image = $filename;
        }
        $photo_gallery->save();
        return redirect()->route('master.photo-gallery.list')->with('success', 'Photo Gallery Added Successfully');
    }

    public function edit($id){
        $photo_gallery = PhotoGallery::findOrFail($id);
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return view('master.photo-gallery.edit', compact('photo_gallery', 'batches'));
    }
    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'title'=> 'required',
            'image' => 'mimes:png,jpg,jpeg|image',
            'visibility' => 'required',
            'batch_id' => 'required_if:visibility,Batch',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        $photo_gallery = PhotoGallery::findOrFail($id);
        $photo_gallery->title = $request->title;
        $photo_gallery->description = $request->description;
        $photo_gallery->visibility = $request->visibility;
        if($request->visibility == 'Batch'){
            $photo_gallery->batch_id = $request->batch_id;
        }
        if($request->hasFile('image')){
            if($photo_gallery->image && file_exists(public_path('uploads/photo-galleries/'.$photo_gallery->image))){
                unlink(public_path('uploads/photo-galleries/'.$photo_gallery->image));
            }
            $file = $request->file('image');
            $filename = time() . '-' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move(public_path('uploads/photo-galleries'), $filename);

            // Get the image dimensions
            // $image = Image::make(public_path('uploads/photo-galleries/'.$filename));
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

            // $image->save(public_path('uploads/photo-galleries/'.$filename));

            // Assuming $photo_gallery is an instance of your model
            $photo_gallery->image = $filename;
        }
        $photo_gallery->save();
        return redirect()->route('master.photo-gallery.list')->with('success', 'Photo Gallery Updated Successfully');
    }

    public function delete($id){
        $photo_gallery = PhotoGallery::findOrFail($id);
        if($photo_gallery->image && file_exists(public_path('uploads/photo-galleries/'.$photo_gallery->image))){
            unlink(public_path('uploads/photo-galleries/'.$photo_gallery->image));
        }
        $photo_gallery->delete();
        return redirect()->back()->with('success', 'Photo Gallery Deleted Successfully');
    }
    public function update_active_status($id){
        $photo_gallery = PhotoGallery::findOrFail($id);
        $photo_gallery->is_active = !$photo_gallery->is_active;
        $photo_gallery->save();
        return redirect()->back()->with('success', 'Status Updated Successfully');
    }
}
