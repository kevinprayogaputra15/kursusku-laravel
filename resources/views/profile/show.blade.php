@extends('layouts.app')

@section('title', 'Profil Saya - KursusKu')

@section('content')
<section class="mb-4">
    <h1 class="h3">Profil Saya</h1>
    <p class="lead text-secondary mb-0">Halo, {{ $user->name }}. Berikut ringkasan KursusKu.</p>
</section>

<section class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body profile-card">
                <x-avatar :user="$user" :size="96" class="mb-3" />
                <h2 class="h4 mb-1">{{ $user->name }}</h2>
                <p class="text-secondary mb-2">&#64;{{ $user->username ?? '-' }}</p>
                <span class="badge {{ $user->isAdmin() ? 'text-bg-success' : 'text-bg-secondary' }} mb-4">
                    {{ $user->isAdmin() ? 'Admin' : 'User' }}
                </span>

                <dl class="profile-meta mb-0">
                    <dt>Email</dt>
                    <dd>{{ $user->email }}</dd>
                    <dt>Akun dibuat</dt>
                    <dd>{{ $user->created_at?->format('d/m/Y H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="row g-3">
            @foreach ([
                ['Total Course', $stats['courses']],
                ['Course Open', $stats['open_courses']],
                ['Participant', $stats['participants']],
                ['Enrollment', $stats['enrollments']],
            ] as [$label, $value])
                <div class="col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase">{{ $label }}</div>
                            <div class="display-6 fw-bold">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('courses.index') }}" class="btn btn-outline-light">Kelola Kursus</a>
    <a href="{{ route('enrollments.create') }}" class="btn btn-outline-light">Tambah Pendaftaran</a>
    <a href="{{ route('enrollments.index') }}" class="btn btn-outline-light">Laporan Enrollment</a>
    <form method="POST" action="{{ route('logout') }}" class="ms-auto">
        @csrf
        <button type="submit" class="btn btn-outline-danger">Keluar</button>
    </form>
</div>
@endsection
