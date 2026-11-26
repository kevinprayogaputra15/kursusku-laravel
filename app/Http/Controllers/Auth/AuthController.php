<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /** Tampilkan form login (jika sudah login, langsung ke dashboard). */
    public function create()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /** Proses login: cek email + password (hash), dukung "ingat saya". */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        // Argumen kedua = remember me (membuat cookie "remember_web_...").
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        // Cegah session fixation.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang, '.Auth::user()->name.'!');
    }

    /** Logout: hapus sesi dan buat token CSRF baru. */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah keluar.');
    }
}
