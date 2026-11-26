<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /** Form "Lupa password": minta tautan reset. */
    public function request()
    {
        return view('auth.forgot-password');
    }

    /** Kirim tautan reset ke email (default: ditulis ke storage/logs/laravel.log). */
    public function email(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email']],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
            ]
        );

        Password::sendResetLink($request->only('email'));

        // Pesan sama untuk email terdaftar/tidak (tidak membocorkan daftar akun).
        return back()->with(
            'success',
            'Jika email terdaftar, tautan reset password sudah dikirim.'
        );
    }

    /** Form password baru (dibuka dari tautan di email). */
    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Simpan password baru (di-hash otomatis oleh cast 'hashed' pada model User). */
    public function update(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', PasswordRule::min(8)],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password baru wajib diisi.',
                'password.confirmed' => 'Konfirmasi password tidak sama.',
                'password.min' => 'Password minimal 8 karakter.',
            ]
        );

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            return redirect()->route('login')
                ->with('success', 'Password berhasil diubah. Silakan masuk.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
    }
}
