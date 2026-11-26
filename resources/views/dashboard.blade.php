@extends('layouts.app')

@section('title', 'Profil Saya - KursusKu')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Profil Saya</h1>
        <p class="text-secondary mb-0">Halo, {{ $user->name }}. Berikut ringkasan KursusKu.</p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-danger">Keluar</button>
    </form>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Profil Saya</h2>
                <dl class="mb-3">
                    <dt class="text-secondary small">Nama</dt>
                    <dd>{{ $user->name }}</dd>
                    <dt class="text-secondary small">Email</dt>
                    <dd>{{ $user->email }}</dd>
                    <dt class="text-secondary small">Akun dibuat</dt>
                    <dd class="mb-0">{{ $user->created_at?->format('d/m/Y H:i') }}</dd>
                </dl>
                <hr>
                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#formResetPasswordProfil" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                        Ubah / Reset Password
                    </button>
                    <a href="{{ route('password.request') }}" class="small text-decoration-none text-secondary">
                        Lupa password? Kirim tautan reset via email
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3 h-100">
            @foreach ([
                ['Total Course', $stats['courses']],
                ['Course Open', $stats['open_courses']],
                ['Participant', $stats['participants']],
                ['Enrollment', $stats['enrollments']],
            ] as [$label, $value])
                <div class="col-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-secondary small">{{ $label }}</div>
                            <div class="display-6 fw-bold">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Panel Form Ubah / Reset Password di Halaman Profil --}}
<div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="formResetPasswordProfil">
    <div class="card shadow-sm border-warning">
        <div class="card-header fw-semibold">Ubah / Reset Password Akun</div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.password.update') }}" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        @include('auth._password-field', [
                            'id' => 'current_password',
                            'name' => 'current_password',
                            'label' => 'Password Lama',
                            'autocomplete' => 'current-password',
                        ])
                    </div>
                    <div class="col-md-4">
                        @include('auth._password-field', [
                            'id' => 'new_password',
                            'name' => 'password',
                            'label' => 'Password Baru (min. 8 karakter)',
                            'autocomplete' => 'new-password',
                        ])
                    </div>
                    <div class="col-md-4">
                        @include('auth._password-field', [
                            'id' => 'new_password_confirmation',
                            'name' => 'password_confirmation',
                            'label' => 'Ulangi Password Baru',
                            'autocomplete' => 'new-password',
                        ])
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
            </form>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('courses.index') }}" class="btn btn-outline-primary">Kelola Kursus</a>
    <a href="{{ route('enrollments.create') }}" class="btn btn-outline-primary">Tambah Pendaftaran</a>
    <a href="{{ route('enrollments.index') }}" class="btn btn-outline-primary">Laporan Enrollment</a>
</div>

<div class="card shadow-sm">
    <div class="card-header">Pendaftaran Terbaru</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>Participant</th><th>Course</th><th>Status</th></tr>
            </thead>
            <tbody>
            @forelse ($recentEnrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->participant->name }}</td>
                    <td>{{ $enrollment->course->name }}</td>
                    <td><span class="kk-status kk-status-{{ $enrollment->status }}">{{ $enrollment->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada pendaftaran.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    @include('auth._toggle-script')
@endpush
