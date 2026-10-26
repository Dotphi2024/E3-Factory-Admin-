<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\ParticipantBatch;
use App\Models\Batch;
use Carbon\Carbon;
use App\Http\Resources\ParticipantResource;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request){

        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'mobile' => 'required|unique:participants,mobile',
            'batch_id' => 'required',
            'email' => 'nullable|email|max:191',
            'birth_date' => 'nullable',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'reference_by' => 'nullable|string|max:191',
            'reference' => 'nullable|string|max:191',
            'reference_detail' => 'nullable|string|max:191',
            'device_token' => 'nullable|string',
        ], [
            'batch_id.required' => 'Batch is required',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'mobile.required' => 'Mobile number is required',
            'mobile.unique' => 'The mobile has already been taken.',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first()
            ], 400);
        }

        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 400);
        }

        $participant = new Participant();
        $participant->first_name = $request->first_name;
        $participant->last_name = $request->last_name;
        $participant->mobile = $request->mobile;
        $participant->batch_id = $request->batch_id;
        $participant->email = $request->email;

        // Parse birth_date supporting YYYY-MM-DD or DD-MM-YYYY or DD/MM/YYYY
        if ($request->filled('birth_date')) {
            $rawBirthDate = trim((string) $request->birth_date);
            try {
                if (preg_match('/^\d{1,2}[-\/]\d{1,2}[-\/]\d{4}$/', $rawBirthDate)) {
                    $sep = str_contains($rawBirthDate, '/') ? '/' : '-';
                    $participant->birth_date = Carbon::createFromFormat("d{$sep}m{$sep}Y", $rawBirthDate)->format('Y-m-d');
                } else {
                    $participant->birth_date = Carbon::parse($rawBirthDate)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                try {
                    $participant->birth_date = Carbon::parse($rawBirthDate)->format('Y-m-d');
                } catch (\Exception $e2) {
                    $participant->birth_date = null;
                }
            }
        }

        $participant->address = $request->address;
        $participant->country = $request->country;
        $participant->state = $request->state;
        $participant->city = $request->city;
        $participant->reference = $request->input('reference_by', $request->input('reference', null));
        $participant->reference_detail = $request->input('reference_detail', null);
        $participant->type = 'student';
        $participant->token = $this->generateToken();
        $participant->total_amount = $batch->registration_fee + ($batch->number_of_sessions * $batch->fee_per_session);
        $participant->paid_amount = 0;
        $participant->due_amount = $participant->total_amount;
        $participant->registration_type = 'self';
        $participant->is_registration_fees_paid = 0;
        $participant->payment_status = 'unpaid';
        $participant->device_token = $request->device_token;
        $participant->save();

        $participantBatch = new ParticipantBatch();
        $participantBatch->participant_id = $participant->id;
        $participantBatch->batch_id = $participant->batch_id;
        $participantBatch->is_registration_fees_paid = 0;
        $participantBatch->save();

        // Calculate Registration Amount & create Razorpay order for instant popup
        $registrationFee = (float) ($batch->registration_fee ?? 0);
        $advanceSessionFee = $batch->is_one_session_advance_payment ? (float) ($batch->fee_per_session ?? 0) : 0;
        $totalPayable = $registrationFee + $advanceSessionFee;

        $razorpayService = app(\App\Services\RazorpayService::class);
        $order = $razorpayService->createOrder(
            $totalPayable,
            'rcpt_reg_' . $participant->id . '_' . time(),
            [
                'participant_id' => $participant->id,
                'mobile' => $participant->mobile,
                'batch_id' => $batch->id,
                'purpose' => 'member_registration',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Registered successfully. Please complete the registration payment.',
            'data'=>[
                'participant' => new ParticipantResource($participant),
                'token' => $participant->token,
                'payment_status' => 'unpaid',
                'is_registration_fees_paid' => 0,
                'logged_in' => false,
                'redirect_to_payment' => true,
                'payment' => [
                    'gateway' => 'razorpay',
                    'key_id' => $order['key_id'],
                    'order_id' => $order['order_id'],
                    'amount' => $order['amount'], // in paise
                    'amount_in_rupees' => $order['amount_in_rupees'],
                    'currency' => $order['currency'],
                    'receipt' => $order['receipt'],
                    'name' => config('app.name', 'E3 Factory'),
                    'description' => 'Registration Fee - ' . ($batch->name ?? 'Course'),
                    'prefill' => [
                        'name' => trim(($participant->first_name ?? '') . ' ' . ($participant->last_name ?? '')),
                        'contact' => (string) $participant->mobile,
                        'email' => (string) ($participant->email ?? ''),
                    ],
                    'fee_breakdown' => [
                        'registration_fee' => $registrationFee,
                        'advance_session_fee' => $advanceSessionFee,
                        'total_amount' => $totalPayable,
                    ],
                ]
            ]
        ]);
    }
    public function login(Request $request){

        $validator = \Validator::make($request->all(), [
            'mobile' => 'required|numeric|digits:10',
            'device_token'=>'required'
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first()
            ], 400);
        }
        $participant = Participant::where('mobile', $request->mobile)->first();
        if(!$participant){
            return response()->json([
                'success' => false,
                'message' => 'Participant not found'
            ], 404);
        }
        $otp = rand(1111, 9999);
        if($participant->mobile == '9922990676'){
            $otp = 1234;
        }
        $participant->otp = $otp;
        $participant->device_token = $request->device_token;
        $participant->otp_expiry = Carbon::now()->addMinutes(5);
        $participant->save();
        $p_name = $participant->first_name ? $participant->first_name : 'Participant';
        $apiUrl = "https://www.smsjust.com/sms/user/urlsms.php";
        $queryParams = [
            'username' => 'e3factory@999',
            'pass' => 'Intel@2025',
            'messagetype' => 'TXT',
            'senderid' => 'ETHREF',
            'dest_mobileno' => $request->mobile,
            'message' => "Dear {$p_name}, Your One Time Password is {$otp} for Account Login. Please do not share this code with anyone. Regards E3Factory",
            'templateid' => '1707172294553888002'
        ];
        $response = Http::get($apiUrl, $queryParams);
        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'data' => [
                    'otp' => $otp,
                    'expiry' => $participant->otp_expiry,
                    'payment_status' => $participant->isPaid() ? 'paid' : 'unpaid',
                    'is_registration_fees_paid' => $participant->isPaid() ? 1 : 0,
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }

    }
    public function verifyOtp(Request $request){

        $validator = \Validator::make($request->all(), [
            'mobile' => 'required|numeric|digits:10',
            'otp' => 'required|numeric|digits:4',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first()
            ], 400);
        }
        $participant = Participant::with('batch')->where('mobile', $request->mobile)->first();
        if(!$participant){
            return response()->json([
                'success' => false,
                'message' => 'Participant not found'
            ], 404);
        }
        if($participant->otp == $request->otp && $participant->otp_expiry > Carbon::now()){
            $participant->otp = null;
            $participant->otp_expiry = null;
            $participant->token = $this->generateToken();
            $participant->save();

            $isPaid = $participant->isPaid();

            $responseData = [
                'participant_id' => $participant->id,
                'is_coach' => $participant->is_coach,
                'token' => $participant->token,
                'payment_status' => $isPaid ? 'paid' : 'unpaid',
                'is_registration_fees_paid' => $isPaid ? 1 : 0,
                'logged_in' => $isPaid,
                'redirect_to_payment' => !$isPaid,
                'is_multiple_batch' => $participant->participantBatches->count() > 1 ? 'yes' : 'no',
            ];

            // If participant is unpaid, provide Razorpay order so client immediately redirects to Razorpay
            if (!$isPaid) {
                $batch = $participant->batch;
                $regFee = (float) ($batch->registration_fee ?? 0);
                $advanceFee = ($batch && $batch->is_one_session_advance_payment) ? (float) ($batch->fee_per_session ?? 0) : 0;
                $totalPayable = $regFee + $advanceFee;

                $razorpayService = app(\App\Services\RazorpayService::class);
                $order = $razorpayService->createOrder(
                    $totalPayable,
                    'rcpt_login_' . $participant->id . '_' . time(),
                    ['participant_id' => $participant->id, 'mobile' => $participant->mobile]
                );

                $responseData['payment'] = [
                    'gateway' => 'razorpay',
                    'key_id' => $order['key_id'],
                    'order_id' => $order['order_id'],
                    'amount' => $order['amount'],
                    'amount_in_rupees' => $order['amount_in_rupees'],
                    'currency' => $order['currency'],
                    'name' => config('app.name', 'E3 Factory'),
                    'description' => 'Registration Fee - ' . ($batch ? $batch->name : 'Course'),
                    'prefill' => [
                        'name' => trim(($participant->first_name ?? '') . ' ' . ($participant->last_name ?? '')),
                        'contact' => (string) $participant->mobile,
                        'email' => (string) ($participant->email ?? ''),
                    ],
                    'fee_breakdown' => [
                        'registration_fee' => $regFee,
                        'advance_session_fee' => $advanceFee,
                        'total_amount' => $totalPayable,
                    ],
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified. Registration fee payment is pending. Please complete payment to access your account.',
                    'data' => $responseData
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'data' => $responseData
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'OTP is invalid'
        ], 400);
    }
    public function logout(Request $request){
        $participant = $request->participant;
        $participant->token = null;
        $participant->save();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function getProfile(Request $request){
        $participant = $request->participant;
        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully',
            'data' => [
                'participant' => new ParticipantResource($participant)
            ]
        ]);
    }

    public function updateProfile(Request $request){

        $validator = \Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required|numeric|unique:participants,mobile,'.$request->participant->id,
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first()
            ], 400);
        }
        $participant = $request->participant;
        $participant->first_name = $request->first_name;
        $participant->last_name = $request->last_name;
        $participant->email = $request->email;
        $participant->mobile = $request->mobile;
        $participant->address = $request->address;
        $participant->country = $request->country;
        $participant->state = $request->state;
        $participant->city = $request->city;
        $participant->save();
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'participant' => new ParticipantResource($participant)
            ]
        ]);
    }

    public function updateProfilePhoto(Request $request){

        $validator = \Validator::make($request->all(), [
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first()
            ], 400);
        }

        $participant = $request->participant;
        $profile_photo = $request->file('profile_photo');
        $filename = time() . '.' . str_replace(' ', '-', $profile_photo->getClientOriginalName());
        $profile_photo->move('uploads/participants/profile-photos', $filename);
        $participant->profile_photo = $filename;
        $participant->save();
        return response()->json([
            'success' => true,
            'message' => 'Profile photo updated successfully',
            'data' => [
                'participant' => new ParticipantResource($participant)
            ]
        ]);
    }
    public function removeProfilePhoto(Request $request){

        $participant = $request->participant;
        if($participant->profile_photo && file_exists(public_path('uploads/participants/profile-photos/'.$participant->profile_photo))){
            unlink(public_path('uploads/participants/profile-photos/'.$participant->profile_photo));
        }
        $participant->profile_photo = null;
        $participant->save();
        return response()->json([
            'success' => true,
            'message' => 'Profile photo removed successfully',
            'data' => [
                'participant' => new ParticipantResource($participant)
            ]
        ]);
    }
    public function generateToken(){
        $token = \Str::random(400);
        if(Participant::where('token',$token)->exists()){
            $this->generateToken();
        }
        return $token;
    }
}
