<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Auth;

class CourseController extends Controller
{
    public function list(){
        $courses = Course::orderBy('id', 'desc')->get();
        return view('master.course.list', compact('courses'));
    }
    public function add(){
        return view('master.course.create');
    }
    public function store(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'duration' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $course = new Course();
        $course->name = $request->name;
        $course->duration = $request->duration;
        $course->added_by = Auth::user()->id;
        $course->save();

        return redirect()->route('master.courses.list')->with('success', 'Course Added Successfully');
    }
    public function edit($id){
        $course = Course::findOrFail($id);
        return view('master.course.edit', compact('course'));
    }
    public function update(Request $request, $id){
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'duration' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $course = Course::findOrFail($id);
        $course->name = $request->name;
        $course->duration = $request->duration;
        $course->save();
        return redirect()->route('master.courses.list')->with('success', 'Course Updated Successfully');
    }

    public function delete($id){
        $course = Course::findOrFail($id);
        $course->delete();
        return redirect()->route('master.courses.list')->with('success', 'Course Deleted Successfully');
    }
}
