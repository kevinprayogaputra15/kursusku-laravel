<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/pendaftaran', function () {
    $courses = Course::query()->orderBy('name')->get();

    return view('enrollments.create', compact('courses'));
})->name('enrollments.create');

Route::post('/pendaftaran/preview', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'min:3', 'max:100'],
        'email' => ['required', 'email'],
        'course_id' => ['required', 'integer'],
    ]);

    return back()
        ->with('success', 'Form lolos validasi. Database pendaftaran akan ditambahkan pada pertemuan berikutnya.')
        ->withInput();
})->name('enrollments.preview');

Route::resource('courses', CourseController::class);

Route::get('/courses/{course}/availability', function (Course $course) {
    return response()->json([
        'id' => $course->id,
        'code' => $course->code,
        'name' => $course->name,
        'quota' => $course->quota,
        'status' => $course->status,
        // Tetap kompatibel dengan Ajax pertemuan 9.
        // Belum ada Enrollment, jadi sisa kursi = kuota.
        'course' => $course->name,
        'remaining' => $course->quota,
    ]);
})->name('courses.availability');
