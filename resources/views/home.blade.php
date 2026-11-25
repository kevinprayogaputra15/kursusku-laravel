@extends('layouts.app')

@section('title', 'Beranda - KursusKu')

@section('content')
<section class="py-4 py-lg-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="badge text-bg-success mb-3">
                Kursus teknologi untuk mahasiswa
            </span>
            <h1 class="display-5 fw-bold">
                Belajar lebih terarah bersama KursusKu
            </h1>
            <p class="lead text-secondary">
                Pilih kursus, cek ketersediaan kursi, lalu isi formulir pendaftaran.
            </p>
            <a href="{{ route('enrollments.create') }}" class="btn btn-success">
                Daftar Kursus
            </a>
        </div>
        <div class="col-lg-5">
            <img src="{{ asset('images/hero-kursus.jpg') }}"
                 class="img-fluid rounded-4 shadow-sm"
                 alt="Ilustrasi kelas KursusKu">
        </div>
    </div>
</section>

<section class="pb-4">
    <h2 class="h4 fw-bold mb-3">Ringkasan Data</h2>
    <div class="row g-3">
        @foreach ([
            ['Total Course', $stats['courses']],
            ['Course Open', $stats['open_courses']],
            ['Participant', $stats['participants']],
            ['Enrollment', $stats['enrollments']],
        ] as [$label, $value])
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small">{{ $label }}</div>
                        <div class="display-6 fw-bold">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="pb-5">
    <h2 class="h4 fw-bold mb-3">Program Belajar Unggulan</h2>
    <div class="row g-3">
        @foreach ($featured as $course)
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 shadow-sm">
                    <div class="card-body">
                        <span class="badge text-bg-light border mb-2">{{ $course['code'] }}</span>
                        <h3 class="h5">{{ $course['name'] }}</h3>
                        <p class="text-secondary mb-0">{{ $course['description'] }}</p>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</section>
@endsection
