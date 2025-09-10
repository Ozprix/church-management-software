<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Integration\GoogleCalendarController;
use App\Http\Controllers\Integration\SmsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Registration Routes
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');
});

Route::middleware('auth')->group(function () {
    // Logout Route
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');

    // Email Verification Routes
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    // Password Confirmation
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
});

Route::get('/dashboard', function () {
    return view('spa');
})->middleware(['auth', 'verified'])->name('dashboard');

// Direct route to Group Analytics page
Route::get('/group-analytics', function () {
    return view('group-analytics');
})->middleware(['auth', 'verified'])->name('group.analytics');

// Direct route to Group Communication page
Route::get('/group-communication', function () {
    return view('group-communication');
})->middleware(['auth', 'verified'])->name('group.communication');

// Fallback route for SPA
Route::get('/app/{any?}', function () {
    return view('spa');
})->where('any', '.*')->middleware(['auth', 'verified'])->name('spa');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Google Calendar Integration Routes
    Route::prefix('google-calendar')->name('google.calendar.')->group(function () {
        Route::get('/connect', [GoogleCalendarController::class, 'connect'])->name('connect');
        Route::get('/callback', [GoogleCalendarController::class, 'callback'])->name('callback');
        Route::post('/create-event', [GoogleCalendarController::class, 'createEvent'])->name('create-event');
        Route::get('/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('disconnect');
    });
    
    // SMS Integration Routes
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [SmsController::class, 'index'])->name('index');
        Route::post('/connect', [SmsController::class, 'connect'])->name('connect');
        Route::get('/disconnect', [SmsController::class, 'disconnect'])->name('disconnect');
        Route::post('/send-to-group', [SmsController::class, 'sendToGroup'])->name('send-to-group');
        Route::post('/send-to-member', [SmsController::class, 'sendToMember'])->name('send-to-member');
    });
});

// Admin dashboard (only for super admin)
Route::middleware(['auth', 'verified', 'adminonly'])->get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// Admin user management (only for super admin)
Route::middleware(['auth', 'verified', 'adminonly'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
});

require __DIR__ . '/auth.php';
