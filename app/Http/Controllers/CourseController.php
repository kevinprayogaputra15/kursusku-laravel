<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $status = trim((string) $request->query('status'));
        $allowedStatuses = ['open', 'closed', 'draft'];

        $courses = Course::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            // Filter status hanya diterapkan jika nilainya dikenal aplikasi.
            ->when(in_array($status, $allowedStatuses, true),
                fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            // Link pagination tidak boleh membawa partial=1 (agar tetap halaman penuh
            // saat dibuka lewat klik kanan / tab baru).
            ->appends('partial', null);

        // Satu query, dua response: halaman penuh atau hanya blok hasil (Ajax).
        if ($request->boolean('partial')) {
            return view('courses._results', compact('courses'));
        }

        return view('courses.index', compact('courses', 'search', 'status'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(StoreCourseRequest $request)
    {
        Course::create($request->validated());

        return to_route('courses.index')
            ->with('success', 'Kursus berhasil ditambahkan.');
    }

    public function show(Course $course)
    {
        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->validated());

        return to_route('courses.show', $course)
            ->with('success', 'Kursus berhasil diperbarui.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return to_route('courses.index')
            ->with('success', 'Kursus berhasil dihapus.');
    }
}
