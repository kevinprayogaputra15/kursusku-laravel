@extends('layouts.app')

@section('title', 'Tambah Kursus')

@section('content')
<div class="container py-4" style="max-width: 760px">
    <h1 class="h3 mb-3">Tambah Kursus</h1>

    <form method="POST" action="{{ route('courses.store') }}" class="card card-body">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="code">Kode Kursus</label>
            <input class="form-control" id="code" name="code"
                   value="{{ old('code') }}" required maxlength="20">
            @error('code') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="name">Nama Kursus</label>
            <input class="form-control" id="name" name="name"
                   value="{{ old('name') }}" required maxlength="120">
            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
            @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="fee">Biaya</label>
                <input class="form-control" id="fee" type="number" name="fee"
                       value="{{ old('fee', 0) }}" min="0" required>
                @error('fee') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="quota">Kuota</label>
                <input class="form-control" id="quota" type="number" name="quota"
                       value="{{ old('quota', 20) }}" min="1" max="500" required>
                @error('quota') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="start_date">Tanggal Mulai</label>
                <input class="form-control" id="start_date" type="date" name="start_date"
                       value="{{ old('start_date') }}" required>
                @error('start_date') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                    <option value="open" @selected(old('status') === 'open')>Open</option>
                    <option value="closed" @selected(old('status') === 'closed')>Closed</option>
                </select>
                @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan Kursus</button>
            <a class="btn btn-outline-secondary" href="{{ route('courses.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
