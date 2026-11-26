<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Models\Course;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| UAS: Autentikasi (login, logout, lupa & reset password, profil/dashboard)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:10,1')   // maksimal 10 percobaan/menit
    ->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Fitur Lupa & Reset Password (saat logout / lupa sandi)
Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'email'])
    ->middleware('throttle:6,1')
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

// Halaman Dashboard & Profil Saya + Reset/Ganti Password saat login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

/*
|--------------------------------------------------------------------------
| Fitur KursusKu (pertemuan 7-13) — tetap dapat diakses
|--------------------------------------------------------------------------
*/
Route::resource('courses', CourseController::class);

Route::resource('enrollments', EnrollmentController::class)
    ->only(['index', 'create', 'store']);

// URL lama tetap berfungsi: /pendaftaran -> /enrollments/create
Route::redirect('/pendaftaran', '/enrollments/create');

Route::get('/courses/{course}/availability', function (Course $course) {
    $remaining = max($course->quota - $course->enrollments()->count(), 0);

    return response()->json([
        'id' => $course->id,
        'code' => $course->code,
        'name' => $course->name,
        'quota' => $course->quota,
        'status' => $course->status,
        'course' => $course->name,
        'remaining' => $remaining,
    ]);
})->name('courses.availability');
