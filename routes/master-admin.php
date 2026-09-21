<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterAdmin\DashboardController;
use App\Http\Controllers\MasterAdmin\SettingController;
use App\Http\Controllers\MasterAdmin\RolesController;
use App\Http\Controllers\MasterAdmin\UserController;
use App\Http\Controllers\MasterAdmin\CourseController;
use App\Http\Controllers\MasterAdmin\BatchController;
use App\Http\Controllers\MasterAdmin\ParticipantController;
use App\Http\Controllers\MasterAdmin\CoachController;
use App\Http\Controllers\MasterAdmin\PhotoGalleryController;
use App\Http\Controllers\MasterAdmin\VideoGalleryController;
use App\Http\Controllers\MasterAdmin\NoticeAndAnnouncementController;
use App\Http\Controllers\MasterAdmin\TestimonialController;
use App\Http\Controllers\MasterAdmin\GuestHomepageController;
use App\Http\Controllers\MasterAdmin\BatchHomepageController;
use App\Http\Controllers\MasterAdmin\MessagesController;
use App\Http\Controllers\MasterAdmin\RecommendationController;


Route::post('get-states',[ParticipantController::class,'getStates'])->name('get-states');
Route::post('get-cities',[ParticipantController::class,'getCities'])->name('get-cities');
Route::middleware(['auth'])->prefix('master')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('master.dashboard');
    Route::get('my-profile',[UserController::class,'myProfile'])->name('master.my-profile');
    Route::post('my-profile',[UserController::class,'updateMyProfile'])->name('master.my-profile.update');
    // Setting
    Route::prefix('setting')->group(function(){
        Route::get('add-setting', [SettingController::class, 'add'])->name('master.setting.add-setting');
        Route::post('add-setting', [SettingController::class, 'store'])->name('master.setting.store-setting');

        // Privacy Policy
        Route::get('privacy-policy', [SettingController::class, 'privacy_policy'])->name('master.setting.privacy-policy')->middleware('permission:settings edit');
        Route::post('privacy-policy', [SettingController::class, 'update_privacy_policy'])->name('master.setting.update-privacy-policy')->middleware('permission:settings edit');

        // Terms and Conditions
        Route::get('terms-of-service', [SettingController::class, 'terms_of_service'])->name('master.setting.terms-of-service')->middleware('permission:settings edit');
        Route::post('terms-of-service', [SettingController::class, 'update_terms_of_service'])->name('master.setting.update-terms-of-service')->middleware('permission:settings edit');

        // Learn About
        Route::get('learn-about', [SettingController::class, 'learn_about'])->name('master.setting.learn-about')->middleware('permission:settings edit');
        Route::post('learn-about', [SettingController::class, 'update_learn_about'])->name('master.setting.update-learn-about')->middleware('permission:settings edit');

        // Get Help
        Route::get('get-help', [SettingController::class, 'get_help'])->name('master.setting.get-help')->middleware('permission:settings edit');
        Route::post('get-help', [SettingController::class, 'update_get_help'])->name('master.setting.update-get-help')->middleware('permission:settings edit');

        // Recommendation banner
        Route::get('recommendation-banner', [SettingController::class, 'recommendation_banner'])->name('master.setting.recommendation-banner')->middleware('permission:settings edit');
        Route::post('recommendation-banner', [SettingController::class, 'update_recommendation_banner'])->name('master.setting.update-recommendation-banner')->middleware('permission:settings edit');
    });

    // Roles and Permission
    Route::prefix('roles')->group(function(){
        Route::get('list', [RolesController::class, 'list'])->name('master.roles.list')->middleware('permission:roles list');
        Route::get('add', [RolesController::class, 'add'])->name('master.roles.add')->middleware('permission:roles add');
        Route::post('add', [RolesController::class, 'store'])->name('master.roles.store')->middleware('permission:roles add');
        Route::get('edit/{id}', [RolesController::class, 'edit'])->name('master.roles.edit')->middleware('permission:roles edit');
        Route::post('edit/{id}', [RolesController::class, 'update'])->name('master.roles.update')->middleware('permission:roles edit');
        Route::get('delete/{id}', [RolesController::class, 'delete'])->name('master.roles.delete')->middleware('permission:roles delete');
    });

    // Users
    Route::prefix('users')->group(function(){
        Route::get('list', [UserController::class, 'list'])->name('master.users.list')->middleware('permission:users list');
        Route::get('add', [UserController::class, 'add'])->name('master.users.add')->middleware('permission:users add');
        Route::post('add', [UserController::class, 'store'])->name('master.users.store')->middleware('permission:users add');
        Route::get('edit/{id}', [UserController::class, 'edit'])->name('master.users.edit')->middleware('permission:users edit');
        Route::post('edit/{id}', [UserController::class, 'update'])->name('master.users.update')->middleware('permission:users edit');
        Route::get('delete/{id}', [UserController::class, 'delete'])->name('master.users.delete')->middleware('permission:users delete');
    });

    // Courses
    Route::prefix('courses')->group(function(){
        Route::get('list', [CourseController::class, 'list'])->name('master.courses.list')->middleware('permission:course list');
        Route::get('add', [CourseController::class, 'add'])->name('master.courses.add')->middleware('permission:course add');
        Route::post('add', [CourseController::class, 'store'])->name('master.courses.store')->middleware('permission:course add');
        Route::get('edit/{id}', [CourseController::class, 'edit'])->name('master.courses.edit')->middleware('permission:course edit');
        Route::post('edit/{id}', [CourseController::class, 'update'])->name('master.courses.update')->middleware('permission:course edit');
        Route::get('delete/{id}', [CourseController::class, 'delete'])->name('master.courses.delete')->middleware('permission:course delete');
    });

    // Batches
    Route::prefix('batches')->group(function(){
        Route::get('list', [BatchController::class, 'list'])->name('master.batches.list')->middleware('permission:batch list');
        Route::get('add', [BatchController::class, 'add'])->name('master.batches.add')->middleware('permission:batch add');
        Route::post('add', [BatchController::class, 'store'])->name('master.batches.store')->middleware('permission:batch add');
        Route::get('view/{id}', [BatchController::class, 'view'])->name('master.batches.view')->middleware('permission:batch view');
        Route::get('edit/{id}', [BatchController::class, 'edit'])->name('master.batches.edit')->middleware('permission:batch edit');
        Route::post('edit/{id}', [BatchController::class, 'update'])->name('master.batches.update')->middleware('permission:batch edit');
        Route::get('delete/{id}', [BatchController::class, 'delete'])->name('master.batches.delete')->middleware('permission:batch delete');
        Route::get('update-active-status/{id}', [BatchController::class, 'update_active_status'])->name('master.batches.update-active-status')->middleware('permission:batch edit');
        Route::post('start-coach-registration/{id}', [BatchController::class, 'start_coach_registration'])->name('master.batches.start-coach-registration')->middleware('permission:batch edit');
        Route::post('stop-coach-registration/{id}', [BatchController::class, 'stop_coach_registration'])->name('master.batches.stop-coach-registration')->middleware('permission:batch edit');
        Route::post('mark-batch-completed/{id}', [BatchController::class, 'mark_batch_completed'])->name('master.batches.mark-batch-completed')->middleware('permission:batch edit');
        Route::get('update-head-coach-status/{id}', [BatchController::class, 'update_head_coach_status'])->name('master.batches.update-head-coach-status')->middleware('permission:batch edit');
        Route::post('add-batch-schedule', [BatchController::class, 'add_batch_schedule'])->name('master.batches.add-batch-schedule')->middleware('permission:batch edit');
        Route::post('update-batch-schedule/{id}', [BatchController::class, 'update_batch_schedule'])->name('master.batches.update-batch-schedule')->middleware('permission:batch edit');
        Route::get('start-receive-payment/{id}', [BatchController::class, 'start_receive_payment'])->name('master.batches.start-recieve-payment')->middleware('permission:batch edit');
        Route::get('mark-session-completed/{id}', [BatchController::class, 'mark_session_completed'])->name('master.batches.mark-session-completed')->middleware('permission:batch edit');
        Route::get('mark-session-active/{id}', [BatchController::class, 'mark_session_active'])->name('master.batches.mark-session-active')->middleware('permission:batch edit');
        Route::get('view-session-assignment/{id}', [BatchController::class, 'view_session_assignment'])->name('master.batches.view-session-assignment')->middleware('permission:batch edit');
        Route::get('add-session-assignment/{id}', [BatchController::class, 'add_session_assignment'])->name('master.batches.add-session-assignment')->middleware('permission:batch edit');
        Route::post('add-session-assignment/{id}', [BatchController::class, 'store_session_assignment'])->name('master.batches.store-session-assignment')->middleware('permission:batch edit');
        Route::get('edit-session-assignment/{id}', [BatchController::class, 'edit_session_assignment'])->name('master.batches.edit-session-assignment')->middleware('permission:batch edit');
        Route::post('edit-session-assignment/{id}', [BatchController::class, 'update_session_assignment'])->name('master.batches.update-session-assignment')->middleware('permission:batch edit');
        Route::get('delete-session-assignment/{id}', [BatchController::class, 'delete_session_assignment'])->name('master.batches.delete-session-assignment')->middleware('permission:batch edit');
        Route::get('start-stop-session-assignment/{id}', [BatchController::class, 'start_stop_session_assignment'])->name('master.batches.start-stop-session-assignment')->middleware('permission:batch edit');
        Route::get('start-all-session-assignment/{id}', [BatchController::class, 'start_all_session_assignments'])->name('master.batches.start-all-session-assignment')->middleware('permission:batch edit');
        Route::get('stop-all-session-assignment/{id}', [BatchController::class, 'stop_all_session_assignments'])->name('master.batches.stop-all-session-assignment')->middleware('permission:batch edit');
        Route::get('update-rating-start-status/{id}', [BatchController::class, 'update_rating_start_status'])->name('master.batches.update-rating-start-status')->middleware('permission:batch edit');

        // Batch Group
        Route::post('add-group/{id}', [BatchController::class, 'add_group'])->name('master.batches.add-group')->middleware('permission:batch edit');
        Route::post('update-group/{id}', [BatchController::class, 'update_group'])->name('master.batches.update-group')->middleware('permission:batch edit');
        Route::get('delete-group/{id}', [BatchController::class, 'delete_group'])->name('master.batches.delete-group')->middleware('permission:batch edit');
        Route::post('add-participant-to-group', [BatchController::class, 'add_participant_to_group'])->name('master.batches.add-participant-to-group')->middleware('permission:batch edit');
        Route::get('remove-participant-from-group/{id}', [BatchController::class, 'remove_participant_from_group'])->name('master.batches.remove-participant-from-group')->middleware('permission:batch edit');

        // Batch Banner
        Route::get('update-batch-banner-active-status/{id}', [BatchController::class, 'update_batch_banner_active_status'])->name('master.batches.update-batch-banner-active-status')->middleware('permission:batch edit');
        Route::post('update-batch-banner/{id}', [BatchController::class, 'update_batch_banner'])->name('master.batches.update-batch-banner')->middleware('permission:batch edit');
        Route::post('delete-batch-banner/{id}', [BatchController::class, 'delete_batch_banner'])->name('master.batches.delete-batch-banner')->middleware('permission:batch delete');
    });

    // Participants
    Route::prefix('participants')->group(function(){
        Route::get('list', [ParticipantController::class, 'list'])->name('master.participants.list')->middleware('permission:participant list');
        Route::get('add', [ParticipantController::class, 'add'])->name('master.participants.add')->middleware('permission:participant add');
        Route::post('add', [ParticipantController::class, 'store'])->name('master.participants.store')->middleware('permission:participant add');
        Route::get('view/{id}', [ParticipantController::class, 'view'])->name('master.participants.view')->middleware('permission:participant view');
        Route::get('edit/{id}', [ParticipantController::class, 'edit'])->name('master.participants.edit')->middleware('permission:participant edit');
        Route::post('edit/{id}', [ParticipantController::class, 'update'])->name('master.participants.update')->middleware('permission:participant edit');
        Route::get('delete/{id}', [ParticipantController::class, 'delete'])->name('master.participants.delete')->middleware('permission:participant delete');
        Route::get('update-active-status/{id}', [ParticipantController::class, 'update_active_status'])->name('master.participants.update-active-status')->middleware('permission:participant edit');
        Route::get('add-registration-payment/{id}', [ParticipantController::class, 'add_registration_payment'])->name('master.participants.add-registration-payment')->middleware('permission:participant add');
        Route::post('add-registration-payment/{id}', [ParticipantController::class, 'store_registration_payment'])->name('master.participants.store-registration-payment')->middleware('permission:participant add');
        Route::get('view-sessions/{participant_id}/{batch_id}', [ParticipantController::class, 'view_sessions'])->name('master.participants.view-sessions')->middleware('permission:participant view');
        Route::get('view-assignments/{participant_id}/{batch_schedule_id}', [ParticipantController::class, 'view_assignments'])->name('master.participants.view-assignments')->middleware('permission:participant view');
        Route::post('add-to-another-batch/{id}', [ParticipantController::class, 'add_to_another_batch'])->name('master.participants.add-to-another-batch')->middleware('permission:participant edit');
        Route::post('import', [ParticipantController::class, 'import'])->name('master.participants.import')->middleware('permission:participant add');
        Route::get('receive-payment', [ParticipantController::class, 'receive_payment'])->name('master.participants.receive-payment')->middleware('permission:participant add');
        Route::post('receive-registration-due-payment', [ParticipantController::class, 'receive_registration_due_payment'])->name('master.participants.receive-registration-due-payment')->middleware('permission:participant add');
        Route::post('send-whatsapp-message', [ParticipantController::class, 'send_whatsapp_message'])->name('master.participants.send-whatsapp-message')->middleware('permission:participant add');
        Route::get('add-session-due-payment/{participant_id}/{batch_id}', [ParticipantController::class, 'add_session_due_payment'])->name('master.participants.add-session-due-payment')->middleware('permission:participant add');
        Route::post('add-session-due-payment', [ParticipantController::class, 'store_session_due_payment'])->name('master.participants.store-session-due-payment')->middleware('permission:participant add');
        Route::get('download-invoice/{payment_id}', [ParticipantController::class, 'download_invoice'])->name('master.participants.download-invoice')->middleware('permission:participant view');
    });

    // Coach
    Route::prefix('coaches')->group(function(){
        Route::get('list', [CoachController::class, 'list'])->name('master.coaches.list')->middleware('permission:participant list');
        Route::get('add', [CoachController::class, 'add'])->name('master.coaches.add')->middleware('permission:participant add');
        Route::post('add', [CoachController::class, 'store'])->name('master.coaches.store')->middleware('permission:participant add');
        Route::get('edit/{id}', [CoachController::class, 'edit'])->name('master.coaches.edit')->middleware('permission:participant edit');
        Route::post('edit/{id}', [CoachController::class, 'update'])->name('master.coaches.update')->middleware('permission:participant edit');
        Route::get('delete/{id}', [CoachController::class, 'delete'])->name('master.coaches.delete')->middleware('permission:participant delete');
        Route::post('assign-batch', [CoachController::class, 'assign_batch'])->name('master.coaches.assign-batch')->middleware('permission:participant edit');
        Route::get('coach-registration-requests', [CoachController::class, 'coach_registration_requests'])->name('master.coaches.coach-registration-requests')->middleware('permission:participant list');
        Route::post('update-registration-request-status', [CoachController::class, 'update_registration_request_status'])->name('master.coaches.update-registration-request-status')->middleware('permission:participant edit');
    });

    // Photo Gallery
    Route::prefix('photo-gallery')->group(function(){
        Route::get('list', [PhotoGalleryController::class, 'list'])->name('master.photo-gallery.list')->middleware('permission:photo-gallery list');
        Route::get('add', [PhotoGalleryController::class, 'add'])->name('master.photo-gallery.add')->middleware('permission:photo-gallery add');
        Route::post('add', [PhotoGalleryController::class, 'store'])->name('master.photo-gallery.store')->middleware('permission:photo-gallery add');
        Route::get('edit/{id}', [PhotoGalleryController::class, 'edit'])->name('master.photo-gallery.edit')->middleware('permission:photo-gallery edit');
        Route::post('edit/{id}', [PhotoGalleryController::class, 'update'])->name('master.photo-gallery.update')->middleware('permission:photo-gallery edit');
        Route::get('delete/{id}', [PhotoGalleryController::class, 'delete'])->name('master.photo-gallery.delete')->middleware('permission:photo-gallery delete');
        Route::get('update-active-status/{id}', [PhotoGalleryController::class, 'update_active_status'])->name('master.photo-gallery.update-active-status')->middleware('permission:photo-gallery edit');
    });

    // Video Gallery
    Route::prefix('video-gallery')->group(function(){
        Route::get('list', [VideoGalleryController::class, 'list'])->name('master.video-gallery.list')->middleware('permission:video-gallery list');
        Route::get('add', [VideoGalleryController::class, 'add'])->name('master.video-gallery.add')->middleware('permission:video-gallery add');
        Route::post('add', [VideoGalleryController::class, 'store'])->name('master.video-gallery.store')->middleware('permission:video-gallery add');
        Route::get('edit/{id}', [VideoGalleryController::class, 'edit'])->name('master.video-gallery.edit')->middleware('permission:video-gallery edit');
        Route::post('edit/{id}', [VideoGalleryController::class, 'update'])->name('master.video-gallery.update')->middleware('permission:video-gallery edit');
        Route::get('delete/{id}', [VideoGalleryController::class, 'delete'])->name('master.video-gallery.delete')->middleware('permission:video-gallery delete');
        Route::get('update-active-status/{id}', [VideoGalleryController::class, 'update_active_status'])->name('master.video-gallery.update-active-status')->middleware('permission:video-gallery edit');
    });

    // E3 Talk
    Route::prefix('e3-talk')->group(function(){
        Route::get('list', [VideoGalleryController::class, 'list_e3_talk'])->name('master.e3-talk.list')->middleware('permission:e3-talk list');
        Route::get('add', [VideoGalleryController::class, 'add_e3_talk'])->name('master.e3-talk.add')->middleware('permission:e3-talk add');
        Route::post('add', [VideoGalleryController::class, 'store_e3_talk'])->name('master.e3-talk.store')->middleware('permission:e3-talk add');
        Route::get('edit/{id}', [VideoGalleryController::class, 'edit_e3_talk'])->name('master.e3-talk.edit')->middleware('permission:e3-talk edit');
        Route::post('edit/{id}', [VideoGalleryController::class, 'update_e3_talk'])->name('master.e3-talk.update')->middleware('permission:e3-talk edit');
        Route::get('delete/{id}', [VideoGalleryController::class, 'delete_e3_talk'])->name('master.e3-talk.delete')->middleware('permission:e3-talk delete');
        Route::get('update-active-status/{id}', [VideoGalleryController::class, 'update_e3_talk_active_status'])->name('master.e3-talk.update-active-status')->middleware('permission:e3-talk edit');
    });

    // Notice And Announcement
    Route::prefix('notice-and-announcement')->group(function(){
        Route::get('list', [NoticeAndAnnouncementController::class, 'list'])->name('master.notice-and-announcement.list')->middleware('permission:notice-announcement list');
        Route::get('add', [NoticeAndAnnouncementController::class, 'add'])->name('master.notice-and-announcement.add')->middleware('permission:notice-announcement add');
        Route::post('add', [NoticeAndAnnouncementController::class, 'store'])->name('master.notice-and-announcement.store')->middleware('permission:notice-announcement add');
        Route::get('edit/{id}', [NoticeAndAnnouncementController::class, 'edit'])->name('master.notice-and-announcement.edit')->middleware('permission:notice-announcement edit');
        Route::post('edit/{id}', [NoticeAndAnnouncementController::class, 'update'])->name('master.notice-and-announcement.update')->middleware('permission:notice-announcement edit');
        Route::get('delete/{id}', [NoticeAndAnnouncementController::class, 'delete'])->name('master.notice-and-announcement.delete')->middleware('permission:notice-announcement delete');
        Route::get('update-active-status/{id}', [NoticeAndAnnouncementController::class, 'update_active_status'])->name('master.notice-and-announcement.update-active-status')->middleware('permission:notice-announcement edit');
    });

    // Testimonial
    Route::prefix('testimonials')->group(function(){
        Route::get('list', [TestimonialController::class, 'list'])->name('master.testimonials.list')->middleware('permission:testimonials list');
        Route::get('add', [TestimonialController::class, 'add'])->name('master.testimonials.add')->middleware('permission:testimonials add');
        Route::post('add', [TestimonialController::class, 'store'])->name('master.testimonials.store')->middleware('permission:testimonials add');
        Route::get('edit/{id}', [TestimonialController::class, 'edit'])->name('master.testimonials.edit')->middleware('permission:testimonials edit');
        Route::post('edit/{id}', [TestimonialController::class, 'update'])->name('master.testimonials.update')->middleware('permission:testimonials edit');
        Route::get('delete/{id}', [TestimonialController::class, 'delete'])->name('master.testimonials.delete')->middleware('permission:testimonials delete');
        Route::get('update-active-status/{id}', [TestimonialController::class, 'update_active_status'])->name('master.testimonials.update-active-status')->middleware('permission:testimonials edit');
    });

    // Guest Homepage
    Route::prefix('guest-homepage')->group(function(){
        Route::get('list', [GuestHomepageController::class, 'list'])->name('master.guest-homepage.list')->middleware('permission:guest-homepage list');
        Route::get('add', [GuestHomepageController::class, 'add'])->name('master.guest-homepage.add')->middleware('permission:guest-homepage add');
        Route::post('add', [GuestHomepageController::class, 'store'])->name('master.guest-homepage.store')->middleware('permission:guest-homepage add');
        Route::get('edit/{id}', [GuestHomepageController::class, 'edit'])->name('master.guest-homepage.edit')->middleware('permission:guest-homepage edit');
        Route::post('edit/{id}', [GuestHomepageController::class, 'update'])->name('master.guest-homepage.update')->middleware('permission:guest-homepage edit');
        Route::get('delete/{id}', [GuestHomepageController::class, 'delete'])->name('master.guest-homepage.delete')->middleware('permission:guest-homepage delete');
        Route::get('update-active-status/{id}', [GuestHomepageController::class, 'update_active_status'])->name('master.guest-homepage.update-active-status')->middleware('permission:guest-homepage edit');
    });

    // Batch Homepage
    Route::prefix('batch-homepage')->group(function(){
        Route::get('list', [BatchHomepageController::class, 'list'])->name('master.batch-homepage.list')->middleware('permission:batch-homepage list');
        Route::get('add', [BatchHomepageController::class, 'add'])->name('master.batch-homepage.add')->middleware('permission:batch-homepage add');
        Route::post('add', [BatchHomepageController::class, 'store'])->name('master.batch-homepage.store')->middleware('permission:batch-homepage add');
        Route::get('edit/{id}', [BatchHomepageController::class, 'edit'])->name('master.batch-homepage.edit')->middleware('permission:batch-homepage edit');
        Route::post('edit/{id}', [BatchHomepageController::class, 'update'])->name('master.batch-homepage.update')->middleware('permission:batch-homepage edit');
        Route::get('delete/{id}', [BatchHomepageController::class, 'delete'])->name('master.batch-homepage.delete')->middleware('permission:batch-homepage delete');
        Route::get('update-active-status/{id}', [BatchHomepageController::class, 'update_active_status'])->name('master.batch-homepage.update-active-status')->middleware('permission:batch-homepage edit');
    });

    // Messages
    Route::prefix('messages')->group(function(){
        Route::get('batch-messages', [MessagesController::class, 'batch_messages'])->name('master.messages.batch-messages')->middleware('permission:messages list');
        Route::post('batch-messages', [MessagesController::class, 'store_batch_messages'])->name('master.messages.store-batch-messages')->middleware('permission:messages add');
        Route::get('group-messages', [MessagesController::class, 'group_messages'])->name('master.messages.group-messages')->middleware('permission:messages list');
        Route::post('group-messages', [MessagesController::class, 'store_group_messages'])->name('master.messages.store-group-messages')->middleware('permission:messages add');
        Route::get('participant-messages', [MessagesController::class, 'participant_messages'])->name('master.messages.participant-messages')->middleware('permission:messages list');
        Route::post('participant-messages', [MessagesController::class, 'store_participant_messages'])->name('master.messages.store-participant-messages')->middleware('permission:messages add');

        Route::get('delete-messages/{id}', [MessagesController::class, 'delete_messages'])->name('master.messages.delete-messages')->middleware('permission:messages delete');
    });

    // Recommendations
    Route::prefix('recommendations')->group(function(){
        Route::get('list', [RecommendationController::class, 'list'])->name('master.recommendations.list')->middleware('permission:recommendations list');
        Route::get('follow-up-list', [RecommendationController::class, 'follow_up_list'])->name('master.recommendations.follow-up-list')->middleware('permission:recommendations list');
        Route::get('add', [RecommendationController::class, 'add'])->name('master.recommendations.add')->middleware('permission:recommendations add');
        Route::post('add', [RecommendationController::class, 'store'])->name('master.recommendations.store')->middleware('permission:recommendations add');
        Route::get('update-follow-up-status/{id}', [RecommendationController::class, 'update_follow_up_status'])->name('master.recommendations.update-follow-up-status')->middleware('permission:recommendations edit');
        Route::post('add-follow-up', [RecommendationController::class, 'add_follow_up'])->name('master.recommendations.add-follow-up')->middleware('permission:recommendations edit');
        Route::post('mark-follow-up-complete/{id}', [RecommendationController::class, 'mark_follow_up_complete'])->name('master.recommendations.mark-follow-up-complete')->middleware('permission:recommendations edit');
    });

   
});
