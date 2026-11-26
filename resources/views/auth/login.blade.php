@extends('layouts.app')

@section('title', 'Masuk - KursusKu')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <div class="text-center mb-4">
            <h1 class="h3 mb-1">Masuk ke KursusKu</h1>
            <p class="text-secondary mb-0">Gunakan akun admin untuk membuka dashboard.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="card shadow-sm" novalidate>
            @csrf

            <div class="card-body p-4">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           autocomplete="username" autofocus
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @include('auth._password-field', [
                    'id' => 'password',
                    'name' => 'password',
                    'label' => 'Password',
                    'autocomplete' => 'current-password',
                ])

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1"
                               @checked(old('remember'))>
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @include('auth._toggle-script')
@endpush
