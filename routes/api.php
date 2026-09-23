<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ParticipantApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('test-notification', [AuthController::class, 'testNotification']);
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::get('getBatches', [ApiController::class, 'getBatches']);
Route::post('getMemberAssignmentReport', [ApiController::class, 'getMemberAssignmentReport']);
Route::post('getParticipantFeeDetails', [ApiController::class, 'getParticipantFeeDetails']);
Route::post('manageCoachGroupMembers', [ApiController::class, 'manageCoachGroupMembers']);
Route::post('getCoachBatchMembersForGroup', [ApiController::class, 'manageCoachGroupMembers']);
Route::post('addMembersToGroup', [ApiController::class, 'manageCoachGroupMembers']);
Route::post('removeMemberFromGroup', [ApiController::class, 'removeMemberFromGroup']);
Route::get('getPhotoGalleryForGuest', [ApiController::class, 'getPhotoGalleryForGuest']);
Route::get('getVideoGalleryForGuest', [ApiController::class, 'getVideoGalleryForGuest']);
Route::get('getRecommendationBanner', [ApiController::class, 'getRecommendationBanner']);
Route::get('getE3TalkData', [ApiController::class, 'getE3TalkData']);

// Get Notice for Guest
Route::get('getNoticesForGuest', [ApiController::class, 'getNoticesForGuest']);

// Get Announcements for Guest
Route::get('getAnnouncementsForGuest', [ApiController::class, 'getAnnouncementsForGuest']);

//Privacy Policy
Route::get('privacy-policy', [ApiController::class, 'privacyPolicy']);

// Terms of Service
Route::get('terms-of-service', [ApiController::class, 'termsOfService']);

// Learn About
Route::get('learn-about', [ApiController::class, 'learnAbout']);

// Get Help
Route::get('get-help', [ApiController::class, 'getHelp']);

// Get Testimonial
Route::get('get-testimonial', [ApiController::class, 'getTestimonial']);

// Get Guest Home Page Content
Route::get('get-guest-home-page-content', [ApiController::class, 'getGuestHomePageContent']);

// Get Courses
Route::get('getPrograms', [ApiController::class, 'getCourses']);

// Get Batch by Course
Route::get('getBatchByProgram', [ApiController::class, 'getBatchByCourse']);

