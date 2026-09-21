<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Participant;
use App\Models\Course;
use App\Models\User;

class DashboardController extends Controller
{
    public function dashboard(){
        $batches = Batch::all();
        $participants = Participant::all();
        $courses = Course::all();
        $users = User::all();
        return view('master.dashboard', compact('batches', 'participants', 'courses', 'users'));
    }
}
