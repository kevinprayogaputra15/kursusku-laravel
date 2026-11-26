<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Halaman "Profil Saya / Dashboard" setelah berhasil login. */
    public function index(Request $request)
    {
        $stats = [
            'courses' => Course::count(),
            'open_courses' => Course::where('status', 'open')->count(),
            'participants' => Participant::count(),
            'enrollments' => Enrollment::count(),
        ];

        $recentEnrollments = Enrollment::with(['participant', 'course'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'user' => $request->user(),
            'stats' => $stats,
            'recentEnrollments' => $recentEnrollments,
        ]);
    }
}
