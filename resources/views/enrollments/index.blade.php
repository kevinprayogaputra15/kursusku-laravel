@extends('layouts.app')

@section('title', 'Laporan Enrollment')

@section('content')
{{-- Flash message success sudah ditampilkan oleh layouts/app.blade.php --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Laporan Enrollment KursusKu</h1>
        <p class="text-muted mb-0">Data gabungan dari tabel enrollments, participants, dan courses.</p>
    </div>
    <a href="{{ route('enrollments.create') }}" class="btn btn-primary">+ Tambah Enrollment</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Course</th>
                        <th>Start Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($enrollments as $enrollment)
                    <tr>
                        <td>{{ $enrollment->participant->name }}</td>
                        <td>{{ $enrollment->course->name }}</td>
                        <td>{{ $enrollment->course->start_date->format('d/m/Y') }}</td>
                        <td><span class="kk-status kk-status-{{ $enrollment->status }}">{{ $enrollment->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada enrollment.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