Route::middleware(['verify_auth_token'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('get-profile', [AuthController::class, 'getProfile']);
    Route::post('update-profile', [AuthController::class, 'updateProfile']);
    Route::post('update-profile-photo', [AuthController::class, 'updateProfilePhoto']);
    Route::post('remove-profile-photo', [AuthController::class, 'removeProfilePhoto']);

    //Registration Payment
    Route::post('registration-payment', [ApiController::class, 'registrationPayment']);

    // Coach Registration Banner
    Route::get('coach-registration-banner', [ApiController::class, 'coachRegistrationBanner']);

    // Coach registration
    Route::post('coach-registration', [ApiController::class, 'coachRegistration']);

    // Get Photo Gallery For Authenticated User
    Route::get('getPhotoGalleryForAuth', [ApiController::class, 'getPhotoGalleryForAuth']);

    // Get Video Gallery For Authenticated User
    Route::get('getVideoGalleryForAuth', [ApiController::class, 'getVideoGalleryForAuth']);

    // Get Notices For Authenticated User
    Route::get('getNoticesForAuth', [ApiController::class, 'getNoticesForAuth']);

    // Get Announcements For Authenticated User
    Route::get('getAnnouncementsForAuth', [ApiController::class, 'getAnnouncementsForAuth']);

    // /*----------------------------Coach Apis ---------------------------*/
    // Get Groups of Coach
    Route::get('getGroupsOfCoach', [ApiController::class, 'getGroupsOfCoach']);
    Route::get('getParticipantsForAddingToGroup', [ApiController::class, 'getParticipantsForAddingToGroup']);
    Route::post('addParticipantToGroup', [ApiController::class, 'addParticipantToGroup']);
    Route::get('getParticipantsOfGroup', [ApiController::class, 'getParticipantsOfGroup']);
    Route::post('addMessageToGroup', [ApiController::class, 'addMessageToGroup']);
    Route::get('getMessages', [ApiController::class, 'getMessages']);
    Route::get('getBatchesAndGroups', [ApiController::class, 'getBatchesAndGroups']);
    Route::get('getGroupsByBatch', [ApiController::class, 'getGroupsByBatch']);
    Route::post('sendMessageCoachToBatch', [ApiController::class, 'sendMessageCoachToBatch']);
    Route::post('sendMessageCoachToGroup', [ApiController::class, 'sendMessageCoachToGroup']);
    Route::post('sendMessageCoachToParticipant', [ApiController::class, 'sendMessageCoachToParticipant']);
    Route::get('getGroupHomePageContent', [ApiController::class, 'getGroupHomePageContent']);
    Route::post('addGroupHomePageContent', [ApiController::class, 'addGroupHomePageContent']);
    Route::post('editGroupHomePageContent', [ApiController::class, 'editGroupHomePageContent']);
    Route::post('deleteGroupHomePageContent', [ApiController::class, 'deleteGroupHomePageContent']);
    Route::get('getActiveSession', [ApiController::class, 'getActiveSession']);
    Route::post('addMeeting', [ApiController::class, 'addMeeting']);
    Route::get('getOnlineMeetings', [ApiController::class, 'getOnlineMeetings']);
    Route::get('getOfflineMeetings', [ApiController::class, 'getOfflineMeetings']);
    Route::get('getMeetings', [ApiController::class, 'getMeetings']);
    Route::post('markMeetingCompleted', [ApiController::class, 'markMeetingCompleted']);
    Route::get('getParticipantsForAttendance', [ApiController::class, 'getParticipantsForAttendance']);
    Route::post('markAttendance', [ApiController::class, 'markAttendance']);
    Route::post('markAttendanceCompleted', [ApiController::class, 'markAttendanceCompleted']);
    Route::get('getSessionsByAnswerForCoach', [ApiController::class, 'getSessionsByAnswerForCoach']);
    Route::get('getQuestionAndAnswerBySession', [ApiController::class, 'getQuestionAndAnswerBySession']);
    Route::get('getParticipantPendingRatingSessions', [ApiController::class, 'getParticipantPendingRatingSessions']);
    Route::get('getUnratedParticipantsListBySession', [ApiController::class, 'getUnratedParticipantsListBySession']);
    Route::post('addRatingCoachToParticipant', [ApiController::class, 'addRatingCoachToParticipant']);
    // Route::get('getAssignmentsBySession', [ApiController::class, 'getAssignmentsBySession']);
    // Route::post('startOrStopAssignment', [ApiController::class, 'startOrStopAssignment']);

    // Assignment & Fee Details & Group Management APIs
    Route::post('getMemberAssignmentReport', [ApiController::class, 'getMemberAssignmentReport']);
    Route::post('getParticipantFeeDetails', [ApiController::class, 'getParticipantFeeDetails']);
    Route::post('manageCoachGroupMembers', [ApiController::class, 'manageCoachGroupMembers']);
    Route::post('getCoachBatchMembersForGroup', [ApiController::class, 'manageCoachGroupMembers']);
    Route::post('addMembersToGroup', [ApiController::class, 'manageCoachGroupMembers']);
    Route::post('removeMemberFromGroup', [ApiController::class, 'removeMemberFromGroup']);
    // /*----------------------------End Coach Apis---------------------------*/

    // Get Batch Home Page Content
    Route::get('get-batch-home-page-content', [ApiController::class, 'getBatchHomePageContent']);

    // Participant
    Route::get('get-my-batches', [ParticipantApiController::class, 'getMyBatches']);
    Route::get('get-my-groups', [ParticipantApiController::class, 'getMyGroups']);
    Route::get('get-participants-of-my-group', [ParticipantApiController::class, 'getParticipantsOfMyGroup']);
    Route::get('get-participant-profile', [ParticipantApiController::class, 'getParticipantProfile']);
    Route::get('get-messages-for-participant', [ParticipantApiController::class, 'getMessagesForParticipant']);

    Route::post('add-recommendation', [ParticipantApiController::class, 'addRecommendation']);

    Route::get('getSessionPaymentLinks', [ParticipantApiController::class, 'getSessionPaymentLinks']);
    Route::get('view-qr-code', [ParticipantApiController::class, 'viewQrCode']);
    Route::post('make-session-payment', [ParticipantApiController::class, 'makeSessionPayment']);
    Route::get('switch-batch', [ParticipantApiController::class, 'switchBatch']);
    Route::post('enroll-batch', [ParticipantApiController::class, 'enrollBatch']);

    Route::get('getSessionByActiveAssignmentForParticipant', [ParticipantApiController::class, 'getSessionByActiveAssignmentForParticipant']);
    Route::get('getAssignmentsForParticipant', [ParticipantApiController::class, 'getAssignmentsForParticipant']);
    Route::post('submit-assignment', [ParticipantApiController::class, 'submit_assignment']);
    Route::get('getSessionsByAnswerForParticipant', [ParticipantApiController::class, 'getSessionsByAnswerForParticipant']);
    Route::get('getQuestionAndAnswerBySessionForParticipant', [ParticipantApiController::class, 'getQuestionAndAnswerBySessionForParticipant']);
    Route::get('getMeetingsForParticipant', [ParticipantApiController::class, 'getMeetingsForParticipant']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
