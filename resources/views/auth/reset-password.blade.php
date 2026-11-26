@extends('layouts.app')

@section('title', 'Reset Password - KursusKu')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <div class="text-center mb-4">
            <h1 class="h3 mb-1">Buat Password Baru</h1>
            <p class="text-secondary mb-0">Minimal 8 karakter.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="card shadow-sm" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="card-body p-4">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $email) }}"
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @include('auth._password-field', [
                    'id' => 'password',
                    'name' => 'password',
                    'label' => 'Password Baru',
                    'autocomplete' => 'new-password',
                ])

                @include('auth._password-field', [
                    'id' => 'password_confirmation',
                    'name' => 'password_confirmation',
                    'label' => 'Ulangi Password Baru',
                    'autocomplete' => 'new-password',
                ])

                <button type="submit" class="btn btn-primary w-100">Simpan Password Baru</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @include('auth._toggle-script')
@endpush
