@extends('layouts.app')

@section('title', 'Lupa Password - KursusKu')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <div class="text-center mb-4">
            <h1 class="h3 mb-1">Lupa Password</h1>
            <p class="text-secondary mb-0">Masukkan email akun Anda. Kami kirimkan tautan untuk membuat password baru.</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}" class="card shadow-sm" novalidate>
            @csrf

            <div class="card-body p-4">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}" autofocus
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">Kirim Tautan Reset</button>
            </div>

            <div class="card-footer text-center">
                <a href="{{ route('login') }}" class="small">Kembali ke halaman masuk</a>
            </div>
        </form>
    </div>
</div>
@endsection
