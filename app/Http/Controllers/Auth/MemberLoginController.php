<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use GuzzleHttp\Client;
use Auth;

class MemberLoginController extends Controller
{
    public function showForm()
    {
        return view('auth.member-login');
    }
    public function sendOtp(Request $request){
        $request->validate([
            'mobile'=>'required|numeric'
        ]);
        $member=Member::where('phone',$request->mobile)->first();
        if($member){
            $otp=rand(111111,999999);
            $response=200;
            // $response=$this->sendSmsideaOTPSMS($mobile,$otp);
            if($response==200){
                $member->otp_code=$otp;
                $member->otp_expiry=now()->addMinute(5);
                $member->save();
                return redirect()->route('member.otp.form',$member->phone)->with('success','OTP sent successfully');
            }
            else{
                return back()->with('failed','Failed to send OTP');
            }
        }
        else{
            return back()->with('failed','Member Not Found');
        }
    }
    public function showOtpForm($mobile){
        $member=Member::where('phone',$mobile)->first();
        if($member && $member->otp_code && $member->otp_expiry > now()){
            return view('auth.verify-otp', compact('mobile'));
        }
        return redirect()->route('member.show-login-form');
    }
    public function verifyOtp(Request $request){
        $request->validate([
            'mobile'=>'required|numeric',
            'otp'=>'required|numeric'
        ]);
        $member=Member::where('phone',$request->mobile)->first();
        $otp=$request->otp;
        if($member){
            if($otp == $member->otp_code && $member->otp_expiry > now()){
                $member->otp_code=null;
                $member->otp_expiry=null;
                $member->save();
                // Log in the member
                // Auth::guard('members')->login($member);
            }
            else{
                return back()->with('failed','Invalid OTP');
            }
        }
        else{
            return back()->with('failed','Member Not Found');
        }
    }
    public static function sendSmsideaOTPSMS($to, $otp) {
        $msg = config('app.smsidea.otp_template');
        $msg = str_replace('{#var#}', ' '.$otp.' ', $msg);
        $endpoint = config('app.smsidea.url');
        $endpoint .= '?mobile='.config('app.smsidea.mobile');
        $endpoint .= '&pass='.config('app.smsidea.pass');
        $endpoint .= '&senderid='.config('app.smsidea.senderid');
        $endpoint .= '&to='.$to;
        $endpoint .= '&msg='.$msg;
        $client = new Client();
        $response = $client->request('GET', $endpoint);

        $statusCode = $response->getStatusCode();
        $content = $response->getBody();
        return $statusCode;
    }
}
