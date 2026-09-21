<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EntryUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Root Route
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('login', [LoginController::class, 'login'])->name('login-post');

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Password Reset Routes
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');


// centers routes
Route::middleware(['auth'])->group(function () {

    Route::get('dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    Route::get('stop_impersonate', [HomeController::class, 'stopImpersonate'])->name('stop-impersonate');
});

Route::prefix('entry-user')->group(function () {

    Route::get('dashboard', [EntryUserController::class, 'entryUserDashboard'])->name('entry-user-dashboard');
    Route::get('session-details/{id}', [EntryUserController::class, 'sessionDetails'])->name('entry-user.session-details');
    Route::get('scan-qr-code', [EntryUserController::class, 'scanQrCode'])->name('entry-user.scan-qr-code');
    Route::get('enter-participant', [EntryUserController::class, 'enterParticipant'])->name('entry-user.enter-participant');
    Route::get('decline-participant', [EntryUserController::class, 'declineParticipant'])->name('entry-user.decline-participant');
});
