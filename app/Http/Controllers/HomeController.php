<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;

class HomeController extends Controller
{
    public function index()
    {
        $siteName = 'KursusKu';

        $featured = [
            [
                'code' => 'WEB-01',
                'name' => 'Web Dasar',
                'description' => 'Belajar struktur HTML dan CSS dasar.',
            ],
            [
                'code' => 'PHP-01',
                'name' => 'PHP Dasar',
                'description' => 'Belajar variabel, function, form, dan logika PHP.',
            ],
            [
                'code' => 'LAR-01',
                'name' => 'Laravel Fundamental',
                'description' => 'Belajar route, controller, Blade, dan MVC.',
            ],
            [
                'code' => 'UI-01',
                'name' => 'UI Web Dasar',
                'description' => 'Belajar prinsip tampilan web yang rapi.',
            ],
            [
                'code' => 'JS-01',
                'name' => 'JavaScript Dasar',
                'description' => 'Belajar event, DOM, dan interaksi di halaman.',
            ],
        ];

        // Dashboard count (milestone pertemuan 13)
        $stats = [
            'courses' => Course::count(),
            'open_courses' => Course::where('status', 'open')->count(),
            'participants' => Participant::count(),
            'enrollments' => Enrollment::count(),
        ];

        return view('home', compact('siteName', 'featured', 'stats'));
    }
}
