<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BatchSchedule;
use App\Models\ParticipantPayment;
use App\Models\Participant;
use Auth;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntryUserController extends Controller
{
    public function entryUserDashboard(){
        $this->checkAuth();
        $batch_schedules = BatchSchedule::where('is_session_completed',2)->where('date','=',date('Y-m-d'))->get();
        return view('master.entry-user-dashboard', compact('batch_schedules'));
    }

    public function sessionDetails($id){
        $this->checkAuth();
        $batch_schedule = BatchSchedule::where('is_session_completed',2)->where('date','=',date('Y-m-d'))->findOrFail($id);
        $participants_reached = ParticipantPayment::where('batch_id',$batch_schedule->batch_id)->where('session_number',$batch_schedule->session_number)->where('is_qr_used',1)->count();
        return view('master.entry-user-session-details', compact('batch_schedule','participants_reached'));
    }

    public function scanQrCode(Request $request){
        $this->checkAuth();
        $payment_id = $request->payment_id;
        $participant_id = $request->participant_id;
        $participant_payment = ParticipantPayment::where('participant_id',$participant_id)->findOrFail($payment_id);
        $participant = Participant::findOrFail($participant_id);
        $batch_schedule = BatchSchedule::where('batch_id', $participant_payment->batch_id)->where('session_number', $participant_payment->session_number)->first();
        return view('master.scan-qr-code', compact('participant','participant_payment','batch_schedule'));
    }

    public function enterParticipant(Request $request){
        $payment_id = $request->payment_id;
        $participant_id = $request->participant_id;
        $participant_payment = ParticipantPayment::where('participant_id',$participant_id)->findOrFail($payment_id);
        $participant = Participant::findOrFail($participant_id);
        $batch_schedule = BatchSchedule::where('batch_id', $participant_payment->batch_id)->where('session_number', $participant_payment->session_number)->first();
        // Check if batch schedule exists
        if (!$batch_schedule) {
            return back()->with('error', 'No Session Found');
        }

        // Conditions
        if ($participant_payment->is_qr_used) {
            return back()->with('error', 'QR Code Already Used OR Scanned');
        }

        if ($batch_schedule->is_session_completed == 1) {
            return back()->with('error', 'Session Completed');
        }

        if ($batch_schedule->is_session_completed == 0) {
            return back()->with('error', 'Session Not Started');
        }

        if ($batch_schedule->date < date('Y-m-d') && $batch_schedule->date != date('Y-m-d')) {
            return back()->with('error', 'Session Expired');
        }

        if ($batch_schedule->date > date('Y-m-d')) {
            return back()->with('error', 'Session Not Yet Started');
        }
        $participant_payment->is_qr_used = 1;
        $participant_payment->save();
        return back()->with('success', 'Participant entered successfully');
    }

    public function declineParticipant(){
        $this->checkAuth();
        return redirect()->route('entry-user-dashboard')->with('success', 'Participant Declined');
    }
    private function checkAuth(){
        if(Auth::check() && Auth::user()->user_type == 'entry-user'){
            return true;
        }
        abort(401,'Please login as entry user');
    }
}
