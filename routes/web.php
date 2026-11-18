<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use App\Models\Course;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::resource('courses', CourseController::class);

// Pertemuan 12: enrollments.index / enrollments.create / enrollments.store
// (route lama /pendaftaran dan enrollments.preview dari pertemuan 9 digantikan).
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
        // Kompatibel dengan Ajax pertemuan 9 (membaca course & remaining).
        'course' => $course->name,
        // Pertemuan 12: sisa kursi = kuota - jumlah enrollment.
        'remaining' => $remaining,
    ]);
})->name('courses.availability');
