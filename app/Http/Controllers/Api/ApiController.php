<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\BatchBanner;
use App\Models\BatchGroup;
use App\Models\GroupMessage;
use App\Models\BatchGroupParticipant;
use App\Models\ParticipantPayment;
use App\Models\Participant;
use App\Models\ParticipantBatch;
use App\Models\GuestHomePage;
use App\Models\Coach;
use App\Models\PhotoGallery;
use App\Models\VideoGallery;
use App\Models\Setting;
use App\Models\NoticeAndAnnouncement;
use App\Models\Testimonial;
use App\Models\Course;
use App\Models\BatchSchedule;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\Assignment;
use App\Models\AssignmentOption;
use App\Models\AssignmentSubmission;
use App\Models\ParticipantSessionRating;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ParticipantResource;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\File;
use App\Services\FcmNotificationService;

class ApiController extends Controller
{
    protected $fcmNotificationService;

    public function __construct(FcmNotificationService $fcmNotificationService)
    {
        $this->fcmNotificationService = $fcmNotificationService;
    }

    public function getBatches()
    {
        $batches = Batch::active()->orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Batches fetched successfully',
            'data'=>[
                'batches' => $batches
            ]
        ]);
    }

    public function registrationPayment(Request $request){
        $validator = \Validator::make($request->all(), [
            'payment_mode' => 'required',
            'payment_image' => 'required|mimes:png,jpg,jpeg|image',
            'transaction_id' => 'required',
            'amount' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success'=>false,
                'message'=>$messages->first()
            ], 400);
        }
        $participant = $request->participant;
        $batch = $participant->batch;
        if($participant->is_registration_fees_paid){
            return response()->json([
                'success'=>false,
                'message'=>'Registration fees is already paid'
            ]);
        }
        try{
            DB::beginTransaction();
            $filename = '';
            $participant_payment = new ParticipantPayment();
            $participant_payment->participant_id = $participant->id;
            $participant_payment->batch_id = $participant->batch_id;
            $participant_payment->transaction_id = $request->transaction_id;
            $participant_payment->amount = $batch->registration_fee;
            $participant_payment->payment_mode = $request->payment_mode;
            if($request->hasFile('payment_image')){
                $file = $request->file('payment_image');
                $filename = time().'-'.str_replace(' ','-',$file->getClientOriginalName());
                $file->move(public_path('uploads/participant-payment'), $filename);
                $participant_payment->payment_image = $filename;
            }
            $participant_payment->payment_for = 'registration';
            $participant_payment->save();
            if($batch->is_one_session_advance_payment){
                $participant_payment = new ParticipantPayment();
                $participant_payment->participant_id = $participant->id;
                $participant_payment->batch_id = $participant->batch_id;
                $participant_payment->transaction_id = $request->transaction_id;
                $participant_payment->amount = $batch->fee_per_session;
                $participant_payment->payment_mode = $request->payment_mode;
                $participant_payment->payment_for = 'session_fee';
                $participant_payment->session_number = 1;
                $participant_payment->payment_image = $filename;
                $participant_payment->save();
                $data = [
                    'participant_id' => $participant->id,
                    'payment_id' => $participant_payment->id,
                    // Add any other data you need
                ];
                $jsonData = json_encode($data);
                $qr_code_url = route('entry-user.scan-qr-code',$data);

                // Prepare the file name and directory path
                $fileName = 'qrcode_' . $participant->id . '_' . $participant_payment->id . '.png';
                $directoryPath = public_path('uploads/participant-payment/qrcode/');

                // Ensure the directory exists
                if (!file_exists($directoryPath)) {
                    mkdir($directoryPath, 0755, true);
                }

                // Full path to save the QR code
                $qrCodePath = $directoryPath . $fileName;

                // Generate the QR code
                $result = Builder::create()
                    ->writer(new PngWriter())
                    ->data($qr_code_url)  // Use JSON-encoded data
                    ->size(300)
                    ->build();

                // Save the QR code to the file path
                $result->saveToFile($qrCodePath);
                $participant_payment->qr_code_file = $fileName;
                $participant_payment->is_qr_used = 0;
                $participant_payment->save();
            }
            $participant->paid_amount += $batch->registration_fee;
            $participant->due_amount -= $batch->registration_fee;
            $participant->is_registration_fees_paid = true;
            $participant->save();
            $participant_batch = ParticipantBatch::where('participant_id', $participant->id)->where('batch_id', $participant->batch_id)->first();
            if($participant_batch){
                $participant_batch->is_registration_fees_paid = true;
                $participant_batch->save();
            }
            if($batch->is_one_session_advance_payment){
                $participant->paid_amount += $batch->fee_per_session;
                $participant->due_amount -= $batch->fee_per_session;
                $participant->save();
            }
            DB::commit();
            return response()->json([
                'success'=>true,
                'message'=>'Registration fees paid successfully'
            ]);
        }
        catch(Exception $e){
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function coachRegistrationBanner(){
        $batch_banners = BatchBanner::with('batch')->where('is_active', 1)->whereHas('batch',function($query){
            $query->where('start_coach_registration',1)->where('status', '!=','completed');
        })->get();
        foreach ($batch_banners as $banner) {
            $banner->banner = asset('uploads/batch-banners/' . $banner->banner);
        }
        return response()->json([
            'success' => true,
            'message' => 'Banners fetched successfully',
            'data'=>[
                'banners' => $batch_banners
            ]
        ]);
    }

    public function coachRegistration(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
        ], [
            'batch_id.required' => 'Batch is required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success'=>false,
                'message'=>$messages->first()
            ], 400);
        }
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success'=>false,
                'message'=>'Batch not found'
            ]);
        }
        if(!$batch->start_coach_registration){
            return response()->json([
                'success'=>false,
                'message'=>'Coach Registration is not started for this batch'
            ]);
        }
        if($batch->status == 'completed'){
            return response()->json([
                'success'=>false,
                'message'=>'Batch is already completed'
            ]);
        }
        $participant = $request->participant;
        if($participant->batch->status != 'completed'){
            return response()->json([
                'success'=>false,
                'message'=>'Participant is not in completed batch'
            ]);
        }
        $check = Coach::where('batch_id', $request->batch_id)->where('participant_id', $participant->id)->first();
        if($check){
            return response()->json([
                'success'=>false,
                'message'=>'Coach Already Exist'
            ]);
        }
        $coach = new Coach();
        $coach->batch_id = $request->batch_id;
        $coach->participant_id = $participant->id;
        $coach->added_by = 0;
        $coach->status = 'pending';
        $coach->save();
        return response()->json([
            'success'=>true,
            'message'=>'Registration Successfully',
        ]);
    }

    public function getPhotoGalleryForGuest(){
        $photo_galleries = PhotoGallery::active()->where('visibility','Guest')->orderBy('id', 'desc')->get();
        foreach ($photo_galleries as $photo_gallery) {
            $photo_gallery->image = asset('uploads/photo-galleries/'.$photo_gallery->image);
        }
        return response()->json([
            'success' => true,
            'message' => 'Photo Galleries fetched successfully',
            'data'=>[
                'photo_galleries' => $photo_galleries
            ]
        ]);
    }

    public function getPhotoGalleryForAuth(Request $request){
        $photo_galleries = PhotoGallery::active()->where('visibility','Batch')->where('batch_id', $request->participant->batch_id)->orderBy('id', 'desc')->get();
        foreach ($photo_galleries as $photo_gallery) {
            $photo_gallery->image = asset('uploads/photo-galleries/'.$photo_gallery->image);
        }
        return response()->json([
            'success' => true,
            'message' => 'Photo Galleries fetched successfully',
            'data'=>[
                'photo_galleries' => $photo_galleries
            ]
        ]);
    }

    public function getVideoGalleryForGuest(){
        $video_galleries = VideoGallery::active()->where('visibility','Guest')->where('type','video')->orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Video Galleries fetched successfully',
            'data'=>[
                'video_galleries' => $video_galleries
            ]
        ]);
    }

    public function getVideoGalleryForAuth(Request $request){
        $video_galleries = VideoGallery::active()->where('visibility','Batch')->where('batch_id', $request->participant->batch_id)->where('type','video')->orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Video Galleries fetched successfully',
            'data'=>[
                'video_galleries' => $video_galleries
            ]
        ]);
    }

    public function getE3TalkData(){
        $video_galleries = VideoGallery::active()->where('type','e3-talk')->orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'E3 Talk fetched successfully',
            'data'=>[
                'e3_talk' => $video_galleries
            ]
        ]);
    }

    public function getRecommendationBanner(Request $request){
        $recommendation_banner = Setting::getValByKey('recommendation_banner');
        return response()->json([
            'success' => true,
            'message' => 'Recommendation Banner fetched successfully',
            'data'=>[
                'recommendation_banner' => asset('uploads/recommendation-banner/'.$recommendation_banner)
            ]
        ]);
    }

    public function getNoticesForGuest(){
        $notices = NoticeAndAnnouncement::active()->where('visibility','Guest')->where('type','notice')->orderBy('id', 'desc')->get();
        foreach ($notices as $notice) {
            $notice->image = asset('uploads/notice-and-announcement/'.$notice->image);
        }
        return response()->json([
            'success' => true,
            'message' => 'Notices fetched successfully',
            'data'=>[
                'notices' => $notices
            ]
        ]);
    }

    public function getNoticesForAuth(Request $request){
        $notices = NoticeAndAnnouncement::active()->where('visibility','Batch')->where('batch_id', $request->participant->batch_id)->where('type','notice')->orderBy('id', 'desc')->get();
        foreach ($notices as $notice) {
            $notice->image = asset('uploads/notice-and-announcement/'.$notice->image);
        }
        return response()->json([
            'success' => true,
            'message' => 'Notices fetched successfully',
            'data'=>[
                'notices' => $notices
            ]
        ]);
    }

    public function getAnnouncementsForGuest(){
        $announcements = NoticeAndAnnouncement::active()->where('visibility','Guest')->where('type','announcement')->orderBy('id', 'desc')->get();
        foreach ($announcements as $announcement) {
            $announcement->image = asset('uploads/notice-and-announcement/'.$announcement->image);
        }
        return response()->json([
            'success' => true,
            'message' => 'Announcements fetched successfully',
            'data'=>[
                'announcements' => $announcements
            ]
        ]);
    }

    public function getAnnouncementsForAuth(Request $request){
        $announcements = NoticeAndAnnouncement::active()->where('visibility','Batch')->where('batch_id', $request->participant->batch_id)->where('type','announcement')->orderBy('id', 'desc')->get();
        foreach ($announcements as $announcement) {
            $announcement->image = asset('uploads/notice-and-announcement/'.$announcement->image);
        }
        return response()->json([
            'success' => true,
            'message' => 'Announcements fetched successfully',
            'data'=>[
                'announcements' => $announcements
            ]
        ]);
    }

    public function privacyPolicy(){
        $privacy_policy = Setting::getValByKey('privacy_policy');
        return response()->json([
            'success' => true,
            'message' => 'Privacy Policy fetched successfully',
            'data'=>[
                'privacy_policy' => $privacy_policy
            ]
        ]);
    }

    public function termsOfService(){
        $terms_of_service = Setting::getValByKey('terms_of_service');
        return response()->json([
            'success' => true,
            'message' => 'Terms of Service fetched successfully',
            'data'=>[
                'terms_of_service' => $terms_of_service
            ]
        ]);
    }

    public function learnAbout(){
        $learn_about = Setting::getValByKey('learn_about');
        return response()->json([
            'success' => true,
            'message' => 'Learn About fetched successfully',
            'data'=>[
                'learn_about' => $learn_about
            ]
        ]);
    }

    public function getHelp(){
        $get_help = Setting::getValByKey('get_help');
        return response()->json([
            'success' => true,
            'message' => 'Get Help fetched successfully',
            'data'=>[
                'get_help' => $get_help
            ]
        ]);
    }

    public function getTestimonial(){
        $testimonials = Testimonial::active()->orderBy('id', 'desc')->get();
        foreach ($testimonials as $testimonial) {
            $testimonial->image = asset('uploads/testimonials/'.$testimonial->image);
        }
        return response()->json([
            'success' => true,
            'message' => 'Testimonial fetched successfully',
            'data'=>[
                'testimonials' => $testimonials
            ]
        ]);
    }

    public function getGuestHomePageContent(){
        // $imageAndUrl = GuestHomePage::where('type','guest')
        // ->whereNotNull('image')
        // ->whereNotNull('url')
        // ->whereNull('title')
        // ->whereNull('description')
        // ->whereNull('video_url')
        // ->get();

        // $photoAndTitleDescription = GuestHomePage::where('type','guest')
        // ->whereNotNull('image')
        // ->whereNull('url')
        // ->whereNotNull('title')
        // ->whereNotNull('description')
        // ->whereNull('video_url')
        // ->get();

        // $titleAndDescription = GuestHomePage::where('type','guest')
        // ->whereNull('image')
        // ->whereNotNull('title')
        // ->whereNotNull('description')
        // ->whereNull('url')
        // ->whereNull('video_url')
        // ->get();

        // $video_url = GuestHomePage::where('type','guest')
        // ->whereNull('image')
        // ->whereNull('url')
        // ->whereNull('title')
        // ->whereNull('description')
        // ->whereNotNull('video_url')
        // ->get();

        // $imageAndUrl->transform(function ($item) {
        //     $item->image = asset('uploads/guest-homepage/'.$item->image);
        //     return $item;
        // });

        // $photoAndTitleDescription->transform(function ($item) {
        //     $item->image = asset('uploads/guest-homepage/'.$item->image);
        //     return $item;
        // });
        $guestHomePageContent = GuestHomePage::active()->where('type','guest')->orderBy('sequence', 'asc')->get();
        $guestHomePageContent->transform(function ($item) {
            // Determine the type based on the fields that are not null
            if ($item->image && $item->url && !$item->title && !$item->description && !$item->video_url) {
                $item->content_type = 'imageAndUrl';
                $item->image = asset('uploads/guest-homepage/' . $item->image);
            } elseif ($item->image && !$item->url && $item->title && $item->description && !$item->video_url) {
                $item->content_type = 'photoAndTitleDescription';
                $item->image = asset('uploads/guest-homepage/' . $item->image);
            } elseif (!$item->image && !$item->url && $item->title && $item->description && !$item->video_url) {
                $item->content_type = 'titleAndDescription';
            } elseif (!$item->image && !$item->url && !$item->title && !$item->description && $item->video_url) {
                $item->content_type = 'video_url';
            } else {
                $item->content_type = 'other';
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Content fetched successfully',
            'data' => $guestHomePageContent
        ]);
        // return response()->json([
        //     'success' => true,
        //     'message' => 'Content fetched successfully',
        //     'data'=>[
        //         'imageAndUrl' => $imageAndUrl,
        //         'photoAndTitleDescription' => $photoAndTitleDescription,
        //         'titleAndDescription' => $titleAndDescription,
        //         'video_url' => $video_url
        //     ]
        // ]);
    }

    public function getBatchHomePageContent(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        // $imageAndUrl = GuestHomePage::where('batch_id', $request->batch_id)
        // ->where('type','batch')
        // ->whereNotNull('image')
        // ->whereNotNull('url')
        // ->whereNull('title')
        // ->whereNull('description')
        // ->whereNull('video_url')
        // ->get();

        // $photoAndTitleDescription = GuestHomePage::where('batch_id', $request->batch_id)
        // ->where('type','batch')
        // ->whereNotNull('image')
        // ->whereNull('url')
        // ->whereNotNull('title')
        // ->whereNotNull('description')
        // ->whereNull('video_url')
        // ->get();

        // $titleAndDescription = GuestHomePage::where('batch_id', $request->batch_id)
        // ->where('type','batch')
        // ->whereNull('image')
        // ->whereNotNull('title')
        // ->whereNotNull('description')
        // ->whereNull('url')
        // ->whereNull('video_url')
        // ->get();

        // $video_url = GuestHomePage::where('batch_id', $request->batch_id)
        // ->where('type','batch')
        // ->whereNull('image')
        // ->whereNull('url')
        // ->whereNull('title')
        // ->whereNull('description')
        // ->whereNotNull('video_url')
        // ->get();

        // $imageAndUrl->transform(function ($item) {
        //     $item->image = asset('uploads/guest-homepage/'.$item->image);
        //     return $item;
        // });

        // $photoAndTitleDescription->transform(function ($item) {
        //     $item->image = asset('uploads/guest-homepage/'.$item->image);
        //     return $item;
        // });
        $batchHomePageContent = GuestHomePage::active()->where('batch_id', $request->batch_id)->where('type','batch')->orderBy('sequence', 'asc')->get();
        $batchHomePageContent->transform(function ($item) {
            // Determine the type based on the fields that are not null
            if ($item->image && $item->url && !$item->title && !$item->description && !$item->video_url) {
                $item->content_type = 'imageAndUrl';
                $item->image = asset('uploads/guest-homepage/' . $item->image);
            } elseif ($item->image && !$item->url && $item->title && $item->description && !$item->video_url) {
                $item->content_type = 'photoAndTitleDescription';
                $item->image = asset('uploads/guest-homepage/' . $item->image);
            } elseif (!$item->image && !$item->url && $item->title && $item->description && !$item->video_url) {
                $item->content_type = 'titleAndDescription';
            } elseif (!$item->image && !$item->url && !$item->title && !$item->description && $item->video_url) {
                $item->content_type = 'video_url';
            } else {
                $item->content_type = 'other';
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Content fetched successfully',
            'data' => $batchHomePageContent
        ]);
        // return response()->json([
        //     'success' => true,
        //     'message' => 'Content fetched successfully',
        //     'data'=>[
        //         'imageAndUrl' => $imageAndUrl,
        //         'photoAndTitleDescription' => $photoAndTitleDescription,
        //         'titleAndDescription' => $titleAndDescription,
        //         'video_url' => $video_url
        //     ]
        // ]);

    }

    public function getCourses(){
        $courses = Course::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Programs fetched successfully',
            'data'=>[
                'programs' => $courses
            ]
        ]);
    }

    public function getBatchByCourse(){
        $validator = \Validator::make(request()->all(), [
            'course_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $course = Course::find(request()->course_id);
        if(!$course){
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ]);
        }
        $batches = $course->batches()->where('is_active', 1)->where('status','!=','completed')->where('start_date','>=',date('Y-m-d'))->orderBy('id', 'desc')->get();
        $batches = $batches->map(function ($item) {
            if ($item->image) {
                $item->image = asset('uploads/batches/'.$item->image);
            }
            return $item;
        });
        return response()->json([
            'success' => true,
            'message' => 'Batches fetched successfully',
            'data'=>[
                'batches' => $batches
            ]
        ]);
    }

    public function getGroupsOfCoach(Request $request){
        $participant = $request->participant;
        $this->checkCoach($participant);

        $groups = BatchGroup::with(['batch'])->where('coach_id', $participant->id)->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Groups fetched successfully',
            'data'=>[
                'groups' => $groups
            ]
        ]);
    }
    public function getParticipantsForAddingToGroup(Request $request){
        $participant = $request->participant;
        $this->checkCoach($participant);

        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        // Check if the participant is a coach of this batch
        $isCoachOfBatch = $batch->coaches()->where('participants.id', $participant->id)->exists();

        if (!$isCoachOfBatch) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this batch'
            ]);
        }
        $participantsNotInAnyGroup = Participant::where('batch_id', $batch->id)
        ->whereDoesntHave('batchGroupParticipants')
        ->get();
        return response()->json([
            'success' => true,
            'message' => 'Participants fetched successfully',
            'data'=>[
                'participants' => $participantsNotInAnyGroup
            ]
        ]);

    }

    public function addParticipantToGroup(Request $request){
        $validator = \Validator::make($request->all(), [
            'participant_id' => 'required',
            'batch_id' => 'required',
            'batch_group_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $participant = $request->participant;
        $this->checkCoach($participant);

        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }

        $batch_group = BatchGroup::where('id', $request->batch_group_id)->where('coach_id', $participant->id)->first();
        if(!$batch_group){
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ]);
        }
        // Check if the participant is a coach of this batch
        $isCoachOfBatch = $batch->coaches()->where('participants.id', $participant->id)->exists();

        if (!$isCoachOfBatch) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this batch'
            ]);
        }
        $check = BatchGroupParticipant::where('batch_id', $batch->id)->where('participant_id', $request->participant_id)->first();

        if($check){
            return response()->json([
                'success' => false,
                'message' => 'Participant already added to this group'
            ]);
        }

        $batch_group_participant = new BatchGroupParticipant();
        $batch_group_participant->batch_id = $request->batch_id;
        $batch_group_participant->batch_group_id = $request->batch_group_id;
        $batch_group_participant->participant_id = $request->participant_id;
        $batch_group_participant->added_by = 0;
        $batch_group_participant->save();
        return response()->json([
            'success' => true,
            'message' => 'Participant added to group successfully',
        ]);
    }
    public function getParticipantsOfGroup(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_group_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $participant = $request->participant;
        $this->checkCoach($participant);

        $batch_group = BatchGroup::with('batchGroupParticipants1')->where('coach_id', $participant->id)->find($request->batch_group_id);
        if(!$batch_group){
            $batch_group = BatchGroup::find($request->batch_group_id);
            if (!$batch_group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
            // Check if the participant is a head coach for the batch
                $isHeadCoach = Coach::where('participant_id', $participant->id)
                ->where('batch_id', $batch_group->batch_id)
                ->where('is_head_coach', true)
                ->exists();

            if (!$isHeadCoach) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this group'
                ]);
            }
            $batch_group = BatchGroup::with('batchGroupParticipants1')
            ->find($request->batch_group_id);
        }
        $batch_group_participants = BatchGroupParticipant::where('batch_group_id', $request->batch_group_id)->get();
        return response()->json([
            'success' => true,
            'message' => 'Participants fetched successfully',
            'data'=>[
                'batch_group' => $batch_group
            ]
        ]);
    }
    public function addMessageToGroup(Request $request){
        $validator = \Validator::make($request->all(), [
            'message' => 'required',
            'description' => 'required',
            'batch_group_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $participant = $request->participant;
        $this->checkCoach($participant);

        $batch_group = BatchGroup::where('coach_id', $participant->id)->find($request->batch_group_id);
        if(!$batch_group){
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ]);
        }
        $group_message = new GroupMessage();
        $group_message->batch_id = $batch_group->batch_id;
        $group_message->batch_group_id = $request->batch_group_id;
        $group_message->coach_id = $participant->id;
        $group_message->message = $request->message;
        $group_message->description = $request->description;
        $group_message->save();

        $device_tokens = $batch_group->batchGroupParticipants1()->whereNotNull('device_token')->pluck('device_token')->toArray();
        $this->fcmNotificationService->sendNotification($device_tokens, 'New Message in Group', 'You have a new Message in group.', 'group_message', $group_message);
        return response()->json([
            'success' => true,
            'message' => 'Message added to group successfully',
        ]);
    }
    public function getMessages(Request $request){
        $participant = Participant::with('batches')->find($request->participant->id);
        $this->checkCoach($participant);
        $batch_messages = $group_messages = $participant_messages = [];
        if($participant->is_coach){
            $batch_messages = GroupMessage::whereIn('batch_id', $participant->batches->pluck('id')->toArray())
            ->where('coach_id', $participant->id)
            ->whereNull('batch_group_id')
            ->whereNull('participant_id')
            ->get();

            $group_messages = GroupMessage::whereIn('batch_id', $participant->batches->pluck('id')->toArray())
            ->where('coach_id', $participant->id)
            ->whereNotNull('batch_group_id')
            ->whereNull('participant_id')
            ->get();

            $participant_messages = GroupMessage::whereIn('batch_id', $participant->batches->pluck('id')->toArray())
            ->where('coach_id', $participant->id)
            ->whereNotNull('batch_group_id')
            ->whereNotNull('participant_id')
            ->get();
        }
        else{
            $batch_messages = GroupMessage::where('batch_id', $request->participant->batch->id)
            ->whereNull('batch_group_id')
            ->whereNull('participant_id')
            ->get();

            $group_messages = GroupMessage::where('batch_id', $request->participant->batch->id)
            ->whereNotNull('batch_group_id')
            ->whereNull('participant_id')
            ->get();

            $participant_messages = GroupMessage::where('batch_id', $request->participant->batch->id)
            ->whereNotNull('batch_group_id')
            ->whereNotNull('participant_id')
            ->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Messages fetched successfully',
            'data' => [
                'batch_messages' => $batch_messages,
                'group_messages' => $group_messages,
                'participant_messages' => $participant_messages,
            ]
        ]);
    }
    public function getBatchesAndGroups(Request $request){
        $participant = Participant::with(['batches'=>function($query){
            $query->where('is_head_coach', 1);
        }, 'assignedGroups'])->find($request->participant->id);
        $this->checkCoach($participant);
        return response()->json([
            'success' => true,
            'message' => 'Batches and groups fetched successfully',
            'data' => [
                'batches' => $participant->batches,
                'groups' => $participant->assignedGroups,
            ]
        ]);
    }
    public function getGroupsByBatch(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $participant = $request->participant;
        $this->checkCoach($participant);
        $isHeadCoach = $participant->batches()
        ->wherePivot('batch_id', $request->batch_id)
        ->wherePivot('is_head_coach', 1)
        ->exists();
        if($isHeadCoach){
            $groups = BatchGroup::where('batch_id', $request->batch_id)->get();
        }
        else{
            $groups = BatchGroup::where('batch_id', $request->batch_id)->where('coach_id', $participant->id)->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Groups fetched successfully',
            'data' => [
                'batch_details'=> Batch::find($request->batch_id),
                'groups' => $groups,
            ]
        ]);
    }
    public function sendMessageCoachToBatch(Request $request){

        $validator = \Validator::make($request->all(), [
            'message' => 'required',
            'batch_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $participant = $request->participant;
        $this->checkCoach($participant);
        $batch = $participant->batches()
        ->wherePivot('batch_id', $request->batch_id)
        ->wherePivot('is_head_coach', 1)
        ->first();
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'You are not a head coach of this batch'
            ]);
        }

        $group_message = new GroupMessage();
        $group_message->batch_id = $request->batch_id;
        $group_message->coach_id = $participant->id;
        $group_message->message = $request->message;
        $group_message->description = $request->description;
        $group_message->save();

        $device_tokens = $batch->batchParticipants()->whereNotNull('device_token')->pluck('device_token')->toArray();
        $this->fcmNotificationService->sendNotification($device_tokens, 'New Message in Batch', 'You have a new Message in batch.', 'batch_message', $group_message);
        return response()->json([
            'success' => true,
            'message' => 'Message sent to batch successfully',
        ]);
    }
    public function sendMessageCoachToGroup(Request $request){

        $validator = \Validator::make($request->all(), [
            'message' => 'required',
            'batch_id' => 'required',
            'batch_group_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $participant = $request->participant;
        $this->checkCoach($participant);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $participant->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $participant->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();
            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }

        $group_message = new GroupMessage();
        $group_message->batch_id = $request->batch_id;
        $group_message->batch_group_id = $request->batch_group_id;
        $group_message->coach_id = $participant->id;
        $group_message->message = $request->message;
        $group_message->description = $request->description;
        $group_message->save();
        $device_tokens = $group->batchGroupParticipants1()->whereNotNull('device_token')->pluck('device_token')->toArray();
        $this->fcmNotificationService->sendNotification($device_tokens, 'New Message in Group', 'You have a new Message in group.', 'group_message', $group_message);
        return response()->json([
            'success' => true,
            'message' => 'Message sent to group successfully',
        ]);
    }
    public function sendMessageCoachToParticipant(Request $request){

        $validator = \Validator::make($request->all(), [
            'message' => 'required',
            'batch_id' => 'required',
            'batch_group_id' => 'required',
            'participant_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }
        $participant = Participant::find($request->participant_id);

        if(!$participant){
            return response()->json([
                'success' => false,
                'message' => 'Participant not found'
            ]);
        }

        $group_message = new GroupMessage();
        $group_message->batch_id = $request->batch_id;
        $group_message->batch_group_id = $request->batch_group_id;
        $group_message->participant_id = $request->participant_id;
        $group_message->coach_id = $coach->id;
        $group_message->message = $request->message;
        $group_message->description = $request->description;
        $group_message->save();

        $device_tokens = [$participant->device_token];
        $this->fcmNotificationService->sendNotification($device_tokens, 'New Message', 'You have a new Message.','participant_message', $group_message);
        return response()->json([
            'success' => true,
            'message' => 'Message sent to participant successfully',
        ]);
    }
    public function addGroupHomePageContent(Request $request){

        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
            'batch_group_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }
        if (!$request->title && !$request->description && !$request->image && !$request->url && !$request->video_url) {
            return response()->json(['error' => 'Please enter at least one field.'], 400);
        }
        $guest_homepage = new GuestHomePage();
        $guest_homepage->title = $request->title;
        $guest_homepage->description = $request->description;
        $guest_homepage->url = $request->url;
        $guest_homepage->video_url = $request->video_url;
        $guest_homepage->batch_id = $request->batch_id;
        $guest_homepage->batch_group_id = $request->batch_group_id;
        $guest_homepage->type = 'batch';
        $guest_homepage->added_by = 0;
        $guest_homepage->sequence = $request->sequence ?? 0;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move(public_path('uploads/guest-homepage/'), $filename);
            $guest_homepage->image = $filename;
        }

        $guest_homepage->save();
        return response()->json([
            'success' => true,
            'message' => 'Content added successfully',
        ]);
    }
    public function getGroupHomePageContent(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
            'batch_group_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }

        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();
            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }

        $guest_homepage = GuestHomePage::where('batch_id', $request->batch_id)->where('batch_group_id', $request->batch_group_id)->get();
        $guest_homepage->transform(function($item){
            if ($item->image && $item->url && !$item->title && !$item->description && !$item->video_url) {
                $item->content_type = 'imageAndUrl';
                $item->image = asset('uploads/guest-homepage/' . $item->image);
            } elseif ($item->image && !$item->url && $item->title && $item->description && !$item->video_url) {
                $item->content_type = 'photoAndTitleDescription';
                $item->image = asset('uploads/guest-homepage/' . $item->image);
            } elseif (!$item->image && !$item->url && $item->title && $item->description && !$item->video_url) {
                $item->content_type = 'titleAndDescription';
            } elseif (!$item->image && !$item->url && !$item->title && !$item->description && $item->video_url) {
                $item->content_type = 'video_url';
            } else {
                $item->content_type = 'other';
            }
            return $item;
        });
        return response()->json([
            'success' => true,
            'message' => 'Content fetched successfully',
            'data' => [
                'guest_homepage' => $guest_homepage
            ],
        ]);
    }
    public function editGroupHomePageContent(Request $request){

        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
            'batch_group_id' => 'required',
            'guest_homepage_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }
        if (!$request->title && !$request->description && !$request->image && !$request->url && !$request->video_url) {
            return response()->json(['error' => 'Please enter at least one field.'], 400);
        }
        $guest_homepage = GuestHomePage::where('batch_id', $request->batch_id)->where('batch_group_id', $request->batch_group_id)->find($request->guest_homepage_id);
        if(!$guest_homepage){
            return response()->json([
                'success' => false,
                'message' => 'Content not found'
            ]);
        }

        $guest_homepage->title = $request->title;
        $guest_homepage->description = $request->description;
        $guest_homepage->url = $request->url;
        $guest_homepage->video_url = $request->video_url;
        $guest_homepage->sequence = $request->sequence ?? 0;
        if ($request->hasFile('image')) {
            if($guest_homepage->image){
                File::delete(public_path('uploads/guest-homepage/'.$guest_homepage->image));
            }
            $file = $request->file('image');
            $filename = time() . '.' . str_replace(' ', '-', $file->getClientOriginalName());
            $file->move(public_path('uploads/guest-homepage/'), $filename);
            $guest_homepage->image = $filename;
        }
        $guest_homepage->save();
        return response()->json([
            'success' => true,
            'message' => 'Content updated successfully',
        ]);

    }
    public function deleteGroupHomePageContent(Request $request){

        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
            'batch_group_id' => 'required',
            'guest_homepage_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }

        $guest_homepage = GuestHomePage::where('batch_id', $request->batch_id)->where('batch_group_id', $request->batch_group_id)->find($request->guest_homepage_id);
        if(!$guest_homepage){
            return response()->json([
                'success' => false,
                'message' => 'Content not found'
            ]);
        }
        if($guest_homepage->image){
            File::delete(public_path('uploads/guest-homepage/'.$guest_homepage->image));
        }
        $guest_homepage->delete();
        return response()->json([
            'success' => true,
            'message' => 'Content deleted successfully',
        ]);
    }
    public function getActiveSession(Request $request){

        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $batch_schedule = BatchSchedule::where('batch_id', $request->batch_id)->where('is_session_completed',2)->first();
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Active session fetched successfully',
            'data' => [
                'batch_schedule' => $batch_schedule
            ],
        ]);

    }

    public function addMeeting(Request $request){

        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
            'batch_group_id' => 'required',
            'batch_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'date' => 'required',
            'time' => 'required',
            'type' => 'required',
            'link'=>'required_if:type,online',
            'venue'=>'required_if:type,offline',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }
        $batch_schedule = BatchSchedule::where('batch_id', $request->batch_id)->where('is_session_completed',2)->first();
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ]);
        }
        $meeting = new Meeting();
        $meeting->batch_schedule_id = $batch_schedule->id;
        $meeting->coach_id = $coach->id;
        $meeting->batch_id = $request->batch_id;
        $meeting->batch_group_id = $request->batch_group_id;
        $meeting->title = $request->title;
        $meeting->description = $request->description;
        $meeting->date = $request->date;
        $meeting->time = $request->time;
        $meeting->type = $request->type;
        $meeting->link = $request->link;
        $meeting->venue = $request->venue;
        $meeting->save();

        $device_tokens = $group->batchGroupParticipants1()->whereNotNull('device_token')->pluck('device_token')->toArray();
        $this->fcmNotificationService->sendNotification($device_tokens, 'New Meeting', 'You have a new'. $meeting->type .' Meeting.','new_meeting', $meeting);
        return response()->json([
            'success' => true,
            'message' => 'Meeting added successfully',
        ]);
    }
    public function getOnlineMeetings(Request $request){

        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
            'batch_group_id' => 'required',
            'batch_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }
        $batch_schedule = BatchSchedule::where('batch_id', $request->batch_id)->first();
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ]);
        }
        $meetings = Meeting::where('batch_schedule_id', $batch_schedule->id)->where('batch_group_id', $request->batch_group_id)->where('batch_id', $request->batch_id)
        ->where('type', 'online')->get();
        return response()->json([
            'success' => true,
            'message' => 'Meetings fetched successfully',
            'data' => [
                'meetings' => $meetings
            ],
        ]);
    }
    public function getMeetings(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
            'batch_group_id' => 'required',
            'batch_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }
        // $batch_schedule = BatchSchedule::where('batch_id', $request->batch_id)->first();
        $batch_schedule = BatchSchedule::find($request->batch_schedule_id);
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ]);
        }
        $meetings = Meeting::where('batch_schedule_id', $request->batch_schedule_id)->where('batch_group_id', $request->batch_group_id)->where('batch_id', $request->batch_id)
        ->where('status', 'active')->get();
        $completed_meetings = Meeting::where('batch_schedule_id', $request->batch_schedule_id)->where('batch_group_id', $request->batch_group_id)->where('batch_id', $request->batch_id)
        ->where('status', 'completed')->get();
        return response()->json([
            'success' => true,
            'message' => 'Meetings fetched successfully',
            'data' => [
                'upcoming_meetings' => $meetings,
                'completed_meetings' => $completed_meetings
            ],
        ]);
    }
    public function getOfflineMeetings(Request $request){

        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
            'batch_group_id' => 'required',
            'batch_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }
        $batch_schedule = BatchSchedule::where('batch_id', $request->batch_id)->first();
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'No active session found'
            ]);
        }
        $meetings = Meeting::where('batch_schedule_id', $batch_schedule->id)->where('batch_group_id', $request->batch_group_id)->where('batch_id', $request->batch_id)
        ->where('type', 'offline')->get();
        return response()->json([
            'success' => true,
            'message' => 'Meetings fetched successfully',
            'data' => [
                'meetings' => $meetings
            ],
        ]);
    }
    public function markMeetingCompleted(Request $request){

        $validator = \Validator::make($request->all(), [
            'meeting_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $this->checkCoach($request->participant);
        $meeting = Meeting::where('coach_id', $request->participant->id)->find($request->meeting_id);
        if(!$meeting){
            return response()->json([
                'success' => false,
                'message' => 'Meeting not found'
            ]);
        }
        $meeting->status = 'completed';
        $meeting->save();
        return response()->json([
            'success' => true,
            'message' => 'Meeting marked as completed successfully',
        ]);
    }
    public function getParticipantsForAttendance(Request $request){
        $validator = \Validator::make($request->all(), [
            'meeting_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $this->checkCoach($request->participant);
        $meeting = Meeting::where('coach_id', $request->participant->id)->where('status','completed')->find($request->meeting_id);
        if(!$meeting){
            return response()->json([
                'success' => false,
                'message' => 'Meeting not found'
            ]);
        }
        $participants = Participant::whereHas('batchGroupParticipants',function($query) use ($meeting){
           $query->where('batch_group_id', $meeting->batch_group_id)
           ->where('batch_id', $meeting->batch_id);
        })->whereDoesntHave('meetingAttendances',function($query) use ($meeting){
            $query->where('meeting_id', $meeting->id);
        })->get();
        return response()->json([
            'success' => true,
            'message' => 'Participants fetched successfully',
            'data' => [
                'participants' => $participants
            ]
            ]);
    }
    public function markAttendance(Request $request){

        $validator = \Validator::make($request->all(), [
            'meeting_id' => 'required',
            'participant_id' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $this->checkCoach($request->participant);
        $meeting = Meeting::where('coach_id', $request->participant->id)->where('status','completed')->find($request->meeting_id);
        if(!$meeting){
            return response()->json([
                'success' => false,
                'message' => 'Meeting not found'
            ]);
        }
        $participant = Participant::find($request->participant_id);
        if(!$participant){
            return response()->json([
                'success' => false,
                'message' => 'Participant not found'
            ]);
        }
        $participantBelongsToGroup = BatchGroupParticipant::where('batch_group_id', $meeting->batch_group_id)
        ->where('participant_id', $participant->id)
        ->where('batch_id', $meeting->batch_id)
        ->exists();
        if(!$participantBelongsToGroup){
            return response()->json([
                'success' => false,
                'message' => 'Participant is not part of the group'
            ]);
        }
        $checkAttendance = MeetingAttendance::where('meeting_id', $meeting->id)
        ->where('participant_id', $participant->id)
        ->exists();
        if($checkAttendance){
            return response()->json([
                'success' => false,
                'message' => 'Attendance already marked'
            ]);
        }
        $meeting_attendance = new MeetingAttendance();
        $meeting_attendance->meeting_id = $meeting->id;
        $meeting_attendance->participant_id = $participant->id;
        $meeting_attendance->status = $request->status;
        $meeting_attendance->save();
        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
        ]);
    }
    public function markAttendanceCompleted(Request $request){
        $validator = \Validator::make($request->all(), [
            'meeting_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $this->checkCoach($request->participant);
        $meeting = Meeting::where('coach_id', $request->participant->id)->find($request->meeting_id);
        if(!$meeting){
            return response()->json([
                'success' => false,
                'message' => 'Meeting not found'
            ]);
        }
        $participants = Participant::whereHas('batchGroupParticipants',function($query) use ($meeting){
            $query->where('batch_group_id', $meeting->batch_group_id)
            ->where('batch_id', $meeting->batch_id);
         })->whereDoesntHave('meetingAttendances',function($query) use ($meeting){
             $query->where('meeting_id', $meeting->id);
         })->get();
        if($participants->count() > 0){
            return response()->json([
                'success' => false,
                'message' => 'Please mark attendance for all participants',
            ]);
        }
        $meeting->is_all_attendance_marked = 1;
        $meeting->save();
        return response()->json([
            'success' => true,
            'message' => 'Meeting all attendance marked successfully',
        ]);
    }
    // public function getAssignmentsBySession(Request $request){
    //     $validator = \Validator::make($request->all(), [
    //         'batch_schedule_id' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         $messages = $validator->getMessageBag();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $messages->first(),
    //         ]);
    //     }
    //     $this->checkCoach($request->participant);
    //     $batch_schedule = BatchSchedule::where('is_session_completed',2)->find($request->batch_schedule_id);
    //     if(!$batch_schedule){
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Session not found or Not Started Or Completed'
    //         ]);
    //     }
    //     $batchAssigned = $request->participant->batches->contains($batch_schedule->batch_id);
    //     if(!$batchAssigned){
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'You are not assigned to this batch'
    //         ]);
    //     }
    //     $assignments = Assignment::where('batch_schedule_id', $request->batch_schedule_id)->get();
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Assignments fetched successfully',
    //         'data' => [
    //             'assignments' => $assignments
    //         ]
    //     ]);
    // }

    // public function startOrStopAssignment(Request $request){
    //     $validator = \Validator::make($request->all(), [
    //         'assignment_id' => 'required',
    //         'batch_id' => 'required',
    //         'batch_schedule_id' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         $messages = $validator->getMessageBag();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $messages->first(),
    //         ]);
    //     }
    //     $this->checkCoach($request->participant);
    //     $batch_schedule = BatchSchedule::where('batch_id', $request->batch_id)->where('is_session_completed',2)->find($request->batch_schedule_id);
    //     if(!$batch_schedule){
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Session not found or Not Started Or Completed'
    //         ]);
    //     }
    //     $batchAssigned = $request->participant->batches->contains($request->batch_id);
    //     if(!$batchAssigned){
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'You are not assigned to this batch'
    //         ]);
    //     }
    //     $assignment = Assignment::find($request->assignment_id);
    //     if(!$assignment){
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Assignment not found'
    //         ]);
    //     }
    //     $assignment->is_started = !$assignment->is_started;
    //     $assignment->save();
    //     return response()->json([
    //         'success' => true,
    //         'message' => $assignment->is_started ? 'Assignment started successfully' : 'Assignment stopped successfully',
    //     ]);
    // }

    public function getSessionsByAnswerForCoach(Request $request){
        $validator = \Validator::make($request->all(), [
            'participant_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $this->checkCoach($request->participant);

        $coach = $request->participant;
        $participant = Participant::find($request->participant_id);
        if(!$participant){
            return response()->json([
                'success' => false,
                'message' => 'Participant not found'
            ]);
        }
        $batch_schedules = BatchSchedule::whereHas('assignmentSubmissions',function ($query) use ($participant){
            $query->where('participant_id', $participant->id);
        })->get();
        return response()->json([
            'success' => true,
            'message' => 'Schedules fetched successfully',
            'data' => [
                'schedules' => $batch_schedules
            ]
        ]);
    }
    public function getQuestionAndAnswerBySession(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
            'participant_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $this->checkCoach($request->participant);
        $participant = Participant::find($request->participant_id);
        if(!$participant){
            return response()->json([
                'success' => false,
                'message' => 'Participant not found'
            ]);
        }

        $batch_schedule = BatchSchedule::find($request->batch_schedule_id);
        if(!$batch_schedule){
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ]);
        }
        $assignmentSubmissions = $batch_schedule->assignmentSubmissions->where('participant_id', $request->participant_id);
        $assignmentSubmissions = $assignmentSubmissions->transform(function ($item, $key) {
           if($item->file){
               $item->file = asset('uploads/participants/assignment/'.$item->file);
           }
           return $item;
        });
        $total_out_of_marks = 300;
        foreach($batch_schedule->assignments as $assignment){
            $total_out_of_marks += $assignment->options->max('mark');
        }
        $total_marks_obtained = $assignmentSubmissions->sum('mark');

        $online_meetings = Meeting::where('batch_schedule_id', $request->batch_schedule_id)->where('type', 'online')->where('status', 'completed')->get();
        $online_meetings_attended = MeetingAttendance::whereIn('meeting_id', $online_meetings->pluck('id'))
        ->where('participant_id', $request->participant_id)
        ->where('status', 'present')
        ->count();

        $offline_meetings = Meeting::where('batch_schedule_id', $request->batch_schedule_id)->where('type', 'offline')->where('status', 'completed')->get();
        $offline_meetings_attended = MeetingAttendance::whereIn('meeting_id', $offline_meetings->pluck('id'))
        ->where('participant_id', $request->participant_id)
        ->where('status', 'present')
        ->count();

        $online_meeting_attended_marks = $online_meetings->count() > 0 ? ($online_meetings_attended / $online_meetings->count()) * 100 : 0;
        $offline_meeting_attended_marks = $offline_meetings->count() > 0 ? ($offline_meetings_attended / $offline_meetings->count()) * 100 : 0;
        $total_marks_obtained = $total_marks_obtained + $online_meeting_attended_marks + $offline_meeting_attended_marks;

        // Check if the participant is registered for the next session
        $next_session = BatchSchedule::where('batch_id', $batch_schedule->batch_id)
        ->where('session_number', '>', $batch_schedule->session_number)
        ->orderBy('session_number', 'asc')
        ->first();
        $is_registered_for_next_session = false;
        $staticQuestions = [
            [
                'question' => 'How many Offline group meetings you have attended?',
                'formula' => 'Formula: (Total meetings attended)/ (Total meetings conducted) * 100',
                'response'=>'',
                'marks_obtained' => $offline_meetings->count() . ' / ' . $offline_meetings_attended . ' x 100 = ' . $offline_meeting_attended_marks
            ],
            [
                'question' => 'How many Online group meetings you have attended?',
                'formula' => 'Formula: (Total meetings attended)/ (Total meetings conducted) * 100',
                'response'=>'',
                'marks_obtained' => $online_meetings->count() . ' / ' . $online_meetings_attended . ' x 100 = ' . $online_meeting_attended_marks
            ],
        ];
        $rating = ParticipantSessionRating::where('participant_id', $request->participant_id)->where('batch_schedule_id', $request->batch_schedule_id)->
        where('rating_type','coach_to_participant')->first();
        if($rating){
            $total_marks_obtained += $rating->rating * 10;
            $staticQuestions[] = [
                'question' => 'Coach Rating',
                'formula' => 'On the scale of 1 to 10',
                'response' => $rating->rating,
                'marks_obtained' => $rating->rating * 10
            ];
        }
        if ($next_session) {
            $staticQuestions[] = [
                'question' => 'Registration For Next Session?',
                'formula' => '',
                'response' => $is_registered_for_next_session ? 'Yes' : 'No',
                'marks_obtained' => $is_registered_for_next_session ? '100 Marks' : '0 Marks'
            ];
            $total_out_of_marks += 100;
            // Check if the participant has paid for the next session
            $payment = ParticipantPayment::where('participant_id', $request->participant_id)
                ->where('batch_id', $batch_schedule->batch_id)
                ->where('session_number', $next_session->session_number)
                ->first();

            // If participant is registered for the next session, add 100 marks
            if ($payment) {
                $total_marks_obtained += 100;
                $is_registered_for_next_session = true;
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Assignment submissions fetched successfully',
            'data' => [
                'assignmentSubmissions' => $assignmentSubmissions,
                'total_out_of_marks' => $total_out_of_marks,
                'total_marks_obtained' => $total_marks_obtained,
                'staticQuestions' => $staticQuestions
            ]
        ]);
    }
    public function getParticipantPendingRatingSessions(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required',
            'batch_group_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch = Batch::find($request->batch_id);
        if(!$batch){
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ]);
        }
        $group = BatchGroup::where('coach_id', $coach->id)->where('batch_id', $request->batch_id)->find($request->batch_group_id);
        if(!$group){
            $isHeadCoach = Coach::where('participant_id', $coach->id)
            ->where('batch_id', $request->batch_id)
            ->where('is_head_coach', true)
            ->exists();

            if ($isHeadCoach) {
                // Fetch the group based on batch_group_id, without coach_id filter since the coach is a head coach
                $group = BatchGroup::where('batch_id', $request->batch_id)->find($request->batch_group_id);
            }
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ]);
            }
        }
        $batch_schedules = BatchSchedule::where('batch_id', $batch->id)
        ->where('is_session_completed', 1)->get();
        $schedules_with_rating = $batch_schedules->map(function ($batch_schedule) use ($group) {
            // Get all participants in the batch group
            $totalParticipants = $group->batchGroupParticipants1->count();

            // Get the count of ratings already given for this batch schedule
            $ratedParticipants = ParticipantSessionRating::where('batch_schedule_id', $batch_schedule->id)
                ->where('rating_type','coach_to_participant')
                ->whereIn('participant_id', $group->batchGroupParticipants1->pluck('id'))
                ->count();

            // Check if all participants are rated
            $is_rating_completed = ($totalParticipants === $ratedParticipants) ? 1 : 0;

            // Return all batch schedule fields, adding is_rating_completed
            return array_merge($batch_schedule->toArray(), [
                'is_rating_completed' => $is_rating_completed
            ]);
        });
        return response()->json([
            'success' => true,
            'message' => 'Pending Rating Sessions fetched successfully',
            'data' => [
                'batch_schedules' => $schedules_with_rating
            ]
        ]);
    }
    public function getUnratedParticipantsListBySession(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
            'batch_group_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }

        $coach = $request->participant;
        $this->checkCoach($coach);

        $batch_schedule = BatchSchedule::find($request->batch_schedule_id);
        if (!$batch_schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ]);
        }

        // Fetch the batch group
        $group = BatchGroup::where('batch_id', $batch_schedule->batch_id)
            ->where('coach_id', $coach->id)
            ->find($request->batch_group_id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Batch group not found'
            ]);
        }

        // Get participants of the batch group
        $groupParticipantIds = $group->batchGroupParticipants->pluck('participant_id');

        // Get participants who have already been rated for the session
        $ratedParticipantIds = ParticipantSessionRating::where('batch_schedule_id', $batch_schedule->id)
            ->where('rating_type','coach_to_participant')
            ->whereIn('participant_id', $groupParticipantIds)
            ->pluck('participant_id');

        // Get participants who have NOT been rated yet
        $unratedParticipants = Participant::whereIn('id', $groupParticipantIds)
            ->whereNotIn('id', $ratedParticipantIds)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Unrated participants fetched successfully',
            'data' => [
                'unrated_participants' => $unratedParticipants
            ]
        ]);
    }

    public function addRatingCoachToParticipant(Request $request){
        $validator = \Validator::make($request->all(), [
            'batch_schedule_id' => 'required',
            'participant_id' => 'required',
            'rating' => 'required|integer|between:1,10',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        $coach = $request->participant;
        $this->checkCoach($coach);
        $batch_schedule = BatchSchedule::where('is_session_completed', 1)->find($request->batch_schedule_id);
        if (!$batch_schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ]);
        }

        $participant = Participant::find($request->participant_id);
        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found'
            ]);
        }
        $checkRating = ParticipantSessionRating::where('batch_schedule_id', $batch_schedule->id)->where('participant_id', $participant->id)->
        where('rating_type','coach_to_participant')->first();
        if($checkRating){
            return response()->json([
                'success' => false,
                'message' => 'Rating already given'
            ]);
        }

        $rating = new ParticipantSessionRating();
        $rating->batch_schedule_id = $batch_schedule->id;
        $rating->batch_id = $participant->batch_id;
        $rating->participant_id = $participant->id;
        $rating->rating = $request->rating;
        $rating->coach_id = $coach->id;
        $rating->save();
        return response()->json([
            'success' => true,
            'message' => 'Rating added successfully'
        ]);
    }
    private function checkCoach($participant){
        if(!$participant->is_coach){
            abort(response()->json([
                'success' => false,
                'message' => 'You are not a coach'
            ]));
        }
    }

    public function getMemberAssignmentReport(Request $request)
    {
        $tokenParticipant = $request->participant;
        if (!$tokenParticipant) {
            $tokenStr = null;
            if ($request->hasHeader('Authorization') && preg_match('/Bearer\s(\S+)/', $request->header('Authorization'), $matches)) {
                $tokenStr = $matches[1];
            } else {
                $tokenStr = $request->input('_auth') ?? $request->input('token') ?? $request->input('auth_token');
            }
            if ($tokenStr) {
                $tokenParticipant = Participant::where('token', trim((string) $tokenStr))->first();
            }
        }

        $selected_participant_id = $request->member_id ?? $request->participant_id ?? ($tokenParticipant ? $tokenParticipant->id : null);
        if ($selected_participant_id) {
            $request->merge(['member_id' => $selected_participant_id, 'participant_id' => $selected_participant_id]);
        }

        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required_without_all:member_id,participant_id|nullable|numeric',
            'group_id' => 'nullable|numeric',
            'schedule_id' => 'nullable|numeric',
            'participant_id' => 'nullable|numeric',
            'member_id' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $batchId = $request->batch_id;
        if (!$batchId && $selected_participant_id) {
            $p = Participant::with('participantBatches')->find($selected_participant_id);
            if (!$p) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member / Participant not found with ID: ' . $selected_participant_id
                ], 404);
            }
            $batchId = $p->batch_id;
            if (!$batchId && $p->participantBatches->count() > 0) {
                $batchId = $p->participantBatches->first()->id;
            }
            if (!$batchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No batch assigned to member with ID: ' . $selected_participant_id
                ], 404);
            }
        }

        $batch = Batch::with(['schedules.assignments.options', 'groups.batchGroupParticipants', 'course'])->find($batchId);

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 404);
        }

        $selected_group_id = $request->group_id;
        $selected_schedule_id = $request->schedule_id;

        if ($selected_participant_id) {
            $participants = Participant::where('id', $selected_participant_id)->get();
        } elseif ($selected_group_id) {
            $groupParticipantIds = BatchGroupParticipant::where('batch_group_id', $selected_group_id)->pluck('participant_id')->toArray();
            $participants = Participant::whereIn('id', $groupParticipantIds)->orderBy('first_name', 'ASC')->get();
        } else {
            $participants = $batch->batchParticipants()->orderBy('first_name', 'ASC')->get();
        }

        $schedules = $batch->schedules;
        $activeSchedules = $selected_schedule_id 
            ? $schedules->where('id', $selected_schedule_id) 
            : $schedules;

        $assignmentsList = collect();
        foreach ($activeSchedules as $sch) {
            foreach ($sch->assignments as $asgn) {
                $assignmentsList->push($asgn);
            }
        }

        $assignmentIds = $assignmentsList->pluck('id')->toArray();
        $participantIds = $participants->pluck('id')->toArray();

        $submissions = AssignmentSubmission::with(['assignmentOption'])
            ->whereIn('assignment_id', $assignmentIds)
            ->whereIn('participant_id', $participantIds)
            ->get()
            ->groupBy(function ($item) {
                return $item->participant_id . '_' . $item->assignment_id;
            });

        $participantGroupMap = [];
        foreach ($batch->groups as $group) {
            foreach ($group->batchGroupParticipants as $bgp) {
                $participantGroupMap[$bgp->participant_id] = $group->group_name;
            }
        }

        $membersReport = [];
        $totalSubmissionsCount = 0;

        foreach ($participants as $participant) {
            $submittedCount = 0;
            $pendingCount = 0;
            $totalMarksObtained = 0;
            $totalMaxMarks = 0;
            $assignmentsData = [];

            foreach ($assignmentsList as $assignment) {
                $key = $participant->id . '_' . $assignment->id;
                $submission = $submissions->get($key)?->first();

                $maxMark = 0;
                if ($assignment->option_type == 'options') {
                    $maxMark = (float) $assignment->options->max('mark');
                }
                $totalMaxMarks += $maxMark;

                if ($submission) {
                    $submittedCount++;
                    $totalSubmissionsCount++;
                    $markObtained = 0;
                    if ($assignment->option_type == 'options' && $submission->assignmentOption) {
                        $markObtained = (float) $submission->assignmentOption->mark;
                    } else {
                        $markObtained = (float) ($submission->mark ?? 0);
                    }
                    $totalMarksObtained += $markObtained;

                    $assignmentsData[] = [
                        'assignment_id' => $assignment->id,
                        'question' => $assignment->question,
                        'option_type' => $assignment->option_type,
                        'session_id' => $assignment->batch_schedule_id,
                        'session_name' => $assignment->batchSchedule?->name,
                        'status' => 'Submitted',
                        'mark_obtained' => $markObtained,
                        'max_mark' => $maxMark,
                        'answer' => $submission->answer,
                        'selected_option' => $submission->assignmentOption?->option,
                        'file_url' => $submission->file ? asset('uploads/participants/assignment/' . $submission->file) : null,
                        'submitted_at' => $submission->created_at ? $submission->created_at->format('Y-m-d H:i:s') : null,
                    ];
                } else {
                    $pendingCount++;
                    $assignmentsData[] = [
                        'assignment_id' => $assignment->id,
                        'question' => $assignment->question,
                        'option_type' => $assignment->option_type,
                        'session_id' => $assignment->batch_schedule_id,
                        'session_name' => $assignment->batchSchedule?->name,
                        'status' => 'Pending',
                        'mark_obtained' => 0,
                        'max_mark' => $maxMark,
                        'answer' => null,
                        'selected_option' => null,
                        'file_url' => null,
                        'submitted_at' => null,
                    ];
                }
            }

            $percentage = $totalMaxMarks > 0 ? round(($totalMarksObtained / $totalMaxMarks) * 100, 1) : 0;

            $membersReport[] = [
                'participant_id' => $participant->id,
                'first_name' => $participant->first_name,
                'last_name' => $participant->last_name,
                'full_name' => trim($participant->first_name . ' ' . $participant->last_name),
                'mobile' => $participant->mobile,
                'group_name' => $participantGroupMap[$participant->id] ?? 'Unassigned',
                'submitted_count' => $submittedCount,
                'pending_count' => $pendingCount,
                'total_assignments' => count($assignmentsList),
                'total_marks_obtained' => $totalMarksObtained,
                'total_max_marks' => $totalMaxMarks,
                'percentage' => $percentage,
                'assignments' => $assignmentsData,
            ];
        }

        $totalAssignmentsPossible = count($participants) * count($assignmentsList);

        return response()->json([
            'success' => true,
            'message' => 'Member assignment report retrieved successfully',
            'data' => [
                'batch_info' => [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'program_name' => $batch->course?->name,
                    'number_of_sessions' => $batch->number_of_sessions,
                ],
                'summary' => [
                    'total_members' => count($membersReport),
                    'total_assignments' => count($assignmentsList),
                    'total_submissions_count' => $totalSubmissionsCount,
                    'total_assignments_possible' => $totalAssignmentsPossible,
                    'overall_submission_rate' => $totalAssignmentsPossible > 0 ? round(($totalSubmissionsCount / $totalAssignmentsPossible) * 100, 1) : 0,
                ],
                'assignments' => $assignmentsList->map(function ($asgn) {
                    return [
                        'id' => $asgn->id,
                        'question' => $asgn->question,
                        'option_type' => $asgn->option_type,
                        'session_id' => $asgn->batch_schedule_id,
                        'session_name' => $asgn->batchSchedule?->name,
                    ];
                }),
                'members_report' => $membersReport,
            ]
        ]);
    }

    public function getParticipantFeeDetails(Request $request)
    {
        $tokenParticipant = $request->participant;
        if (!$tokenParticipant) {
            $tokenStr = null;
            if ($request->hasHeader('Authorization') && preg_match('/Bearer\s(\S+)/', $request->header('Authorization'), $matches)) {
                $tokenStr = $matches[1];
            } else {
                $tokenStr = $request->input('_auth') ?? $request->input('token') ?? $request->input('auth_token');
            }
            if ($tokenStr) {
                $tokenParticipant = Participant::where('token', trim((string) $tokenStr))->first();
            }
        }

        $selected_participant_id = $request->member_id ?? $request->participant_id ?? ($tokenParticipant ? $tokenParticipant->id : null);
        if ($selected_participant_id) {
            $request->merge(['member_id' => $selected_participant_id, 'participant_id' => $selected_participant_id]);
        }

        if (!$selected_participant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide member_id or participant_id parameter'
            ], 422);
        }

        $participant = Participant::with(['batch.schedules', 'participantBatches', 'payments.batch'])
            ->find($selected_participant_id);

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Member / Participant not found with ID: ' . $selected_participant_id
            ], 404);
        }

        $batch = $participant->batch;
        if (!$batch && $participant->participantBatches->count() > 0) {
            $batch = $participant->participantBatches->first();
        }

        $payments = $participant->payments;
        $registrationPayment = $payments->where('payment_for', 'registration')->first();

        $schedules = $batch ? $batch->schedules->sortBy('session_number') : collect();
        $sessionBreakdown = [];
        $totalSessionsFeeDue = 0;
        $totalSessionsFeePaid = 0;

        foreach ($schedules as $schedule) {
            $sessionPayment = $payments->where('payment_for', 'session_fee')
                ->where('session_number', $schedule->session_number)
                ->first();

            $feeAmount = (float) ($schedule->amount > 0 ? $schedule->amount : ($batch ? $batch->fee_per_session : 0));
            $isPaid = $sessionPayment ? true : false;

            if ($isPaid) {
                $totalSessionsFeePaid += $feeAmount;
            } else {
                $totalSessionsFeeDue += $feeAmount;
            }

            $sessionBreakdown[] = [
                'session_id' => $schedule->id,
                'session_number' => $schedule->session_number,
                'session_name' => $schedule->name,
                'session_date' => $schedule->date,
                'amount' => $feeAmount,
                'status' => $isPaid ? 'Paid' : 'Pending',
                'is_paid' => $isPaid,
                'payment_link_active' => (bool) $schedule->payment_link_status,
                'is_session_completed' => $schedule->is_session_completed,
                'payment_details' => $sessionPayment ? [
                    'payment_id' => $sessionPayment->id,
                    'transaction_id' => $sessionPayment->transaction_id,
                    'amount' => (float) $sessionPayment->amount,
                    'payment_mode' => $sessionPayment->payment_mode,
                    'payment_image_url' => $sessionPayment->payment_image ? asset('uploads/participant-payment/' . $sessionPayment->payment_image) : null,
                    'qr_code_url' => $sessionPayment->qr_code_file ? asset('uploads/participant-payment/qrcode/' . $sessionPayment->qr_code_file) : null,
                    'is_qr_used' => (bool) $sessionPayment->is_qr_used,
                    'paid_at' => $sessionPayment->created_at ? $sessionPayment->created_at->format('Y-m-d H:i:s') : null,
                ] : null,
            ];
        }

        $paymentHistory = $payments->map(function ($p) {
            return [
                'payment_id' => $p->id,
                'batch_id' => $p->batch_id,
                'batch_name' => $p->batch?->name,
                'payment_for' => $p->payment_for,
                'session_number' => $p->session_number,
                'amount' => (float) $p->amount,
                'payment_mode' => $p->payment_mode,
                'transaction_id' => $p->transaction_id,
                'payment_image_url' => $p->payment_image ? asset('uploads/participant-payment/' . $p->payment_image) : null,
                'qr_code_url' => $p->qr_code_file ? asset('uploads/participant-payment/qrcode/' . $p->qr_code_file) : null,
                'created_at' => $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Participant fee and pending details fetched successfully',
            'data' => [
                'member_info' => [
                    'participant_id' => $participant->id,
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'full_name' => trim($participant->first_name . ' ' . $participant->last_name),
                    'mobile' => $participant->mobile,
                    'email' => $participant->email,
                    'city' => $participant->city,
                ],
                'batch_info' => $batch ? [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'registration_fee' => (float) $batch->registration_fee,
                    'fee_per_session' => (float) $batch->fee_per_session,
                    'number_of_sessions' => $batch->number_of_sessions,
                ] : null,
                'financial_summary' => [
                    'total_amount' => (float) $participant->total_amount,
                    'paid_amount' => (float) $participant->paid_amount,
                    'due_amount' => (float) $participant->due_amount,
                    'is_registration_fees_paid' => (bool) $participant->is_registration_fees_paid,
                    'registration_fee_status' => $participant->is_registration_fees_paid ? 'Paid' : 'Pending',
                    'registration_fee_amount' => $batch ? (float) $batch->registration_fee : 0,
                    'total_sessions_count' => count($schedules),
                    'total_sessions_fee_paid' => $totalSessionsFeePaid,
                    'total_sessions_fee_due' => $totalSessionsFeeDue,
                ],
                'registration_payment_details' => $registrationPayment ? [
                    'payment_id' => $registrationPayment->id,
                    'amount' => (float) $registrationPayment->amount,
                    'payment_mode' => $registrationPayment->payment_mode,
                    'transaction_id' => $registrationPayment->transaction_id,
                    'payment_image_url' => $registrationPayment->payment_image ? asset('uploads/participant-payment/' . $registrationPayment->payment_image) : null,
                    'paid_at' => $registrationPayment->created_at ? $registrationPayment->created_at->format('Y-m-d H:i:s') : null,
                ] : null,
                'session_fee_breakdown' => $sessionBreakdown,
                'payment_history' => $paymentHistory,
            ]
        ]);
    }

    public function manageCoachGroupMembers(Request $request)
    {
        $tokenParticipant = $request->participant;
        if (!$tokenParticipant && $request->hasHeader('Authorization')) {
            $authHeader = $request->header('Authorization');
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $tokenParticipant = Participant::where('token', $matches[1])->first();
            }
        }

        if (!$request->has('coach_id') && $tokenParticipant) {
            $request->merge(['coach_id' => $tokenParticipant->id]);
        }

        $validator = \Validator::make($request->all(), [
            'batch_id' => 'required_without:batch_group_id|nullable|numeric',
            'batch_group_id' => 'required_without:batch_id|nullable|numeric',
            'coach_id' => 'nullable|numeric',
            'member_ids' => 'nullable',
            'participant_ids' => 'nullable',
            'member_id' => 'nullable',
            'participant_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $batchGroupId = $request->batch_group_id;
        $batchGroup = $batchGroupId ? BatchGroup::find($batchGroupId) : null;
        $batchId = $request->batch_id ?? ($batchGroup ? $batchGroup->batch_id : null);

        $batch = Batch::with(['groups.batchGroupParticipants'])->find($batchId);
        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 404);
        }

        $addedCount = 0;
        $alreadyAddedCount = 0;
        $addedMembersList = [];
        $rawIds = $request->participant_ids ?? $request->member_ids ?? $request->participant_id ?? $request->member_id;

        // Parse member IDs reliably from array, string like "[6,7]", "6,7", or single integer
        if ($rawIds && $batchGroup) {
            $participantIds = [];
            if (is_array($rawIds)) {
                foreach ($rawIds as $item) {
                    if (is_numeric($item)) {
                        $participantIds[] = (int) $item;
                    }
                }
            } elseif (is_string($rawIds) || is_numeric($rawIds)) {
                preg_match_all('/\d+/', (string) $rawIds, $matches);
                if (!empty($matches[0])) {
                    $participantIds = array_map('intval', $matches[0]);
                }
            }

            foreach ($participantIds as $pId) {
                if ($pId <= 0) continue;

                $pObj = Participant::find($pId);
                if (!$pObj) continue;

                $exists = BatchGroupParticipant::where('batch_group_id', $batchGroup->id)
                    ->where('participant_id', $pId)
                    ->first();

                if (!$exists) {
                    $bgp = new BatchGroupParticipant();
                    $bgp->batch_id = $batch->id;
                    $bgp->batch_group_id = $batchGroup->id;
                    $bgp->participant_id = $pId;
                    $bgp->added_by = 0;
                    $bgp->save();
                    $addedCount++;
                    $addedMembersList[] = [
                        'participant_id' => $pObj->id,
                        'full_name' => trim($pObj->first_name . ' ' . $pObj->last_name),
                        'mobile' => $pObj->mobile,
                        'status' => 'Newly Added',
                    ];
                } else {
                    $alreadyAddedCount++;
                    $addedMembersList[] = [
                        'participant_id' => $pObj->id,
                        'full_name' => trim($pObj->first_name . ' ' . $pObj->last_name),
                        'mobile' => $pObj->mobile,
                        'status' => 'Already in Group',
                    ];
                }
            }

            $batch->load(['groups.batchGroupParticipants']);
        }

        $participants = $batch->batchParticipants()->orderBy('first_name', 'ASC')->get();
        $groupParticipantMap = [];
        foreach ($batch->groups as $group) {
            $gName = $group->name ?? $group->group_name ?? 'Group #' . $group->id;
            foreach ($group->batchGroupParticipants as $bgp) {
                $groupParticipantMap[$bgp->participant_id] = [
                    'group_id' => $group->id,
                    'group_name' => $gName,
                ];
            }
        }

        $members = $participants->map(function ($p) use ($groupParticipantMap, $batchGroupId) {
            $groupInfo = $groupParticipantMap[$p->id] ?? null;
            $isInAnyGroup = $groupInfo ? true : false;
            $isInSelectedGroup = ($groupInfo && $batchGroupId && $groupInfo['group_id'] == $batchGroupId);

            return [
                'participant_id' => $p->id,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'full_name' => trim($p->first_name . ' ' . $p->last_name),
                'mobile' => $p->mobile,
                'email' => $p->email,
                'city' => $p->city,
                'is_in_any_group' => $isInAnyGroup,
                'current_group_id' => $groupInfo ? $groupInfo['group_id'] : null,
                'current_group_name' => $groupInfo ? $groupInfo['group_name'] : 'Unassigned',
                'is_in_selected_group' => $isInSelectedGroup,
            ];
        });

        $targetGroupName = $batchGroup ? ($batchGroup->name ?? $batchGroup->group_name ?? 'Group #' . $batchGroup->id) : null;

        $message = $addedCount > 0 
            ? "Successfully added {$addedCount} member(s) to group '{$targetGroupName}'" 
            : 'Batch members fetched successfully for group selection';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'batch_info' => [
                    'id' => $batch->id,
                    'name' => $batch->name,
                ],
                'selected_group' => $batchGroup ? [
                    'id' => $batchGroup->id,
                    'name' => $targetGroupName,
                ] : null,
                'added_count' => $addedCount,
                'already_added_count' => $alreadyAddedCount,
                'added_members' => $addedMembersList,
                'total_members' => count($members),
                'unassigned_members_count' => $members->where('is_in_any_group', false)->count(),
                'members' => $members->values(),
            ]
        ]);
    }

    public function removeMemberFromGroup(Request $request)
    {
        $tokenParticipant = $request->participant;
        if (!$tokenParticipant && $request->hasHeader('Authorization')) {
            $authHeader = $request->header('Authorization');
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $tokenParticipant = Participant::where('token', $matches[1])->first();
            }
        }

        $selectedParticipantId = $request->member_id ?? $request->participant_id ?? ($tokenParticipant ? $tokenParticipant->id : null);

        $validator = \Validator::make($request->all(), [
            'batch_group_id' => 'required|numeric',
            'participant_id' => 'nullable|numeric',
            'member_id' => 'nullable|numeric',
        ]);

        if ($validator->fails() || !$selectedParticipantId) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide batch_group_id and member_id / participant_id'
            ], 422);
        }

        $deleted = BatchGroupParticipant::where('batch_group_id', $request->batch_group_id)
            ->where('participant_id', $selectedParticipantId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Member removed from group successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Member was not in this group'
        ], 404);
    }
}
