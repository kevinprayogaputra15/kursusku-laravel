<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /** Tampilkan halaman Profil Saya (sesuai tampilan /profil). */
    public function show(Request $request)
    {
        return app(DashboardController::class)->index($request);
    }

    /** Ubah / reset password langsung dari halaman Profil Saya. */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ],
            [
                'current_password.required' => 'Password lama wajib diisi.',
                'current_password.current_password' => 'Password lama tidak sesuai.',
                'password.required' => 'Password baru wajib diisi.',
                'password.confirmed' => 'Konfirmasi password baru tidak sama.',
                'password.min' => 'Password baru minimal 8 karakter.',
            ]
        );

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diperbarui!');
    }
}
