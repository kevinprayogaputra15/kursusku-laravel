@extends('layouts.app')

@section('title', 'Tambah Enrollment')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-1">Tambah Enrollment</h1>
                <p class="text-secondary mb-0">
                    Pilih participant dan course. Sisa kursi dicek tanpa reload halaman.
                </p>
            </div>
            <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary">
                Laporan Enrollment
            </a>
        </div>

        <form method="POST" action="{{ route('enrollments.store') }}" class="card shadow-sm" novalidate>
            @csrf

            <div class="card-body p-4">
                <div class="mb-3">
                    <label for="participant_id" class="form-label">Participant</label>
                    <select name="participant_id" id="participant_id"
                            class="form-select @error('participant_id') is-invalid @enderror">
                        <option value="">-- Pilih Participant --</option>
                        @foreach ($participants as $participant)
                            <option value="{{ $participant->id }}"
                                    @selected(old('participant_id') == $participant->id)>
                                {{ $participant->name }} - {{ $participant->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('participant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if ($participants->isEmpty())
                        <div class="form-text text-danger">
                            Belum ada participant. Jalankan
                            <code>php artisan db:seed --class=ParticipantSeeder</code>.
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="course_id" class="form-label">Course</label>
                    <select name="course_id" id="course_id"
                            class="form-select @error('course_id') is-invalid @enderror">
                        <option value="">-- Pilih Course --</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}"
                                    @selected(old('course_id') == $course->id)>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if ($courses->isEmpty())
                        <div class="form-text text-danger">
                            Belum ada course berstatus <strong>open</strong>.
                            Ubah status course lewat menu Daftar Kursus.
                        </div>
                    @endif
                </div>

                {{-- Diisi oleh Ajax pertemuan 9 (resources/js/app.js) --}}
                <div id="availability" class="alert alert-secondary mb-0">
                    Pilih kursus untuk melihat sisa kursi.
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Enrollment</button>
            </div>
        </form>
    </div>
</div>
@endsection
